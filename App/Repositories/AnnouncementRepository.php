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
        $sql = "
            SELECT 
                a.id, a.title, a.content, a.qrCode, a.createdDate, 
                a.userId, a.link, a.shortCode,
                COALESCE(u.name || ' ' || u.lastName, 'Sistem') AS userFullName,
                COALESCE(sl.scanCount, 0) AS scanCount
            FROM announcement a
            LEFT JOIN user u ON u.id = a.userId
            LEFT JOIN short_link sl ON sl.code = a.shortCode
            ORDER BY a.id DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM announcement WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function create(AnnouncementDTO $dto, string $qrSvg = '', ?string $shortCode = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO announcement (title, content, qrCode, createdDate, userId, link, shortCode)
            VALUES (:title, :content, :qrCode, :createdDate, :userId, :link, :shortCode)
        ");

        $stmt->execute([
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':qrCode' => $qrSvg,
            ':createdDate' => date('Y.m.d H:i:s'),
            ':userId' => $dto->userId,
            ':link' => $dto->link,
            ':shortCode' => $shortCode
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(AnnouncementDTO $dto, ?string $qrSvg = null, ?string $shortCode = null): bool
    {
        if ($dto->id === null) {
            return false;
        }

        $fields = [
            'title = :title',
            'content = :content',
            'link = :link'
        ];

        $params = [
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':link' => $dto->link,
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

        $sql = "UPDATE announcement SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM announcement WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
