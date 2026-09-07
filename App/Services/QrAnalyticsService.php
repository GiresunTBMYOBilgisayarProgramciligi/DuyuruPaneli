<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ShortLinkRepository;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrAnalyticsService
{
    private ShortLinkRepository $shortLinkRepository;

    public function __construct()
    {
        $this->shortLinkRepository = new ShortLinkRepository();
    }

    /**
     * Hedef URL için kısa link üretir, analitik kaydını hazırlar ve yerel SVG QR kod döndürür
     */
    public function generateForUrl(string $targetUrl, string $title = ''): array
    {
        if (empty(trim($targetUrl))) {
            return [
                'shortCode' => null,
                'qrSvg' => ''
            ];
        }

        // Kısa link nesnesi al veya oluştur
        $shortLink = $this->shortLinkRepository->getOrCreate($targetUrl, $title);

        // Dinamik host adresine göre yönlendirme URL'si oluştur
        $host = $_SERVER['HTTP_HOST'] ?? 'unipano.loc';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $redirectUrl = "{$scheme}://{$host}/r/{$shortLink->code}";

        // BaconQrCode ile yerel SVG QR kod üret
        // Düşük çözünürlüklü TV ekranlarında modül boyutunu maksimize etmek için:
        // - size: 120 (yüksek çözünürlüklü SVG viewBox koordinatları)
        // - margin: 1 (iç boşluğu minimize ederek matris alanını genişletir)
        $renderer = new ImageRenderer(
            new RendererStyle(120, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $rawSvg = $writer->writeString($redirectUrl);

        // XML declaration'ı temizle, doğrudan DOM'a gömülebilir SVG bırak
        $cleanSvg = substr($rawSvg, strpos($rawSvg, "\n") + 1);

        // Düşük çözünürlüklü ekranlarda anti-aliasing bulanıklığını önlemek için jilet gibi keskin kenar oluştur
        if (strpos($cleanSvg, '<svg') !== false && strpos($cleanSvg, 'shape-rendering') === false) {
            $cleanSvg = preg_replace('/<svg /', '<svg shape-rendering="crispEdges" ', $cleanSvg, 1);
        }

        return [
            'shortCode' => $shortLink->code,
            'qrSvg' => $cleanSvg,
            'redirectUrl' => $redirectUrl
        ];
    }

    /**
     * Veritabanındaki tüm aktif duyuru ve slaytların QR kodlarını yeni netlik ve sessiz alan standartlarıyla günceller
     */
    public function regenerateExistingQrCodes(): void
    {
        $db = \App\Core\Database::getConnection();

        // Duyurular
        $stmt = $db->query("SELECT id, link, title FROM announcement WHERE link IS NOT NULL AND trim(link) != ''");
        $announcements = $stmt->fetchAll();
        foreach ($announcements as $ann) {
            $qrData = $this->generateForUrl((string)$ann->link, (string)($ann->title ?? ''));
            if (!empty($qrData['qrSvg'])) {
                $upd = $db->prepare("UPDATE announcement SET qrCode = :qr, shortCode = :sc WHERE id = :id");
                $upd->execute([
                    ':qr' => $qrData['qrSvg'],
                    ':sc' => $qrData['shortCode'],
                    ':id' => $ann->id
                ]);
            }
        }

        // Slaytlar
        $stmt = $db->query("SELECT id, link, title FROM slider WHERE link IS NOT NULL AND trim(link) != ''");
        $slides = $stmt->fetchAll();
        foreach ($slides as $sl) {
            $qrData = $this->generateForUrl((string)$sl->link, (string)($sl->title ?? ''));
            if (!empty($qrData['qrSvg'])) {
                $upd = $db->prepare("UPDATE slider SET qrCode = :qr, shortCode = :sc WHERE id = :id");
                $upd->execute([
                    ':qr' => $qrData['qrSvg'],
                    ':sc' => $qrData['shortCode'],
                    ':id' => $sl->id
                ]);
            }
        }
    }

    /**
     * QR kod okutulduğunda istatistik kaydeder ve asıl hedef URL'yi döndürür
     */
    public function handleRedirect(string $code, string $ip, string $userAgent, string $referer): ?string
    {
        $shortLink = $this->shortLinkRepository->findByCode($code);
        if (!$shortLink) {
            \App\Core\Logger::channel('qr')->warning("Geçersiz veya bulunamayan QR kısa link okutuldu: {$code}", [
                'code' => $code,
                'ip' => $ip
            ]);
            return null;
        }

        $this->shortLinkRepository->recordScan((int)$shortLink->id, $ip, $userAgent, $referer);
        \App\Core\Logger::channel('qr')->info("QR kod başarıyla okutuldu ve yönlendirildi: {$code}", [
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
