<?php
declare(strict_types=1);

namespace App\Core;

use App\Config;
use DateTimeZone;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Monolog\LogRecord;
use Throwable;

/**
 * UniPano Merkezi ve Yüksek Performanslı Loglama Servisi (Monolog Tabanlı)
 * 
 * PSR-3 standartlarına tam uyumluluk, 30 günlük (aylık) rotasyon,
 * 2 yıllık saklama süresi, otomatik bağlam enjeksiyonu ve hata toleranslı (fail-safe) yapı.
 */
class Logger
{
    /** @var array<string, MonologLogger> */
    private static array $channels = [];

    private static ?RotatingFileHandler $sharedHandler = null;
    private static bool $handlersRegistered = false;
    private static bool $pruned = false;

    private function __construct()
    {
    }

    /**
     * Belirtilen log kanalına ait Monolog instance'ını döndürür
     */
    public static function channel(string $name = 'app'): MonologLogger
    {
        $name = strtolower(trim($name));
        if ($name === '') {
            $name = 'app';
        }

        if (isset(self::$channels[$name])) {
            return self::$channels[$name];
        }

        $logDir = Config::LOG_DIR;
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
            @chmod($logDir, 0777);
        }

        // 2 yıldan eski logları periyodik temizle
        if (!self::$pruned) {
            self::$pruned = true;
            self::pruneOldLogs();
        }

        if (self::$sharedHandler === null) {
            $logLevelName = defined('App\Config::LOG_LEVEL') ? Config::LOG_LEVEL : 'DEBUG';
            $maxFiles = defined('App\Config::LOG_MAX_FILES') ? Config::LOG_MAX_FILES : 24; // 24 ay (2 yıl)
            $filePrefix = defined('App\Config::LOG_FILE_PREFIX') ? Config::LOG_FILE_PREFIX : 'unipano';

            try {
                $level = Level::fromName($logLevelName);
            } catch (Throwable) {
                $level = Level::Debug;
            }

            $logPath = rtrim($logDir, '/') . '/' . $filePrefix . '.log';

            // 30 günlük (aylık) rotasyon: unipano-YYYY-MM.log
            // filePermission: null verilir; böylece Monolog'un StreamHandler içindeki chmod() çağrısı ve
            // web sunucusu (www-data) yetki çakışması (EPERM) kesinlikle engellenir.
            self::$sharedHandler = new RotatingFileHandler(
                $logPath,
                $maxFiles,
                $level,
                true,
                null
            );

            // 30 günlük / aylık rotasyon formatı
            self::$sharedHandler->setFilenameFormat('{filename}-{date}', RotatingFileHandler::FILE_PER_MONTH);

            // Standart satır formatı: [Y-m-d H:i:s] [kanal] SEVİYE: Mesaj {bağlam} {ekstra}
            $formatter = new LineFormatter(
                "[%datetime%] [%channel%] %level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,
                true
            );
            self::$sharedHandler->setFormatter($formatter);

            // Beklenen mevcut ay log dosyasını 0666 izinleriyle hazırla (CLI & Apache ortak yazabilmesi için)
            $currentMonthFile = rtrim($logDir, '/') . '/' . $filePrefix . '-' . date('Y-m') . '.log';
            if (!file_exists($currentMonthFile)) {
                $oldMask = umask(0000);
                @touch($currentMonthFile);
                @chmod($currentMonthFile, 0666);
                umask($oldMask);
            } else {
                @chmod($currentMonthFile, 0666);
            }
        }

        $logger = new MonologLogger($name, [self::$sharedHandler], [], new DateTimeZone(Config::TIMEZONE));
        $logger->pushProcessor(self::getContextProcessor());

