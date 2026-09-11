<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use Exception;

class UrlShortenerService
{
    private SettingService $settingService;

    public function __construct(?SettingService $settingService = null)
    {
        $this->settingService = $settingService ?? new SettingService();
    }

    /**
     * Verilen URL'yi yapılandırılmış servis sağlayıcısı ile ultra kısa linke dönüştürür.
     * 
     * @param string $redirectUrl Dahili yönlendirme URL'si (ör. https://unipano.gencbilisim.net/r/nQhGL3)
     * @param string $targetUrl Asıl hedef URL (Yerel geliştirme ortamında harici servise göndermek için)
     * @return string Kısaltılmış URL veya hata durumunda orijinal URL
     */
    public function shorten(string $redirectUrl, string $targetUrl = ''): string
    {
        $provider = strtolower($this->settingService->getString('url_shortener_provider', 'auto'));
        
        // Sağlayıcı 'internal' (yalnızca yerel) seçilmişse harici servislere gitme
        if ($provider === 'internal') {
            return $redirectUrl;
        }

        // Kısaltılacak URL'yi belirle
        $urlToShorten = $this->determineUrlToShorten($redirectUrl, $targetUrl);
        if (empty($urlToShorten)) {
            return $redirectUrl;
        }

        $bitlyToken = trim($this->settingService->getString('bitly_access_token', ''));
        $tinyUrlApiKey = trim($this->settingService->getString('tinyurl_api_key', ''));

        // 1. Bitly (Öncelikli veya açıkça seçilmişse)
        if (($provider === 'bitly' || $provider === 'auto') && !empty($bitlyToken)) {
            $shortened = $this->shortenWithBitly($urlToShorten, $bitlyToken);
            if ($shortened !== null) {
                return $shortened;
            }
            // Bitly açıkça seçilmişse ve başarısız olduysa devam etme
            if ($provider === 'bitly') {
                Logger::channel('qr')->warning("Bitly kısaltma başarısız oldu ve sağlayıcı 'bitly' olarak sabitlenmiş.");
                return $redirectUrl;
            }
        }

        // 2. TinyURL (API Anahtarlı veya Anonim)
        if ($provider === 'auto' || $provider === 'tinyurl') {
            $shortened = $this->shortenWithTinyUrl($urlToShorten, !empty($tinyUrlApiKey) ? $tinyUrlApiKey : null);
            if ($shortened !== null) {
                return $shortened;
            }
            if ($provider === 'tinyurl') {
                return $redirectUrl;
            }
        }

        // 3. is.gd (Alternatif acil durum yedeği)
        if ($provider === 'auto' || $provider === 'isgd') {
            $shortened = $this->shortenWithIsGd($urlToShorten);
            if ($shortened !== null) {
                return $shortened;
            }
        }

        // 4. Fallback: Harici servisler başarısız olduysa dahili yönlendirmeyi dön
        return $redirectUrl;
    }

