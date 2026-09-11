<?php
declare(strict_types=1);

namespace App\Validators;

use App\DTO\SettingDTO;

class SettingValidator
{
    /**
     * Sistem ayarlarını doğrular, hata varsa hata mesajı döner
     */
    public static function validate(SettingDTO $dto): ?string
    {
        $allowedDrivers = ['hybrid', 'tinypng', 'gd', 'off'];
        if (!in_array($dto->imageDriver, $allowedDrivers, true)) {
            return "Geçersiz görsel optimizasyon sürücüsü seçildi.";
        }

        if ($dto->imageResizeDimension < 0 || $dto->imageResizeDimension > 4000) {
            return "Geçersiz yeniden boyutlandırma boyutu. (0 ile 4000 arasında olmalıdır).";
        }

        if ($dto->imageQuality < 40 || $dto->imageQuality > 100) {
            return "Görsel kalitesi %40 ile %100 arasında olmalıdır.";
        }

        if ($dto->slideInterval < 5 || $dto->slideInterval > 600) {
            return "Slayt geçiş süresi 5 ile 600 saniye arasında olmalıdır.";
        }

        if (!empty($dto->weatherLatitude) && !is_numeric($dto->weatherLatitude)) {
            return "Hava durumu enlem değeri sayısal bir değer olmalıdır.";
        }

        if (!empty($dto->weatherLongitude) && !is_numeric($dto->weatherLongitude)) {
            return "Hava durumu boylam değeri sayısal bir değer olmalıdır.";
        }

        if (mb_strlen($dto->institutionName) > 255) {
            return "Kurum adı en fazla 255 karakter olabilir.";
        }

        $allowedShortenerProviders = ['auto', 'bitly', 'tinyurl', 'isgd', 'internal'];
        if (!in_array($dto->urlShortenerProvider, $allowedShortenerProviders, true)) {
            return "Geçersiz URL kısaltma servis sağlayıcısı seçildi.";
        }

        if ($dto->bitlyAccessToken !== null && mb_strlen($dto->bitlyAccessToken) > 255) {
            return "Bitly API erişim belirteci (token) en fazla 255 karakter olabilir.";
        }

        if ($dto->tinyUrlApiKey !== null && mb_strlen($dto->tinyUrlApiKey) > 255) {
            return "TinyURL API anahtarı en fazla 255 karakter olabilir.";
        }

        return null;
    }
}