        self::$channels[$name] = $logger;
        return $logger;
    }

    /**
     * İstemci IP'si, kullanıcı ID'si ve istek bilgilerini log kayıtlarına otomatik bağlayan işlemci
     */
    private static function getContextProcessor(): callable
    {
        return function (LogRecord $record): LogRecord {
            $extra = $record->extra;

            if (PHP_SAPI === 'cli') {
                $extra['cli'] = true;
                if (isset($_SERVER['argv'][0])) {
                    $extra['script'] = basename($_SERVER['argv'][0]);
                }
            } else {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $extra['ip'] = trim(explode(',', $ip)[0]);

                if (class_exists(Session::class) && Session::isLoggedIn()) {
                    $userId = Session::getUserId();
                    if ($userId !== null) {
                        $extra['user_id'] = $userId;
                    }
                }

                if (isset($_SERVER['REQUEST_METHOD'])) {
                    $extra['method'] = $_SERVER['REQUEST_METHOD'];
                }

                if (isset($_SERVER['REQUEST_URI'])) {
                    $extra['uri'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
                }
            }

            return $record->with(extra: $extra);
        };
    }

    /* =========================================================================
     * Kolay Erişim Statik Metotları (Fail-Safe Facade)
     * Loglama hatası ana uygulamanın akışını ASLA bozmaz.
     * ========================================================================= */

    public static function emergency(string $message, array $context = []): void
    {
        try {
            self::channel('app')->emergency($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Acil Durum: {$message} | " . $e->getMessage());
        }
    }

    public static function alert(string $message, array $context = []): void
    {
        try {
            self::channel('app')->alert($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Alarm: {$message} | " . $e->getMessage());
        }
    }

    public static function critical(string $message, array $context = []): void
    {
        try {
            self::channel('app')->critical($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Kritik: {$message} | " . $e->getMessage());
        }
    }

    public static function error(string $message, array $context = []): void
    {
        try {
            self::channel('app')->error($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Hata: {$message} | " . $e->getMessage());
        }
    }

    public static function warning(string $message, array $context = []): void
    {
        try {
            self::channel('app')->warning($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Uyarı: {$message} | " . $e->getMessage());
        }
    }

    public static function notice(string $message, array $context = []): void
    {
        try {
            self::channel('app')->notice($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Not: {$message} | " . $e->getMessage());
        }
    }

    public static function info(string $message, array $context = []): void
    {
        try {
            self::channel('app')->info($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Bilgi: {$message} | " . $e->getMessage());
        }
    }

    public static function debug(string $message, array $context = []): void
    {
        try {
            self::channel('app')->debug($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log Debug: {$message} | " . $e->getMessage());
        }
    }

    public static function log(string|Level $level, string $message, array $context = []): void
    {
        try {
            if (is_string($level)) {
                try {
                    $level = Level::fromName(strtoupper($level));
                } catch (Throwable) {
                    $level = Level::Info;
                }
            }
            self::channel('app')->log($level, $message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Log: {$message} | " . $e->getMessage());
        }
    }

    /**
     * Güvenlik olayları için kestirme metot (security kanalı)
     */
    public static function security(string $message, array $context = []): void
    {
        try {
            self::channel('security')->warning($message, $context);
        } catch (Throwable $e) {
            error_log("UniPano Security: {$message} | " . $e->getMessage());
        }
    }

    /**
     * Denetim ve kullanıcı işlem geçmişi için kestirme metot (audit kanalı)
     */
    public static function audit(string $action, array $details = []): void
    {
        try {
            self::channel('audit')->info($action, $details);
        } catch (Throwable $e) {
            error_log("UniPano Audit: {$action} | " . $e->getMessage());
        }
    }

    /**
     * Hata ve İstisnaları (Throwable) detaylı bağlamla otomatik loglar
     */
    public static function exception(Throwable $e, string $customMessage = '', array $context = []): void
    {
        try {
            $context['exception_class'] = get_class($e);
            $context['code'] = $e->getCode();
            $context['file'] = $e->getFile() . ':' . $e->getLine();
            
            $msg = empty($customMessage) 
                ? "İstisna Hatası: {$e->getMessage()}" 
                : "{$customMessage}: {$e->getMessage()}";

            self::channel('app')->error($msg, $context);
        } catch (Throwable $loggingEx) {
            error_log("UniPano Exception Log Fail: " . $e->getMessage() . " | " . $loggingEx->getMessage());
        }
    }

    /**
     * 2 yıldan (varsayılan 730 gün) eski tüm log dosyalarını otomatik temizler
     */
    public static function pruneOldLogs(): void
    {
        $logDir = Config::LOG_DIR;
        if (!is_dir($logDir)) {
            return;
        }

        $files = glob(rtrim($logDir, '/') . '/*.log');
        if (!$files) {
            return;
        }

        $retentionDays = defined('App\Config::LOG_RETENTION_DAYS') ? Config::LOG_RETENTION_DAYS : 730;
        $cutoffTimestamp = time() - ($retentionDays * 86400);

        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime !== false && $mtime < $cutoffTimestamp) {
                @unlink($file);
            }
        }
    }

    /**
     * Global PHP hata, istisna ve ölümcül çökme (fatal error) yakalayıcılarını kaydeder
     */
    public static function registerHandlers(): void
    {
        if (self::$handlersRegistered) {
            return;
        }
        self::$handlersRegistered = true;

        // 1. PHP Hataları ve Uyarıları Yakalayıcısı
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            try {
                $context = ['file' => "{$file}:{$line}", 'severity' => $severity];

                if (in_array($severity, [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
                    self::channel('system')->error("PHP Hatası: {$message}", $context);
                } elseif (in_array($severity, [E_WARNING, E_USER_WARNING], true)) {
                    self::channel('system')->warning("PHP Uyarısı: {$message}", $context);
                } else {
                    self::channel('system')->notice("PHP Notu: {$message}", $context);
                }
            } catch (Throwable) {
                // Sonsuz döngü engeli
            }

            return false;
        });

        // 2. Yakalanamayan İstisnalar (Uncaught Exceptions)
        set_exception_handler(function (Throwable $e): void {
            try {
                self::exception($e, "Yakalanamayan İstisna (Uncaught Exception)");
            } catch (Throwable) {
                error_log("Kritik Log Hatası: " . $e->getMessage());
            }

            if (PHP_SAPI !== 'cli') {
                if (!headers_sent()) {
                    http_response_code(500);
                }
                echo "<!DOCTYPE html><html lang='tr'><head><meta charset='utf-8'><title>Sistem Hatası</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>500 - Bir Sistem Hatası Oluştu</h1><p>İşlem sırasında beklenmeyen bir hata meydana geldi. Olay teknik ekibe iletildi.</p><a href='/'>Ana Sayfa</a></body></html>";
            }
        });

        // 3. Ölümcül Kapanma Hataları (Fatal Shutdown Errors)
        register_shutdown_function(function (): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                try {
                    self::channel('system')->critical("Kritik Kapanma Hatası (Fatal Error): {$error['message']}", [
                        'file' => "{$error['file']}:{$error['line']}",
                        'type' => $error['type']
                    ]);
                } catch (Throwable) {
                    error_log("Kritik Kapanma Hatası: " . $error['message']);
                }
            }
        });
    }

    /**
     * storage/logs altındaki mevcut log dosyalarının listesini döndürür
     * 
     * @return array<int, array{filename: string, size: int, sizeHuman: string, modifiedAt: string}>
     */
    public static function getLogFiles(): array
    {
        $logDir = Config::LOG_DIR;
        if (!is_dir($logDir)) {
            return [];
        }

        $files = glob(rtrim($logDir, '/') . '/*.log');
        if (!$files) {
            return [];
        }

        // Yeniden eskiye doğru sırala
        usort($files, static fn(string $a, string $b) => filemtime($b) <=> filemtime($a));

        $result = [];
        foreach ($files as $filePath) {
            $bytes = filesize($filePath) ?: 0;
            $result[] = [
                'filename' => basename($filePath),
                'size' => $bytes,
                'sizeHuman' => self::formatBytes($bytes),
                'modifiedAt' => date('Y-m-d H:i:s', filemtime($filePath) ?: time())
            ];
        }

        return $result;
    }

    /**
     * Belirtilen log dosyasından son N satırı ayrıştırarak döndürür
     * 
     * @return array<int, array{raw: string, datetime: string, channel: string, level: string, message: string}>
     */
    public static function readLogLines(string $filename = '', int $limit = 200): array
    {
        $logDir = rtrim(Config::LOG_DIR, '/');
        
        if (empty($filename)) {
            // En güncel log dosyasını al
            $files = glob("{$logDir}/*.log");
            if (empty($files)) {
                return [];
            }
            usort($files, static fn(string $a, string $b) => filemtime($b) <=> filemtime($a));
            $filePath = $files[0];
        } else {
            // Path traversal önlemi
            $sanitized = basename($filename);
            $filePath = "{$logDir}/{$sanitized}";
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        // Son $limit kadar satırı al ve tersine çevir (en güncel en üstte)
        $sliced = array_slice($lines, -$limit);
        $sliced = array_reverse($sliced);

        $parsed = [];
        $pattern = '/^\[(?P<datetime>[^\]]+)\]\s+\[(?P<channel>[^\]]+)\]\s+(?P<level>[A-Z]+):\s+(?P<message>.*)$/';

        foreach ($sliced as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $parsed[] = [
                    'raw' => $line,
                    'datetime' => $matches['datetime'],
                    'channel' => $matches['channel'],
                    'level' => $matches['level'],
                    'message' => $matches['message'],
                ];
            } else {
                $parsed[] = [
                    'raw' => $line,
                    'datetime' => '',
                    'channel' => 'system',
                    'level' => 'INFO',
                    'message' => $line,
                ];
            }
        }

        return $parsed;
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
