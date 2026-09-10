<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ShortLinkRepository;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrAnalyticsService
{
    private ShortLinkRepository $shortLinkRepository;
    private UrlShortenerService $urlShortenerService;

    public function __construct(
        ?ShortLinkRepository $shortLinkRepository = null,
        ?UrlShortenerService $urlShortenerService = null
    ) {
        $this->shortLinkRepository = $shortLinkRepository ?? new ShortLinkRepository();
        $this->urlShortenerService = $urlShortenerService ?? new UrlShortenerService();
    }

    /**
     * Hedef URL için kısa link üretir, harici servis ile kısaltır ve yerel ultra net SVG QR kod döndürür.
     * 
     * @param string $targetUrl Kullanıcının girdiği asıl web adresi
     * @param string $title İlgili duyuru veya slayt başlığı
     * @param bool $forceNewShortUrl Mevcut harici kısa URL'yi yeniden üretmeye zorla
     * @return array [shortCode, externalShortUrl, qrSvg, redirectUrl, qrPayload]
     */
    public function generateForUrl(string $targetUrl, string $title = '', bool $forceNewShortUrl = false): array
    {
        $targetUrl = trim($targetUrl);
        if (empty($targetUrl)) {
            return [
                'shortCode' => null,
                'externalShortUrl' => null,
                'qrSvg' => '',
                'redirectUrl' => '',
                'qrPayload' => ''
            ];
        }

        // 1. Yerel kısa link kaydı al veya oluştur (analitik takip kimliği)
        $shortLink = $this->shortLinkRepository->getOrCreate($targetUrl, $title);

        // 2. Dinamik host adresine göre yönlendirme URL'si oluştur
        $host = $_SERVER['HTTP_HOST'] ?? 'unipano.loc';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $redirectUrl = "{$scheme}://{$host}/r/{$shortLink->code}";

        // 3. Ultra kısa link üretimi (Bitly / TinyURL / is.gd / Dahili)
        $externalShortUrl = $shortLink->externalShortUrl ?? null;
        if ($forceNewShortUrl || empty($externalShortUrl)) {
            $shortened = $this->urlShortenerService->shorten($redirectUrl, $targetUrl);
            if (!empty($shortened) && $shortened !== $redirectUrl) {
                $externalShortUrl = $shortened;
                $this->shortLinkRepository->updateExternalShortUrl((int)$shortLink->id, $externalShortUrl);
            }
        }

        // 4. QR kod içeriğini belirle (Harici ultra kısa link varsa matris Version 1/2'ye iner ve pikseller devleşir!)
        $qrPayload = (!empty($externalShortUrl)) ? $externalShortUrl : $redirectUrl;

        // 5. BaconQrCode ile yerel SVG QR kod üret
        // Düşük çözünürlüklü TV ekranlarında modül boyutunu maksimize etmek için:
        // - size: 120 (yüksek çözünürlüklü SVG viewBox koordinatları)
        // - margin: 1 (iç sessiz alan boşluğunu minimize ederek matris alanını genişletir)
        $renderer = new ImageRenderer(
            new RendererStyle(120, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $rawSvg = $writer->writeString($qrPayload);

        // XML declaration'ı temizle, doğrudan DOM'a gömülebilir SVG bırak
        $cleanSvg = substr($rawSvg, strpos($rawSvg, "\n") + 1);

        // Düşük çözünürlüklü ekranlarda anti-aliasing bulanıklığını önlemek için jilet gibi keskin kenar (crispEdges) oluştur
        if (strpos($cleanSvg, '<svg') !== false && strpos($cleanSvg, 'shape-rendering') === false) {
            $cleanSvg = preg_replace('/<svg /', '<svg shape-rendering="crispEdges" ', $cleanSvg, 1);
        }

        return [
            'shortCode' => $shortLink->code,
            'externalShortUrl' => $externalShortUrl,
            'qrSvg' => $cleanSvg,
            'redirectUrl' => $redirectUrl,
            'qrPayload' => $qrPayload
        ];
    }

    /**
     * Veritabanındaki tüm aktif duyuru ve slaytların QR kodlarını yeni kısaltma ve netlik standartlarıyla günceller
     */
    public function regenerateExistingQrCodes(bool $forceNewShortUrls = true): int
    {
        $db = Database::getConnection();
        $updatedCount = 0;

        // 1. Duyurular
        $stmt = $db->query("SELECT id, link, title FROM announcement WHERE link IS NOT NULL AND trim(link) != ''");
        $announcements = $stmt->fetchAll();
        foreach ($announcements as $ann) {
            $qrData = $this->generateForUrl((string)$ann->link, (string)($ann->title ?? ''), $forceNewShortUrls);
            if (!empty($qrData['qrSvg'])) {
                $upd = $db->prepare("
                    UPDATE announcement 
                    SET qrCode = :qr, shortCode = :sc, externalShortUrl = :ext 
                    WHERE id = :id
                ");
                $upd->execute([
                    ':qr' => $qrData['qrSvg'],
                    ':sc' => $qrData['shortCode'],
                    ':ext' => $qrData['externalShortUrl'],
                    ':id' => $ann->id
                ]);
                $updatedCount++;
            }
        }

        // 2. Slaytlar
        $stmt = $db->query("SELECT id, link, title FROM slider WHERE link IS NOT NULL AND trim(link) != ''");
        $slides = $stmt->fetchAll();
        foreach ($slides as $sl) {
            $qrData = $this->generateForUrl((string)$sl->link, (string)($sl->title ?? ''), $forceNewShortUrls);
            if (!empty($qrData['qrSvg'])) {
                $upd = $db->prepare("
                    UPDATE slider 
                    SET qrCode = :qr, shortCode = :sc, externalShortUrl = :ext 
                    WHERE id = :id
                ");
                $upd->execute([
                    ':qr' => $qrData['qrSvg'],
                    ':sc' => $qrData['shortCode'],
                    ':ext' => $qrData['externalShortUrl'],
                    ':id' => $sl->id
                ]);
                $updatedCount++;
            }
        }

        Logger::channel('qr')->info("Tüm QR kodlar yeniden üretildi", ['total' => $updatedCount]);
        return $updatedCount;
    }

    /**
     * QR kod okutulduğunda istatistik kaydeder ve asıl hedef URL'yi döndürür
     */
    public function handleRedirect(string $code, string $ip, string $userAgent, string $referer): ?string
    {
        $shortLink = $this->shortLinkRepository->findByCode($code);
        if (!$shortLink) {
            Logger::channel('qr')->warning("Geçersiz veya bulunamayan QR kısa link okutuldu: {$code}", [
                'code' => $code,
                'ip' => $ip
            ]);
            return null;
        }

        $this->shortLinkRepository->recordScan((int)$shortLink->id, $ip, $userAgent, $referer);
        Logger::channel('qr')->info("QR kod başarıyla okutuldu ve yönlendirildi: {$code}", [
            'code' => $code,
            'targetUrl' => $shortLink->targetUrl,
            'ip' => $ip
        ]);
        return $shortLink->targetUrl;
    }

    public function getAnalytics(int $shortLinkId): array
    {
        return $this->shortLinkRepository->getAnalytics($shortLinkId);
    }
}
