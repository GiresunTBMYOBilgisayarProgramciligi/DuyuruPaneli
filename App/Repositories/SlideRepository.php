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
        $sql = "
            SELECT 
                s.id, s.title, s.content, s.image, s.qrCode, s.createdDate, 
                s.userId, s.fullWidth, s.link, s.shortCode,
                COALESCE(u.name || ' ' || u.lastName, 'Sistem') AS userFullName,
                COALESCE(sl.scanCount, 0) AS scanCount
            FROM slider s
            LEFT JOIN user u ON u.id = s.userId
            LEFT JOIN short_link sl ON sl.code = s.shortCode
            ORDER BY s.id DESC
        ";
        return $this->db->query($sql)->fetchAll();
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
            INSERT INTO slider (title, content, image, qrCode, createdDate, userId, fullWidth, link, shortCode)
            VALUES (:title, :content, :image, :qrCode, :createdDate, :userId, :fullWidth, :link, :shortCode)
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
            ':shortCode' => $shortCode
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
            'link = :link'
        ];

        $params = [
            ':title' => $dto->title,
            ':content' => $dto->content,
            ':fullWidth' => $dto->fullWidth,
            ':link' => $dto->link,
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

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slider WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