    /**
     * Bitly API v4 ile URL kısaltır
     */
    public function shortenWithBitly(string $url, string $accessToken): ?string
    {
        try {
            $ch = curl_init('https://api-ssl.bitly.com/v4/shorten');
            if ($ch === false) {
                return null;
            }

            $payload = json_encode(['long_url' => $url], JSON_UNESCAPED_SLASHES);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || !empty($error)) {
                Logger::channel('qr')->warning("Bitly API cURL hatası: {$error}");
                return null;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode((string)$response, true);
                if (isset($data['link']) && is_string($data['link'])) {
                    Logger::channel('qr')->info("Bitly ile link başarıyla kısaltıldı", [
                        'original' => $url,
                        'short' => $data['link']
                    ]);
                    return $data['link'];
                }
            }

            Logger::channel('qr')->warning("Bitly API yanıt hatası (HTTP {$httpCode}): {$response}");
            return null;
        } catch (Exception $e) {
            Logger::channel('qr')->error("Bitly servisinde beklenmeyen hata: " . $e->getMessage());
            return null;
        }
    }

    /**
     * TinyURL servisi ile URL kısaltır.
     * API anahtarı sağlanmışsa veya ayarlarda tanımlıysa TinyURL API v2 (Bearer token) kullanılır;
     * tanımlı değilse veya v2 başarısız olursa anonim genel servis kullanılır.
     */
    public function shortenWithTinyUrl(string $url, ?string $apiKey = null): ?string
    {
        $token = $apiKey !== null ? trim($apiKey) : trim($this->settingService->getString('tinyurl_api_key', ''));

        // 1. API Anahtarı tanımlıysa TinyURL API v2 motoru ile kısalt
        if (!empty($token)) {
            $shortUrl = $this->shortenWithTinyUrlV2($url, $token);
            if ($shortUrl !== null) {
                return $shortUrl;
            }
            // API v2 başarısız olursa TV panosunun kesintisiz çalışması için anonim servise fallback yap
            Logger::channel('qr')->warning("TinyURL API v2 başarısız oldu, anonim genel servise geçiliyor.");
        }

        // 2. API anahtarı yoksa veya v2 başarısız olduysa anonim servis motoru
        return $this->shortenWithTinyUrlAnonymous($url);
    }

    /**
     * TinyURL API v2 ile URL kısaltır (Bearer Token yetkilendirmesi ile)
     * Dokümantasyon: https://tinyurl.com/app/settings/api
     */
    public function shortenWithTinyUrlV2(string $url, string $apiToken): ?string
    {
        try {
            $ch = curl_init('https://api.tinyurl.com/create');
            if ($ch === false) {
                return null;
            }

            $payload = json_encode([
                'url' => $url,
                'domain' => 'tinyurl.com'
            ], JSON_UNESCAPED_SLASHES);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'UniPano/2.0 (Digital Signage Platform)'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || !empty($error)) {
                Logger::channel('qr')->warning("TinyURL API v2 cURL hatası: {$error}");
                return null;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode((string)$response, true);
                if (isset($data['data']['tiny_url']) && is_string($data['data']['tiny_url'])) {
                    Logger::channel('qr')->info("TinyURL v2 ile link başarıyla kısaltıldı", [
                        'original' => $url,
                        'short' => $data['data']['tiny_url']
                    ]);
                    return $data['data']['tiny_url'];
                }
            }

            Logger::channel('qr')->warning("TinyURL API v2 yanıt hatası (HTTP {$httpCode}): {$response}");
            return null;
        } catch (Exception $e) {
            Logger::channel('qr')->error("TinyURL API v2 servisinde hata: " . $e->getMessage());
            return null;
        }
    }

    /**
     * TinyURL anonim genel API ile URL kısaltır (API anahtarı gerektirmez)
     */
    public function shortenWithTinyUrlAnonymous(string $url): ?string
    {
        try {
            $apiUrl = 'https://tinyurl.com/api-create.php?url=' . urlencode($url);
            $ch = curl_init($apiUrl);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'UniPano/2.0 (Digital Signage Platform)'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || !empty($error) || $httpCode !== 200) {
                Logger::channel('qr')->warning("TinyURL anonim API hatası (HTTP {$httpCode}): {$error}");
                return null;
            }

            $shortUrl = trim((string)$response);
            if (str_starts_with($shortUrl, 'http://') || str_starts_with($shortUrl, 'https://')) {
                Logger::channel('qr')->info("TinyURL anonim servis ile link başarıyla kısaltıldı", [
                    'original' => $url,
                    'short' => $shortUrl
                ]);
                return $shortUrl;
            }

            return null;
        } catch (Exception $e) {
            Logger::channel('qr')->error("TinyURL anonim servisinde hata: " . $e->getMessage());
            return null;
        }
    }

    /**
     * is.gd API ile URL kısaltır
     */
    public function shortenWithIsGd(string $url): ?string
    {
        try {
            $apiUrl = 'https://is.gd/create.php?format=simple&url=' . urlencode($url);
            $ch = curl_init($apiUrl);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'UniPano/2.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                $shortUrl = trim((string)$response);
                if (str_starts_with($shortUrl, 'http://') || str_starts_with($shortUrl, 'https://')) {
                    return $shortUrl;
                }
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Kısaltma bağlantılarını ve servis sağlayıcıları (Bitly ve TinyURL) test eder
     */
    public function testService(?string $bitlyToken = null, ?string $tinyUrlToken = null): array
    {
        $testTarget = 'https://giresun.edu.tr';
        $bitlyTokenToTest = $bitlyToken !== null ? trim($bitlyToken) : trim($this->settingService->getString('bitly_access_token', ''));
        $tinyUrlTokenToTest = $tinyUrlToken !== null ? trim($tinyUrlToken) : trim($this->settingService->getString('tinyurl_api_key', ''));

        $results = [
            'success' => false,
            'bitly' => [
                'configured' => !empty($bitlyTokenToTest),
                'success' => false,
                'shortUrl' => null,
                'message' => ''
            ],
            'tinyurl' => [
                'configured' => !empty($tinyUrlTokenToTest),
                'authenticated' => false,
                'success' => false,
                'shortUrl' => null,
                'message' => ''
            ],
            'active_provider' => $this->settingService->getString('url_shortener_provider', 'auto'),
            'summary' => ''
        ];

        // 1. Bitly testi
        if (!empty($bitlyTokenToTest)) {
            $bitlyUrl = $this->shortenWithBitly($testTarget, $bitlyTokenToTest);
            if ($bitlyUrl !== null) {
                $results['bitly']['success'] = true;
                $results['bitly']['shortUrl'] = $bitlyUrl;
                $results['bitly']['message'] = 'Bitly API v4 bağlantısı ve yetkilendirmesi başarılı.';
            } else {
                $results['bitly']['message'] = 'Bitly API bağlantısı başarısız oldu. Token veya kotayı kontrol ediniz.';
            }
        } else {
            $results['bitly']['message'] = 'Bitly API Token tanımlı değil.';
        }

        // 2. TinyURL testi
        if (!empty($tinyUrlTokenToTest)) {
            // API v2 anahtarlı test
            $tinyUrl = $this->shortenWithTinyUrlV2($testTarget, $tinyUrlTokenToTest);
            if ($tinyUrl !== null) {
                $results['tinyurl']['success'] = true;
                $results['tinyurl']['authenticated'] = true;
                $results['tinyurl']['shortUrl'] = $tinyUrl;
                $results['tinyurl']['message'] = 'TinyURL API v2 bağlantısı ve API anahtarı doğrulaması başarılı.';
            } else {
                $results['tinyurl']['message'] = 'TinyURL API v2 bağlantısı başarısız. API anahtarınızı veya hesap durumunuzu kontrol ediniz.';
            }
        } else {
            // Anonim genel servis testi
            $tinyUrl = $this->shortenWithTinyUrlAnonymous($testTarget);
            if ($tinyUrl !== null) {
                $results['tinyurl']['success'] = true;
                $results['tinyurl']['authenticated'] = false;
                $results['tinyurl']['shortUrl'] = $tinyUrl;
                $results['tinyurl']['message'] = 'TinyURL genel servisi (API anahtarsız anonim mod) sorunsuz çalışıyor.';
            } else {
                $results['tinyurl']['message'] = 'TinyURL genel servisine ulaşılamadı.';
            }
        }

        if ($results['bitly']['success'] || $results['tinyurl']['success']) {
            $results['success'] = true;
            $sample = $results['bitly']['shortUrl'] ?? $results['tinyurl']['shortUrl'];
            $statusParts = [];
            if ($results['bitly']['success']) {
                $statusParts[] = "Bitly: Aktif ({$results['bitly']['shortUrl']})";
            }
            if ($results['tinyurl']['success']) {
                $tinyMode = $results['tinyurl']['authenticated'] ? 'API Anahtarlı' : 'Anonim';
                $statusParts[] = "TinyURL [{$tinyMode}]: Aktif ({$results['tinyurl']['shortUrl']})";
            }
            $results['summary'] = "Kısaltma servisi bağlantısı başarılı! " . implode(' | ', $statusParts);
        } else {
            $results['summary'] = "Kısaltma servislerine bağlanılamadı. Sistem dahili linklerle kesintisiz çalışmaya devam eder.";
        }

        return $results;
    }

    /**
     * Dış servise gönderilecek URL'yi belirler.
     * Harici kısaltıcılar localhost veya .loc gibi yerel IP ve alan adlarını reddettiği için
     * sistem yerel modda iken varsa public hedef URL gönderilir.
     */
    private function determineUrlToShorten(string $redirectUrl, string $targetUrl): string
    {
        $redirectIsLocal = $this->isLocalAddress($redirectUrl);

        // Eğer sistem public bir alan adındaysa (ör. unipano.gencbilisim.net), doğrudan yönlendirme linkini kısalt!
        if (!$redirectIsLocal) {
            return $redirectUrl;
        }

        // Eğer yereldeysek ve hedef URL public bir internet adresi ise hedef URL'yi kısalt
        if (!empty($targetUrl) && !$this->isLocalAddress($targetUrl)) {
            return $targetUrl;
        }

        // Her ikisi de yerelse harici kısaltıcıya gönderme
        return '';
    }

    /**
     * Verilen URL'nin yerel bir ağ/geliştirme adresi olup olmadığını denetler (SSRF ve Local Host koruması)
     */
    public function isLocalAddress(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            return true;
        }

        $host = strtolower($host);

        // Yerel domain uzantıları ve adları
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        if (str_ends_with($host, '.loc') || str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return true;
        }

        // Özel/Yerel IP blokları (10.x, 192.168.x, 172.16-31.x)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return true;
            }
        }

        return false;
    }
}
