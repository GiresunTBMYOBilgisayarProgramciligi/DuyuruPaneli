<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\DTO\AnnouncementDTO;
use PDO;

class AnnouncementRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $this->deactivateExpired();

        $sql = "
            SELECT 
                a.id, a.title, a.content, a.qrCode, a.createdDate, 
                a.userId, a.link, a.shortCode,
                COALESCE(a.externalShortUrl, sl.externalShortUrl) AS externalShortUrl,
                a.orderNumber, a.isActive, a.startsAt, a.expiresAt,
                COALESCE(u.name || ' ' || u.lastName, 'Sistem') AS userFullName,
                COALESCE(sl.scanCount, 0) AS scanCount
            FROM announcement a
            LEFT JOIN user u ON u.id = a.userId
            LEFT JOIN short_link sl ON sl.code = a.shortCode
            ORDER BY CASE WHEN a.orderNumber > 0 THEN a.orderNumber ELSE 999999 END ASC, a.id DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getActiveAnnouncements(): array
    {
        $this->deactivateExpired();

        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT 
                a.id, a.title, a.content, a.qrCode, a.createdDate, 
                a.userId, a.link, a.shortCode,
                COALESCE(a.externalShortUrl, sl.externalShortUrl) AS externalShortUrl,
                a.orderNumber, a.isActive, a.startsAt, a.expiresAt,
                COALESCE(u.name || ' ' || u.lastName, 'Sistem') AS userFullName,
                COALESCE(sl.scanCount, 0) AS scanCount
            FROM announcement a
            LEFT JOIN user u ON u.id = a.userId
            LEFT JOIN short_link sl ON sl.code = a.shortCode
            WHERE a.isActive = 1 
              AND (a.startsAt IS NULL OR a.startsAt = '' OR REPLACE(a.startsAt, 'T', ' ') <= :now)
              AND (a.expiresAt IS NULL OR a.expiresAt = '' OR REPLACE(a.expiresAt, 'T', ' ') > :now)
            ORDER BY CASE WHEN a.orderNumber > 0 THEN a.orderNumber ELSE 999999 END ASC, a.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':now' => $now]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM announcement WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function create(AnnouncementDTO $dto, string $qrSvg = '', ?string $shortCode = null, ?string $externalShortUrl = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO announcement (title, content, qrCode, createdDate, userId, link, shortCode, externalShortUrl, orderNumber, isActive, startsAt, expiresAt)
            VALUES (:title, :content, :qrCode, :createdDate, :userId, :link, :shortCode, :externalShortUrl, :orderNumber, :isActive, :startsAt, :expiresAt)
        ");

        $stmt->execute([
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':qrCode' => $qrSvg,
            ':createdDate' => date('Y.m.d H:i:s'),
            ':userId' => $dto->userId,
            ':link' => $dto->link,
            ':shortCode' => $shortCode,
            ':externalShortUrl' => $externalShortUrl,
            ':orderNumber' => $dto->orderNumber,
            ':isActive' => $dto->isActive,
            ':startsAt' => $dto->startsAt,
            ':expiresAt' => $dto->expiresAt
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(AnnouncementDTO $dto, ?string $qrSvg = null, ?string $shortCode = null, ?string $externalShortUrl = null): bool
    {
        if ($dto->id === null) {
            return false;
        }

        $fields = [
            'title = :title',
            'content = :content',
            'link = :link',
            'orderNumber = :orderNumber',
            'isActive = :isActive',
            'startsAt = :startsAt',
            'expiresAt = :expiresAt'
        ];

        $params = [
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':link' => $dto->link,
            ':orderNumber' => $dto->orderNumber,
            ':isActive' => $dto->isActive,
            ':startsAt' => $dto->startsAt,
            ':expiresAt' => $dto->expiresAt,
            ':id' => $dto->id
        ];

        if ($qrSvg !== null) {
            $fields[] = 'qrCode = :qrCode';
            $params[':qrCode'] = $qrSvg;
        }

        if ($shortCode !== null) {
            $fields[] = 'shortCode = :shortCode';
            $params[':shortCode'] = $shortCode;
        }

        if ($externalShortUrl !== null) {
            $fields[] = 'externalShortUrl = :externalShortUrl';
            $params[':externalShortUrl'] = $externalShortUrl;
        }

        $sql = "UPDATE announcement SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateOrder(int $id, int $orderNumber): bool
    {
        $stmt = $this->db->prepare("UPDATE announcement SET orderNumber = :orderNumber WHERE id = :id");
        return $stmt->execute([':orderNumber' => $orderNumber, ':id' => $id]);
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE announcement SET isActive = CASE WHEN isActive = 1 THEN 0 ELSE 1 END WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function deactivateExpired(): int
    {
        $now = date('Y-m-d H:i:s');
        // Süresi dolan duyuruları silmek yerine durumunu pasif (0 - Duraklatıldı) yap
        $stmt = $this->db->prepare("UPDATE announcement SET isActive = 0 WHERE expiresAt IS NOT NULL AND expiresAt != '' AND REPLACE(expiresAt, 'T', ' ') <= :now AND isActive = 1");
        $stmt->execute([':now' => $now]);
        return $stmt->rowCount();
    }

    public function purgeExpired(): int
    {
        return $this->deactivateExpired();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM announcement WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
