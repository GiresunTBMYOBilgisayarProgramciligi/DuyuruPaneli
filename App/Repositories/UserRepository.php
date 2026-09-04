<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\DTO\UserDTO;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT id, userName, mail, name, lastName, createdDate FROM user WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE userName = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Tüm kullanıcıları döndürür (Şifre hash'leri filtrelenir)
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, userName, mail, name, lastName, createdDate FROM user ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function create(UserDTO $dto): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO user (userName, mail, password, name, lastName, createdDate)
            VALUES (:userName, :mail, :password, :name, :lastName, :createdDate)
        ");

        $stmt->execute([
            ':userName' => $dto->userName,
            ':mail' => $dto->mail,
            ':password' => password_hash((string)$dto->password, PASSWORD_DEFAULT),
            ':name' => $dto->name,
            ':lastName' => $dto->lastName,
            ':createdDate' => date('Y.m.d H:i:s')
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(UserDTO $dto): bool
    {
        if ($dto->id === null) {
            return false;
        }

        if ($dto->password !== null) {
            $stmt = $this->db->prepare("
                UPDATE user
                SET userName = :userName, mail = :mail, password = :password, name = :name, lastName = :lastName
                WHERE id = :id
            ");
            return $stmt->execute([
                ':userName' => $dto->userName,
                ':mail' => $dto->mail,
                ':password' => password_hash($dto->password, PASSWORD_DEFAULT),
                ':name' => $dto->name,
                ':lastName' => $dto->lastName,
                ':id' => $dto->id
            ]);
        }

        $stmt = $this->db->prepare("
            UPDATE user
            SET userName = :userName, mail = :mail, name = :name, lastName = :lastName
            WHERE id = :id
        ");
        return $stmt->execute([
            ':userName' => $dto->userName,
            ':mail' => $dto->mail,
            ':name' => $dto->name,
            ':lastName' => $dto->lastName,
            ':id' => $dto->id
        ]);
    }

    public function delete(int $id): bool
    {
        // Yönetici (ID 1) silinemez
        if ($id === 1) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM user WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
