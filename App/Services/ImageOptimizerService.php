<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use Exception;
use Throwable;
use Tinify\Tinify;
use Tinify\AccountException;
use Tinify\ClientException;
use Tinify\ServerException;
use Tinify\ConnectionException;

class ImageOptimizerService
{
    private SettingService $settingService;

    public function __construct(?SettingService $settingService = null)
    {
        $this->settingService = $settingService ?? new SettingService();
    }

    /**
     * Görseli yapılandırılan kural ve sürücüye göre optimize eder
     *
     * @param string $filePath Dosyanın mutlak sunucu yolu
     * @return array Sonuç ve istatistik dizisi
     */
    public function optimize(string $filePath): array
    {
        if (!file_exists($filePath) || !is_file($filePath)) {
            return [
                'success' => false,
                'driver' => 'none',
                'message' => 'Dosya bulunamadı.'
            ];
        }

        $origSize = filesize($filePath) ?: 0;
        $driver = $this->settingService->getImageDriver();
        $resizeDim = $this->settingService->getImageResizeDimension();
        $quality = $this->settingService->getImageQuality();

        // 1. Optimizasyon tamamen devre dışı ise
        if ($driver === 'off') {
            return [
                'success' => true,
                'driver' => 'off',
                'original_size' => $origSize,
                'optimized_size' => $origSize,
                'saved_bytes' => 0,
                'saved_percent' => 0.0,
                'message' => 'Optimizasyon ayarlar üzerinden devre dışı bırakılmış.'
            ];
        }

        // 2. TinyPNG denenmesi (hybrid veya tinypng modunda)
        if (in_array($driver, ['hybrid', 'tinypng'], true)) {
            $apiKey = trim($this->settingService->getTinyPngApiKey());

            if (!empty($apiKey)) {
                try {
                    $result = $this->optimizeWithTinyPng($filePath, $apiKey, $resizeDim);
                    Logger::channel('app')->info("Görsel TinyPNG ile optimize edildi", [
                        'file' => basename($filePath),
                        'orig_size' => $origSize,
                        'new_size' => $result['optimized_size'],
                        'saved_percent' => $result['saved_percent']
                    ]);
                    return $result;
                } catch (Throwable $e) {
                    Logger::channel('app')->warning("TinyPNG optimizasyonu başarısız, yerel GD fallback uygulanıyor", [
                        'error' => $e->getMessage(),
                        'file' => basename($filePath)
                    ]);

                    if ($driver === 'tinypng') {
                        // Sadece TinyPNG istendiğinde hata döndür
                        return [
                            'success' => false,
                            'driver' => 'tinypng',
                            'original_size' => $origSize,
                            'optimized_size' => $origSize,
                            'saved_bytes' => 0,
                            'saved_percent' => 0.0,
                            'message' => 'TinyPNG hatası: ' . $e->getMessage()
                        ];
                    }
                }
            } else {
                if ($driver === 'tinypng') {
                    return [
                        'success' => false,
                        'driver' => 'tinypng',
                        'message' => 'TinyPNG API anahtarı tanımlanmamış.'
                    ];
                }
            }
        }

        // 3. Yerel GD ile optimizasyon (hybrid fallback veya doğrudan gd modu)
        try {
            $result = $this->optimizeWithGd($filePath, $resizeDim, $quality);
            Logger::channel('app')->info("Görsel yerel GD ile optimize edildi", [
                'file' => basename($filePath),
                'orig_size' => $origSize,
                'new_size' => $result['optimized_size'],
                'saved_percent' => $result['saved_percent']
            ]);
            return $result;
        } catch (Throwable $e) {
            Logger::channel('app')->error("Yerel GD optimizasyon hatası: " . $e->getMessage(), [
                'file' => basename($filePath)
            ]);

            return [
                'success' => false,
                'driver' => 'gd',
                'original_size' => $origSize,
                'optimized_size' => $origSize,
                'saved_bytes' => 0,
                'saved_percent' => 0.0,
                'message' => 'Yerel görsel optimizasyon hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * TinyPNG API kullanarak görseli sıkıştırır ve boyutlandırır
     */
    private function optimizeWithTinyPng(string $filePath, string $apiKey, int $resizeDim): array
    {
        $origSize = filesize($filePath) ?: 0;
        \Tinify\setKey($apiKey);

        $source = \Tinify\fromFile($filePath);

        // Boyutlandırma gerekiyorsa (1920, 1024 vb.)
        if ($resizeDim > 0) {
            $imageInfo = @getimagesize($filePath);
            $origW = $imageInfo ? $imageInfo[0] : 0;
            $origH = $imageInfo ? $imageInfo[1] : 0;

            // Yalnızca görsel hedef sınırdan büyükse küçült
            if ($origW > $resizeDim || $origH > $resizeDim) {
                if ($origW >= $origH) {
                    $resized = $source->resize([
                        'method' => 'scale',
                        'width' => $resizeDim
                    ]);
                } else {
                    $resized = $source->resize([
                        'method' => 'scale',
                        'height' => $resizeDim
                    ]);
                }
                $resized->toFile($filePath);
            } else {
                $source->toFile($filePath);
            }
        } else {
            $source->toFile($filePath);
        }

        clearstatcache(true, $filePath);
        $newSize = filesize($filePath) ?: 0;
        $savedBytes = max(0, $origSize - $newSize);
        $savedPercent = $origSize > 0 ? round(($savedBytes / $origSize) * 100, 1) : 0.0;

        return [
            'success' => true,
            'driver' => 'tinypng',
            'original_size' => $origSize,
            'optimized_size' => $newSize,
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
            'message' => "TinyPNG ile optimize edildi. %{$savedPercent} tasarruf sağlandı."
        ];
    }

    /**
     * Yerel PHP GD motoru ile görseli boyutlandırır ve sıkıştırır
     */
    public function optimizeWithGd(string $filePath, int $resizeDim = 1920, int $quality = 85): array
    {
        $origSize = filesize($filePath) ?: 0;
        $info = @getimagesize($filePath);

        if (!$info) {
            throw new Exception("Görsel metaverileri okunamadı.");
        }

        $origW = $info[0];
        $origH = $info[1];
        $mime = $info['mime'] ?? '';

        // GIF animasyonlarının bozulmaması için GIF dosyalarını sadece boyut sınırında ise işle
        if ($mime === 'image/gif') {
            return [
                'success' => true,
                'driver' => 'gd',
                'original_size' => $origSize,
                'optimized_size' => $origSize,
                'saved_bytes' => 0,
                'saved_percent' => 0.0,
                'message' => 'GIF dosyaları animasyon koruması nedeniyle dönüştürülmedi.'
            ];
        }

        // Yeni boyutları hesapla (en-boy oranını koru)
        $newW = $origW;
        $newH = $origH;

        if ($resizeDim > 0 && ($origW > $resizeDim || $origH > $resizeDim)) {
            if ($origW >= $origH) {
                $newW = $resizeDim;
                $newH = (int)round(($origH / $origW) * $newW);
            } else {
                $newH = $resizeDim;
                $newW = (int)round(($origW / $origH) * $newH);
            }
        }

        $newW = max(1, $newW);
        $newH = max(1, $newH);

        // Kaynak görseli belleğe al
        $srcImage = null;
        switch ($mime) {
            case 'image/jpeg':
                $srcImage = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = @imagecreatefromwebp($filePath);
                }
                break;
            default:
                break;
        }

        if (!$srcImage) {
            throw new Exception("Görsel GD tarafından işlenemedi (Desteklenmeyen format: {$mime}).");
        }

        // Yeniden boyutlandırma veya sıkıştırma için hedef tuval
        $destImage = imagecreatetruecolor($newW, $newH);

        // PNG ve WebP için alfa şeffaflık kanalı koruması
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $newW, $newH, $transparent);
        }

        // Yüksek kaliteli orantılı kopyalama
        imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Geçici bir hedef dosyaya kaydet (orijinal üzerine güvenli yazım)
        $tempOut = $filePath . '.tmp_opt';

        $saved = false;
        switch ($mime) {
            case 'image/jpeg':
                $saved = imagejpeg($destImage, $tempOut, $quality);
                break;
            case 'image/png':
                // PNG sıkıştırma seviyesi 0-9 arasındadır (9 en yüksek sıkıştırma)
                $pngCompression = (int)round((100 - $quality) / 10);
                $pngCompression = max(0, min(9, $pngCompression));
                $saved = imagepng($destImage, $tempOut, $pngCompression);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    $saved = imagewebp($destImage, $tempOut, $quality);
                }
                break;
        }

