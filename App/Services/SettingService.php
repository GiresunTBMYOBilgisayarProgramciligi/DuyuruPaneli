<?php
declare(strict_types=1);

namespace App\Services;

use App\Config;
use App\Core\Logger;
use App\Repositories\SettingRepository;

class SettingService
{
    private SettingRepository $repository;
    private static ?array $cache = null;

    public function __construct(?SettingRepository $repository = null)
    {
        $this->repository = $repository ?? new SettingRepository();
    }

    /**
     * Tüm ayarları anahtar-değer olarak döndürür (DB ve .env entegre)
     */
    public function getAll(): array
    {
        if (self::$cache === null) {
            $settings = $this->repository->getAll();

            // .env veya ortam değişkenlerinden boş kalan alanlara otomatik tamamlama
            $envMap = [
                'tinypng_api_key' => $_ENV['TINYPNG_API_KEY'] ?? getenv('TINYPNG_API_KEY') ?: '',
                'image_driver' => $_ENV['IMAGE_DRIVER'] ?? getenv('IMAGE_DRIVER') ?: '',
                'image_resize_dimension' => $_ENV['IMAGE_RESIZE_DIMENSION'] ?? getenv('IMAGE_RESIZE_DIMENSION') ?: '',
                'image_quality' => $_ENV['IMAGE_QUALITY'] ?? getenv('IMAGE_QUALITY') ?: '',
                'bitly_access_token' => $_ENV['BITLY_ACCESS_TOKEN'] ?? getenv('BITLY_ACCESS_TOKEN') ?: '',
                'url_shortener_provider' => $_ENV['URL_SHORTENER_PROVIDER'] ?? getenv('URL_SHORTENER_PROVIDER') ?: '',
            ];

            foreach ($envMap as $k => $envVal) {
                if ((empty($settings[$k]) || $settings[$k] === '') && !empty($envVal)) {
                    $settings[$k] = (string)$envVal;
                }
            }

            self::$cache = $settings;
        }

        return self::$cache;
    }

    /**
     * Belirli bir ayarı string olarak döndürür
     */
    public function getString(string $key, ?string $default = null): string
    {
        $all = $this->getAll();
        if (isset($all[$key]) && $all[$key] !== '') {
            return (string)$all[$key];
        }

        // Çevresel değişken kontrolü (.env)
        $envKey = strtoupper($key);
        if (isset($_ENV[$envKey]) && $_ENV[$envKey] !== '') {
            return (string)$_ENV[$envKey];
        }
        $envVal = getenv($envKey);
        if ($envVal !== false && $envVal !== '') {
            return (string)$envVal;
        }

        return $default ?? '';
    }

    /**
     * Belirli bir ayarı tamsayı (int) olarak döndürür
     */
    public function getInt(string $key, int $default = 0): int
    {
        $val = $this->getString($key);
        return $val !== '' ? (int)$val : $default;
    }

    /**
     * Belirli bir ayarı mantıksal (bool) olarak döndürür
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $val = $this->getString($key);
        if ($val === '') {
            return $default;
        }

        return in_array(strtolower($val), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Ayarları veritabanına kaydeder, yerel önbelleği sıfırlar ve .env dosyasına senkronize eder
     */
    public function saveSettings(array $settings): void
    {
        $this->repository->setMultiple($settings);
        self::$cache = null; // Önbelleği sıfırla

        // .env dosyasına otomatik senkronizasyon
        $this->syncToEnvFile($settings);
    }

    /**
     * Hassas ve yapılandırma anahtarlarını .env dosyasına otomatik yazar / senkronize eder
     */
    public function syncToEnvFile(array $settings = []): void
    {
        $envPath = Config::ROOT_PATH . '.env';
        $examplePath = Config::ROOT_PATH . '.env.example';

        // .env henüz yoksa .env.example şablonu üzerinden başlat
        if (!file_exists($envPath)) {
            if (file_exists($examplePath)) {
                $content = (string)file_get_contents($examplePath);
            } else {
                $content = "# UniPano Çevresel Yapılandırma Dosyası\n";
            }
        } else {
            $content = (string)file_get_contents($envPath);
        }

        // Senkronize edilecek anahtarlar ve değerleri
        $syncMap = [
            'TINYPNG_API_KEY' => $settings['tinypng_api_key'] ?? $this->getString('tinypng_api_key', ''),
            'IMAGE_DRIVER' => $settings['image_driver'] ?? $this->getString('image_driver', 'hybrid'),
            'IMAGE_RESIZE_DIMENSION' => $settings['image_resize_dimension'] ?? $this->getString('image_resize_dimension', '1920'),
            'IMAGE_QUALITY' => $settings['image_quality'] ?? $this->getString('image_quality', '85'),
            'BITLY_ACCESS_TOKEN' => $settings['bitly_access_token'] ?? $this->getString('bitly_access_token', ''),
            'URL_SHORTENER_PROVIDER' => $settings['url_shortener_provider'] ?? $this->getString('url_shortener_provider', 'auto'),
        ];

        foreach ($syncMap as $envKey => $envVal) {
            $envValStr = (string)$envVal;
            // Değer boşluk içeriyorsa tırnak içine al
            if (str_contains($envValStr, ' ') && !str_starts_with($envValStr, '"')) {
                $formattedVal = '"' . addcslashes($envValStr, '"') . '"';
            } else {
                $formattedVal = $envValStr;
            }

            $pattern = "/^{$envKey}=.*$/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$envKey}={$formattedVal}", $content);
            } else {
                $content = rtrim($content) . "\n{$envKey}={$formattedVal}\n";
            }

            // Çalışma zamanı ortamını da anında güncelle
            $_ENV[$envKey] = $envValStr;
            putenv("{$envKey}={$envValStr}");
        }

        $writeRes = @file_put_contents($envPath, $content, LOCK_EX);
        if ($writeRes === false) {
            Logger::channel('app')->error(".env dosyası sunucu kullanıcısı tarafından yazılamadı: " . $envPath);
        }
        @chmod($envPath, 0666);
    }

    /**
     * TinyPNG API Anahtarını döndürür (Öncelik: DB > .env)
     */
    public function getTinyPngApiKey(): string
    {
        return $this->getString('tinypng_api_key', '');
    }

    /**
     * Yeniden boyutlandırma genişliğini döndürür (varsayılan: 1920)
     */
    public function getImageResizeDimension(): int
    {
        return $this->getInt('image_resize_dimension', 1920);
    }

    /**
     * Optimizasyon sürücüsünü döndürür (hybrid, tinypng, gd, off)
     */
    public function getImageDriver(): string
    {
        $driver = strtolower($this->getString('image_driver', 'hybrid'));
        return in_array($driver, ['hybrid', 'tinypng', 'gd', 'off'], true) ? $driver : 'hybrid';
    }

    /**
     * Görsel kalitesini döndürür (varsayılan: 85)
     */
    public function getImageQuality(): int
    {
        $quality = $this->getInt('image_quality', 85);
        return max(40, min(100, $quality));
    }

    /**
     * Bitly API Erişim Belirtecini döndürür
     */
    public function getBitlyAccessToken(): string
    {
        return $this->getString('bitly_access_token', '');
    }

    /**
     * Aktif URL kısaltma servis sağlayıcısını döndürür (auto, bitly, tinyurl, isgd, internal)
     */
    public function getUrlShortenerProvider(): string
    {
        $provider = strtolower($this->getString('url_shortener_provider', 'auto'));
        return in_array($provider, ['auto', 'bitly', 'tinyurl', 'isgd', 'internal'], true) ? $provider : 'auto';
    }
}
