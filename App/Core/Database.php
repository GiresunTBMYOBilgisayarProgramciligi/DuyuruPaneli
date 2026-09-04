<?php
declare(strict_types=1);

namespace App\Core;

use App\Config;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Singleton PDO bağlantısı döndürür
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbDir = Config::ROOT_PATH . "db";
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0775, true);
            }

            try {
                self::$instance = new PDO("sqlite:" . Config::PATH_TO_SQLITE_FILE);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                self::$instance->exec("PRAGMA foreign_keys = ON;");
                @chmod(Config::PATH_TO_SQLITE_FILE, 0666);
                self::ensureTables(self::$instance);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw $e;
            }
        }

        return self::$instance;
    }

    /**
     * Veritabanı tablolarının varlığını denetler ve eksikse otomatik oluşturur
     */
    private static function ensureTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user(
                id INTEGER PRIMARY KEY,
                userName TEXT NOT NULL UNIQUE,
                mail TEXT NOT NULL,
                password TEXT,
                name TEXT,
                lastName TEXT,
                createdDate TEXT
            );

            CREATE TABLE IF NOT EXISTS slider(
                id INTEGER PRIMARY KEY,
                title TEXT,
                content TEXT,
                image TEXT,
                qrCode TEXT,
                createdDate TEXT,
                userId INTEGER,
                fullWidth INTEGER,
                link TEXT,
                shortCode TEXT,
                FOREIGN KEY (userId) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE
            );

            CREATE TABLE IF NOT EXISTS announcement(
                id INTEGER PRIMARY KEY,
                title TEXT,
                content TEXT,
                qrCode TEXT,
                createdDate TEXT,
                userId INTEGER,
                link TEXT,
                shortCode TEXT,
                FOREIGN KEY (userId) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE
            );

            CREATE TABLE IF NOT EXISTS short_link(
                id INTEGER PRIMARY KEY,
                code TEXT NOT NULL UNIQUE,
                targetUrl TEXT NOT NULL,
                title TEXT,
                scanCount INTEGER DEFAULT 0,
                createdDate TEXT
            );

            CREATE TABLE IF NOT EXISTS qr_analytics(
                id INTEGER PRIMARY KEY,
                shortLinkId INTEGER NOT NULL,
                scannedAt TEXT NOT NULL,
                ipHash TEXT,
                userAgent TEXT,
                referer TEXT,
                FOREIGN KEY (shortLinkId) REFERENCES short_link (id) ON DELETE CASCADE ON UPDATE CASCADE
            );
        ");

        $userCount = (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
        if ($userCount === 0) {
            $stmt = $pdo->prepare("
                INSERT OR IGNORE INTO user(userName, mail, password, name, lastName, createdDate)
                VALUES('sametatabasch', 'sametatabasch@gmail.com', :password, 'Samet', 'ATABAŞ', :createdDate)
            ");
            $stmt->execute([
                ':password' => password_hash("123456", PASSWORD_DEFAULT),
                ':createdDate' => date('Y.m.d H:i:s')
            ]);
        }
    }
}


