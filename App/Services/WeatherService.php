<?php
declare(strict_types=1);

namespace App\Services;

use App\Config;

class WeatherService
{
    private string $cacheFile;

    public function __construct()
    {
        $this->cacheFile = Config::ROOT_PATH . "db/weather_cache.json";
    }

    /**
     * Güncel hava durumu bilgilerini döndürür (önbellek destekli)
     */
    public function getCurrentWeather(): array
    {
        if (!Config::MODULE_WEATHER) {
            return ['enabled' => false];
        }

        // Önbellek kontrolü
        $cached = $this->readCache();
        if ($cached !== null && (time() - $cached['timestamp']) < Config::WEATHER_CACHE_TTL) {
            return $cached['data'];
        }

        // Yeni veri çek
        $freshData = $this->fetchFromApi();
        if ($freshData !== null) {
            $this->writeCache($freshData);
            return $freshData;
        }

        // API başarısızsa ve eski önbellek varsa onu dön
        if ($cached !== null) {
            return $cached['data'];
        }

        // Tamamen çevrimdışı ve önbelleksiz durum için varsayılan fallback
        return $this->getDefaultFallback();
    }

    /**
     * Open-Meteo REST API üzerinden güncel hava durumunu çeker
     */
    private function fetchFromApi(): ?array
    {
        $lat = Config::WEATHER_LATITUDE;
        $lon = Config::WEATHER_LONGITUDE;
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m&timezone=auto";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'UniPano-Kiosk/2.1');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $json = json_decode((string)$response, true);
        if (!$json || !isset($json['current'])) {
            return null;
        }

        $current = $json['current'];
        $weatherCode = (int)($current['weather_code'] ?? 0);
        $codeMeta = $this->mapWeatherCode($weatherCode);

        $temp = round((float)$current['temperature_2m'], 1);
        $tempInt = (int)round((float)$current['temperature_2m']);
        $humidity = (int)($current['relative_humidity_2m'] ?? 0);
        $windSpeed = round((float)($current['wind_speed_10m'] ?? 0), 1);
        $apparentTemp = (int)round((float)($current['apparent_temperature'] ?? $temp));

        return [
            'enabled' => true,
            'city' => Config::WEATHER_CITY,
            'temperature' => $tempInt,
            'temperatureFormatted' => "{$tempInt}°C",
            'apparentTemperature' => $apparentTemp,
            'humidity' => $humidity,
            'windSpeed' => $windSpeed,
            'weatherCode' => $weatherCode,
            'condition' => $codeMeta['text'],
            'iconType' => $codeMeta['icon'],
            'iconSvg' => $this->getSvgIcon($codeMeta['icon']),
            'updatedAt' => date('H:i')
        ];
    }

    /**
     * WMO hava kodu karşılıkları
     */
    private function mapWeatherCode(int $code): array
    {
        return match (true) {
            $code === 0 => [
                'text' => 'Açık / Güneşli',
                'icon' => 'sun'
            ],
            $code === 1 => [
                'text' => 'Çoğunlukla Açık',
                'icon' => 'sun-cloud'
            ],
            $code === 2 => [
                'text' => 'Parçalı Bulutlu',
                'icon' => 'partly-cloudy'
            ],
            $code === 3 => [
                'text' => 'Bulutlu',
                'icon' => 'cloudy'
            ],
            in_array($code, [45, 48], true) => [
                'text' => 'Sisli',
                'icon' => 'fog'
            ],
            in_array($code, [51, 53, 55, 56, 57], true) => [
                'text' => 'Hafif Çisenti',
                'icon' => 'drizzle'
            ],
            in_array($code, [61, 63, 65, 66, 67], true) => [
                'text' => 'Yağmurlu',
                'icon' => 'rain'
            ],
            in_array($code, [71, 73, 75, 77, 85, 86], true) => [
                'text' => 'Karlı',
                'icon' => 'snow'
            ],
            in_array($code, [80, 81, 82], true) => [
                'text' => 'Sağanak Yağışlı',
                'icon' => 'showers'
            ],
            in_array($code, [95, 96, 99], true) => [
                'text' => 'Gök Gürültülü Fırtına',
                'icon' => 'thunderstorm'
            ],
            default => [
                'text' => 'Parçalı Bulutlu',
                'icon' => 'partly-cloudy'
            ]
        };
    }

    /**
     * Modern, minimalist SVG Hava İkonları
     */
    public function getSvgIcon(string $iconType): string
    {
        return match ($iconType) {
            'sun' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>',
            'partly-cloudy', 'sun-cloud' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="#f59e0b" d="M12 2v2M4.93 4.93l1.41 1.41M20 12h2M19.07 4.93l-1.41 1.41M15.95 9.05A6 6 0 0 0 6.06 13.5"></path><path stroke="#64748b" fill="#f1f5f9" d="M17.5 19H9a5 5 0 0 1-.7-9.95A6 6 0 0 1 17.5 10a4 4 0 0 1 0 8z"></path></svg>',
            'cloudy' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="#f1f5f9" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path></svg>',
            'rain', 'showers', 'drizzle' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="#64748b" fill="#f1f5f9" d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path><line stroke="#3b82f6" x1="8" y1="19" x2="7" y2="23"></line><line stroke="#3b82f6" x1="12" y1="19" x2="11" y2="23"></line><line stroke="#3b82f6" x1="16" y1="19" x2="15" y2="23"></line></svg>',
            'snow' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="#64748b" fill="#f8fafc" d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path><circle cx="8" cy="21" r="1" fill="#38bdf8"></circle><circle cx="12" cy="21" r="1" fill="#38bdf8"></circle><circle cx="16" cy="21" r="1" fill="#38bdf8"></circle></svg>',
            'thunderstorm' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="#475569" fill="#e2e8f0" d="M19 11h-1.26A8 8 0 1 0 9 20h10a4 4 0 0 0 0-8z"></path><polygon stroke="#eab308" fill="#eab308" points="13 14 10 19 13 19 11 23 16 17 13 17 15 14"></polygon></svg>',
            'fog' => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="9" x2="21" y2="9"></line><line x1="4" y1="13" x2="20" y2="13"></line><line x1="6" y1="17" x2="18" y2="17"></line><line x1="8" y1="21" x2="16" y2="21"></line></svg>',
            default => '<svg class="weather-svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line></svg>'
        };
    }

    private function readCache(): ?array
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }

        $content = @file_get_contents($this->cacheFile);
        if (!$content) {
            return null;
        }

        $json = json_decode($content, true);
        if (!$json || !isset($json['timestamp'], $json['data'])) {
            return null;
        }

        return $json;
    }

    private function writeCache(array $data): void
    {
        $payload = json_encode([
            'timestamp' => time(),
            'data' => $data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        @file_put_contents($this->cacheFile, $payload, LOCK_EX);
        @chmod($this->cacheFile, 0666);
    }

    private function getDefaultFallback(): array
    {
        return [
            'enabled' => true,
            'city' => Config::WEATHER_CITY,
            'temperature' => 22,
            'temperatureFormatted' => "22°C",
            'apparentTemperature' => 22,
            'humidity' => 60,
            'windSpeed' => 5.0,
            'weatherCode' => 2,
            'condition' => 'Parçalı Bulutlu',
            'iconType' => 'partly-cloudy',
            'iconSvg' => $this->getSvgIcon('partly-cloudy'),
            'updatedAt' => date('H:i')
        ];
    }
}