        // Belleği temizle
        imagedestroy($srcImage);
        imagedestroy($destImage);

        if (!$saved || !file_exists($tempOut)) {
            if (file_exists($tempOut)) {
                @unlink($tempOut);
            }
            throw new Exception("Görsel diske yazılamadı.");
        }

        $newSize = filesize($tempOut) ?: $origSize;

        // Eğer optimize edilen dosya orijinalinden daha küçükse veya boyut küçültülmüşse değiştir
        if ($newSize < $origSize || ($newW !== $origW || $newH !== $origH)) {
            rename($tempOut, $filePath);
            $finalSize = $newSize;
        } else {
            // Sıkıştırma dosyayı büyüttüyse orijinali koru
            @unlink($tempOut);
            $finalSize = $origSize;
        }

        clearstatcache(true, $filePath);
        $savedBytes = max(0, $origSize - $finalSize);
        $savedPercent = $origSize > 0 ? round(($savedBytes / $origSize) * 100, 1) : 0.0;

        return [
            'success' => true,
            'driver' => 'gd',
            'original_size' => $origSize,
            'optimized_size' => $finalSize,
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
            'message' => "Yerel GD ile optimize edildi. %{$savedPercent} tasarruf sağlandı."
        ];
    }

    /**
     * Verilen veya kayıtlı TinyPNG API anahtarını test eder
     */
    public function testApiKey(?string $apiKey = null): array
    {
        $key = trim($apiKey ?? $this->settingService->getTinyPngApiKey());

        if (empty($key)) {
            return [
                'valid' => false,
                'count' => 0,
                'message' => 'API anahtarı girilmemiş.'
            ];
        }

        try {
            \Tinify\setKey($key);
            \Tinify\validate();
            $count = \Tinify\compressionCount() ?? 0;

            return [
                'valid' => true,
                'count' => $count,
                'message' => "Bağlantı başarılı! Bu ay kullanılan sıkıştırma sayısı: {$count}"
            ];
        } catch (AccountException $e) {
            return [
                'valid' => false,
                'count' => 0,
                'message' => 'Hesap hatası veya geçersiz API anahtarı: ' . $e->getMessage()
            ];
        } catch (ConnectionException $e) {
            return [
                'valid' => false,
                'count' => 0,
                'message' => 'TinyPNG sunucularına bağlanılamadı (Ağ hatası): ' . $e->getMessage()
            ];
        } catch (Throwable $e) {
            return [
                'valid' => false,
                'count' => 0,
                'message' => 'Beklenmeyen hata: ' . $e->getMessage()
            ];
        }
    }
}
