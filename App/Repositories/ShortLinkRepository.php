<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ShortLinkRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Hedef URL için kısa link oluşturur veya var olanı getirir
     */
    public function getOrCreate(string $targetUrl, string $title = ''): object
    {
        $stmt = $this->db->prepare("SELECT * FROM short_link WHERE targetUrl = :targetUrl LIMIT 1");
        $stmt->execute([':targetUrl' => $targetUrl]);
        $existing = $stmt->fetch();

        if ($existing) {
            return $existing;
        }

        // 6 haneli benzersiz kod üret
        $code = $this->generateUniqueCode();

        $insert = $this->db->prepare("
            INSERT INTO short_link (code, targetUrl, title, scanCount, createdDate)
            VALUES (:code, :targetUrl, :title, 0, :createdDate)
        ");

        $insert->execute([
            ':code' => $code,
            ':targetUrl' => $targetUrl,
            ':title' => $title,
            ':createdDate' => date('Y-m-d H:i:s')
        ]);

        return (object)[
            'id' => (int)$this->db->lastInsertId(),
            'code' => $code,
            'targetUrl' => $targetUrl,
            'title' => $title,
            'scanCount' => 0,
            'createdDate' => date('Y-m-d H:i:s')
        ];
    }

    public function findByCode(string $code): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM short_link WHERE code = :code LIMIT 1");
        $stmt->execute([':code' => $code]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Taramayı kaydeder ve sayaç artırır
     */
    public function recordScan(int $shortLinkId, string $ip, string $userAgent, string $referer): void
    {
        // IP anonimleştirme (KVKK uyumlu SHA-256)
        $ipHash = hash('sha256', $ip . 'unipano_salt_2026');

        // Sayacı artır
        $update = $this->db->prepare("UPDATE short_link SET scanCount = scanCount + 1 WHERE id = :id");
        $update->execute([':id' => $shortLinkId]);

        // Analitik logu ekle
        $log = $this->db->prepare("
            INSERT INTO qr_analytics (shortLinkId, scannedAt, ipHash, userAgent, referer)
            VALUES (:shortLinkId, :scannedAt, :ipHash, :userAgent, :referer)
        ");

        $log->execute([
            ':shortLinkId' => $shortLinkId,
            ':scannedAt' => date('Y-m-d H:i:s'),
            ':ipHash' => $ipHash,
            ':userAgent' => mb_substr($userAgent, 0, 255),
            ':referer' => mb_substr($referer, 0, 255)
        ]);
    }

    /**
     * Bir kısa linkin detaylı istatistiklerini getirir
     */
    public function getAnalytics(int $shortLinkId): array
    {
        $stmt = $this->db->prepare("
            SELECT scannedAt, userAgent, referer
            FROM qr_analytics
            WHERE shortLinkId = :id
            ORDER BY id DESC
            LIMIT 50
        ");
        $stmt->execute([':id' => $shortLinkId]);
        return $stmt->fetchAll();
    }

    private function generateUniqueCode(int $length = 6): string
    {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $maxIndex = strlen($characters) - 1;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, $maxIndex)];
            }
            $stmt = $this->db->prepare("SELECT id FROM short_link WHERE code = :code");
            $stmt->execute([':code' => $code]);
        } while ($stmt->fetch());

        return $code;
    }
}
