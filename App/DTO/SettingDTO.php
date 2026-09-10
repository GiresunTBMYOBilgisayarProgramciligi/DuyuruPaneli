<?php
declare(strict_types=1);

namespace App\DTO;

class SettingDTO
{
    public function __construct(
        public ?string $tinyPngApiKey = null,
        public string $imageDriver = 'hybrid',
        public int $imageResizeDimension = 1920,
        public int $imageQuality = 85,
        public int $slideInterval = 20,
        public bool $kioskVideoSound = true,
        public string $institutionName = '',
        public string $campusName = '',
        public string $appTagline = '',
        public string $weatherCity = 'Tirebolu',
        public string $weatherLatitude = '41.0064',
        public string $weatherLongitude = '38.8142',
        public bool $moduleWeather = true,
        public bool $moduleClock = true,
        public bool $moduleTicker = true,
        public bool $moduleQrAnalytics = true,
        public ?string $bitlyAccessToken = null,
        public string $urlShortenerProvider = 'auto'
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tinyPngApiKey: isset($data['tinypng_api_key']) ? trim((string)$data['tinypng_api_key']) : null,
            imageDriver: (string)($data['image_driver'] ?? 'hybrid'),
            imageResizeDimension: isset($data['image_resize_dimension']) ? (int)$data['image_resize_dimension'] : 1920,
            imageQuality: isset($data['image_quality']) ? (int)$data['image_quality'] : 85,
            slideInterval: isset($data['slide_interval']) ? (int)$data['slide_interval'] : 20,
            kioskVideoSound: !empty($data['kiosk_video_sound']),
            institutionName: trim((string)($data['institution_name'] ?? '')),
            campusName: trim((string)($data['campus_name'] ?? '')),
            appTagline: trim((string)($data['app_tagline'] ?? '')),
            weatherCity: trim((string)($data['weather_city'] ?? 'Tirebolu')),
            weatherLatitude: trim((string)($data['weather_latitude'] ?? '41.0064')),
            weatherLongitude: trim((string)($data['weather_longitude'] ?? '38.8142')),
            moduleWeather: !empty($data['module_weather']),
            moduleClock: !empty($data['module_clock']),
            moduleTicker: !empty($data['module_ticker']),
            moduleQrAnalytics: !empty($data['module_qr_analytics']),
            bitlyAccessToken: isset($data['bitly_access_token']) ? trim((string)$data['bitly_access_token']) : null,
            urlShortenerProvider: (string)($data['url_shortener_provider'] ?? 'auto')
        );
    }

    public function toArray(): array
    {
        $arr = [
            'image_driver' => $this->imageDriver,
            'image_resize_dimension' => (string)$this->imageResizeDimension,
            'image_quality' => (string)$this->imageQuality,
            'slide_interval' => (string)$this->slideInterval,
            'kiosk_video_sound' => $this->kioskVideoSound ? '1' : '0',
            'institution_name' => $this->institutionName,
            'campus_name' => $this->campusName,
            'app_tagline' => $this->appTagline,
            'weather_city' => $this->weatherCity,
            'weather_latitude' => $this->weatherLatitude,
            'weather_longitude' => $this->weatherLongitude,
            'module_weather' => $this->moduleWeather ? '1' : '0',
            'module_clock' => $this->moduleClock ? '1' : '0',
            'module_ticker' => $this->moduleTicker ? '1' : '0',
            'module_qr_analytics' => $this->moduleQrAnalytics ? '1' : '0',
            'url_shortener_provider' => $this->urlShortenerProvider,
        ];

        // API anahtarı boş bırakılmamışsa veya açıkça gönderildiyse güncelle
        if ($this->tinyPngApiKey !== null) {
            $arr['tinypng_api_key'] = $this->tinyPngApiKey;
        }

        if ($this->bitlyAccessToken !== null) {
            $arr['bitly_access_token'] = $this->bitlyAccessToken;
        }

        return $arr;
    }
}
