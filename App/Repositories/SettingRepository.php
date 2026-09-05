<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class SettingRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Tüm sistem ayarlarını anahtar-değer sözlüğü olarak döndürür
     *
     * @return array<string, string>
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT key, value FROM setting");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'] ?? '';
        }

        return $settings;
    }

    /**
     * Belirtilen ayarın değerini döndürür
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare("SELECT value FROM setting WHERE key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $val = $stmt->fetchColumn();

        return ($val !== false && $val !== null) ? (string)$val : $default;
    }

    /**
     * Tek bir ayarı günceller veya ekler
     */
    public function set(string $key, ?string $value): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            INSERT INTO setting (key, value, updated_at)
            VALUES (:key, :value, :updated_at)
            ON CONFLICT(key) DO UPDATE SET
                value = :value,
                updated_at = :updated_at
        ");
        $stmt->execute([
            ':key' => $key,
            ':value' => $value ?? '',
            ':updated_at' => $now
        ]);
    }

    /**
     * Birden fazla ayarı tek bir işlemde günceller
     *
     * @param array<string, string|int|bool|null> $settings
     */
    public function setMultiple(array $settings): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO setting (key, value, updated_at)
                VALUES (:key, :value, :updated_at)
                ON CONFLICT(key) DO UPDATE SET
                    value = :value,
                    updated_at = :updated_at
            ");

            foreach ($settings as $key => $value) {
                $stringVal = is_bool($value) ? ($value ? '1' : '0') : (string)$value;
                $stmt->execute([
                    ':key' => (string)$key,
                    ':value' => $stringVal,
                    ':updated_at' => $now
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
