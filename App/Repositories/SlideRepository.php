<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\DTO\SlideDTO;
use PDO;

class SlideRepository
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
                s.id, s.title, s.content, s.image, s.qrCode, s.createdDate, 
                s.userId, s.fullWidth, s.link, s.shortCode,
                s.orderNumber, s.isActive, s.startsAt, s.expiresAt,
                COALESCE(u.name || ' ' || u.lastName, 'Sistem') AS userFullName,
                COALESCE(sl.scanCount, 0) AS scanCount
            FROM slider s
            LEFT JOIN user u ON u.id = s.userId
            LEFT JOIN short_link sl ON sl.code = s.shortCode
            ORDER BY CASE WHEN s.orderNumber > 0 THEN s.orderNumber ELSE 999999 END ASC, s.id DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getActiveSlides(): array
    {
        $this->deactivateExpired();

        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT 
                s.id, s.title, s.content, s.image, s.qrCode, s.createdDate, 
                s.userId, s.fullWidth, s.link, s.shortCode,
                s.orderNumber, s.isActive, s.startsAt, s.expiresAt,
                COALESCE(u.name || ' ' || u.lastName, 'Sistem') AS userFullName,
                COALESCE(sl.scanCount, 0) AS scanCount
            FROM slider s
            LEFT JOIN user u ON u.id = s.userId
            LEFT JOIN short_link sl ON sl.code = s.shortCode
            WHERE s.isActive = 1 
              AND (s.startsAt IS NULL OR s.startsAt = '' OR s.startsAt <= :now)
              AND (s.expiresAt IS NULL OR s.expiresAt = '' OR s.expiresAt > :now)
            ORDER BY CASE WHEN s.orderNumber > 0 THEN s.orderNumber ELSE 999999 END ASC, s.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':now' => $now]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM slider WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $slide = $stmt->fetch();
        return $slide ?: null;
    }

    public function create(SlideDTO $dto, string $qrSvg = '', ?string $shortCode = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO slider (title, content, image, qrCode, createdDate, userId, fullWidth, link, shortCode, orderNumber, isActive, startsAt, expiresAt)
            VALUES (:title, :content, :image, :qrCode, :createdDate, :userId, :fullWidth, :link, :shortCode, :orderNumber, :isActive, :startsAt, :expiresAt)
        ");

        $stmt->execute([
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':image' => $dto->image,
            ':qrCode' => $qrSvg,
            ':createdDate' => date('Y.m.d H:i:s'),
            ':userId' => $dto->userId,
            ':fullWidth' => $dto->fullWidth,
            ':link' => $dto->link,
            ':shortCode' => $shortCode,
            ':orderNumber' => $dto->orderNumber,
            ':isActive' => $dto->isActive,
            ':startsAt' => $dto->startsAt,
            ':expiresAt' => $dto->expiresAt
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(SlideDTO $dto, ?string $qrSvg = null, ?string $shortCode = null): bool
    {
        if ($dto->id === null) {
            return false;
        }

        $fields = [
            'title = :title',
            'content = :content',
            'fullWidth = :fullWidth',
            'link = :link',
            'orderNumber = :orderNumber',
            'isActive = :isActive',
            'startsAt = :startsAt',
            'expiresAt = :expiresAt'
        ];

        $params = [
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':fullWidth' => $dto->fullWidth,
            ':link' => $dto->link,
            ':orderNumber' => $dto->orderNumber,
            ':isActive' => $dto->isActive,
            ':startsAt' => $dto->startsAt,
            ':expiresAt' => $dto->expiresAt,
            ':id' => $dto->id
        ];

        if ($dto->image !== null) {
            $fields[] = 'image = :image';
            $params[':image'] = $dto->image;
        }

        if ($qrSvg !== null) {
            $fields[] = 'qrCode = :qrCode';
            $params[':qrCode'] = $qrSvg;
        }

        if ($shortCode !== null) {
            $fields[] = 'shortCode = :shortCode';
            $params[':shortCode'] = $shortCode;
        }

        $sql = "UPDATE slider SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateOrder(int $id, int $orderNumber): bool
    {
        $stmt = $this->db->prepare("UPDATE slider SET orderNumber = :orderNumber WHERE id = :id");
        return $stmt->execute([':orderNumber' => $orderNumber, ':id' => $id]);
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE slider SET isActive = CASE WHEN isActive = 1 THEN 0 ELSE 1 END WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function deactivateExpired(): int
    {
        $now = date('Y-m-d H:i:s');
        // Süresi dolan afişleri kalıcı silmek yerine durumunu pasif (0 - Duraklatıldı) yap
        $stmt = $this->db->prepare("UPDATE slider SET isActive = 0 WHERE expiresAt IS NOT NULL AND expiresAt != '' AND expiresAt <= :now AND isActive = 1");
        $stmt->execute([':now' => $now]);
        return $stmt->rowCount();
    }

    public function purgeExpired(): int
    {
        return $this->deactivateExpired();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slider WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
