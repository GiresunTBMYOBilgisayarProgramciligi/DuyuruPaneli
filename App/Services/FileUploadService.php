<?php
declare(strict_types=1);

namespace App\Services;

use App\Config;
use Exception;

class FileUploadService
{
    /**
     * Yüklenen dosyayı doğrular, benzersiz isimle kaydeder ve göreceli web yolunu döndürür
     */
    public function uploadImage(array $file): string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Yüklenecek geçerli bir dosya bulunamadı.");
        }

        if ($file['size'] > Config::MAX_IMAGE_SIZE_BYTES) {
            throw new Exception("Dosya boyutu çok büyük (Maksimum 10MB).");
        }

        // Uzantı kontrolü
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, Config::ALLOWED_IMAGE_EXTENSIONS, true)) {
            throw new Exception("Geçersiz dosya uzantısı. Sadece JPG, PNG, GIF veya WEBP yüklenebilir.");
        }

        // Gerçek MIME kontrolü (finfo)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, Config::ALLOWED_IMAGE_MIMES, true)) {
            throw new Exception("Geçersiz dosya formatı. Gerçek bir görsel dosyası seçiniz.");
        }

        // Benzersiz rastgele dosya adı üretimi
        $newFilename = bin2hex(random_bytes(10)) . '.' . $extension;
        $targetPath = Config::UPLOAD_DIR . $newFilename;

        if (!is_dir(Config::UPLOAD_DIR)) {
            mkdir(Config::UPLOAD_DIR, 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Dosya sunucuya taşınırken bir hata oluştu.");
        }

        // Web üzerinden erişilebilir göreceli yol
        return Config::UPLOAD_URL_PREFIX . $newFilename;
    }

    /**
     * Eski görsel dosyasını diskten siler
     */
    public function deleteImage(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $filename = basename($relativePath);
        $filePath = Config::UPLOAD_DIR . $filename;

        if (file_exists($filePath) && is_file($filePath)) {
            @unlink($filePath);
        } else {
            // Geriye dönük uyumluluk: Eski images/ dizinini de kontrol et
            $legacyPath = Config::ROOT_PATH . 'images/' . $filename;
            if (file_exists($legacyPath) && is_file($legacyPath)) {
                @unlink($legacyPath);
            }
        }
    }

    /**
     * YouTube video kimliğine göre kapak görselini indirir ve yerel uploads dizinine kaydeder
     */
    public function downloadYouTubeThumbnail(string $videoId): string
    {
        $safeVideoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);
        if (empty($safeVideoId)) {
            return '';
        }

        $filename = 'yt_' . $safeVideoId . '.jpg';
        $targetPath = Config::UPLOAD_DIR . $filename;

        // Dosya zaten indirilmiş ve geçerliyse doğrudan yolunu dön
        if (file_exists($targetPath) && filesize($targetPath) > 1000) {
            return Config::UPLOAD_URL_PREFIX . $filename;
        }

        if (!is_dir(Config::UPLOAD_DIR)) {
            mkdir(Config::UPLOAD_DIR, 0775, true);
        }

        $resolutions = [
            "https://img.youtube.com/vi/{$safeVideoId}/maxresdefault.jpg",
            "https://img.youtube.com/vi/{$safeVideoId}/hqdefault.jpg",
            "https://img.youtube.com/vi/{$safeVideoId}/mqdefault.jpg",
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);

        foreach ($resolutions as $url) {
            $data = @file_get_contents($url, false, $context);
            if ($data !== false && strlen($data) > 1000) {
                if (file_put_contents($targetPath, $data) !== false) {
                    @chmod($targetPath, 0666);
                    return Config::UPLOAD_URL_PREFIX . $filename;
                }
            }
        }

        // İnternet kesintisi veya YouTube erişim engeli durumunda yerel logo fallback
        $fallbackLogo = Config::ROOT_PATH . 'assets/images/logo_230x230.png';
        if (file_exists($fallbackLogo)) {
            return '/assets/images/logo_230x230.png';
        }

        return '';
    }
}
