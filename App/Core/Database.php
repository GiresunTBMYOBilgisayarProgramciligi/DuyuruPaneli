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
            if (date_default_timezone_get() !== Config::TIMEZONE) {
                date_default_timezone_set(Config::TIMEZONE);
            }

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
                Logger::channel('database')->critical("Veritabanı bağlantı hatası: " . $e->getMessage(), [
                    'exception_code' => $e->getCode(),
                    'file' => $e->getFile() . ':' . $e->getLine()
                ]);
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
                fullWidth INTEGER DEFAULT 0,
                link TEXT,
                shortCode TEXT,
                orderNumber INTEGER DEFAULT 0,
                isActive INTEGER DEFAULT 1,
                startsAt TEXT DEFAULT NULL,
                expiresAt TEXT DEFAULT NULL,
                showCaption INTEGER DEFAULT 1,
                qrPosition TEXT DEFAULT 'bottom-right',
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
                orderNumber INTEGER DEFAULT 0,
                isActive INTEGER DEFAULT 1,
                startsAt TEXT DEFAULT NULL,
                expiresAt TEXT DEFAULT NULL,
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

        // Mevcut veritabanları için güvenli şema güncellemesi (Otomatik migrasyon)
        self::migrateColumns($pdo);

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

    /**
     * Eksik sütunları güvenli şekilde tabloya ekler
     */
    private static function migrateColumns(PDO $pdo): void
    {
        // 1. slider tablosu sütunları
        $sliderCols = array_column($pdo->query("PRAGMA table_info(slider)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('orderNumber', $sliderCols, true)) {
            $pdo->exec("ALTER TABLE slider ADD COLUMN orderNumber INTEGER DEFAULT 0;");
        }
        if (!in_array('isActive', $sliderCols, true)) {
            $pdo->exec("ALTER TABLE slider ADD COLUMN isActive INTEGER DEFAULT 1;");
        }
        if (!in_array('startsAt', $sliderCols, true)) {
            $pdo->exec("ALTER TABLE slider ADD COLUMN startsAt TEXT DEFAULT NULL;");
        }
        if (!in_array('expiresAt', $sliderCols, true)) {
            $pdo->exec("ALTER TABLE slider ADD COLUMN expiresAt TEXT DEFAULT NULL;");
        }
        if (!in_array('showCaption', $sliderCols, true)) {
            $pdo->exec("ALTER TABLE slider ADD COLUMN showCaption INTEGER DEFAULT 1;");
        }
        if (!in_array('qrPosition', $sliderCols, true)) {
            $pdo->exec("ALTER TABLE slider ADD COLUMN qrPosition TEXT DEFAULT 'bottom-right';");
        }

        // 2. announcement tablosu sütunları
        $annCols = array_column($pdo->query("PRAGMA table_info(announcement)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('orderNumber', $annCols, true)) {
            $pdo->exec("ALTER TABLE announcement ADD COLUMN orderNumber INTEGER DEFAULT 0;");
        }
        if (!in_array('isActive', $annCols, true)) {
            $pdo->exec("ALTER TABLE announcement ADD COLUMN isActive INTEGER DEFAULT 1;");
        }
        if (!in_array('startsAt', $annCols, true)) {
            $pdo->exec("ALTER TABLE announcement ADD COLUMN startsAt TEXT DEFAULT NULL;");
        }
        if (!in_array('expiresAt', $annCols, true)) {
            $pdo->exec("ALTER TABLE announcement ADD COLUMN expiresAt TEXT DEFAULT NULL;");
        }

        // 3. Veritabanındaki 'T' ayraçlı tarihleri SQLite standart 'YYYY-MM-DD HH:MM:SS' formatına normalize et
        $pdo->exec("UPDATE slider SET startsAt = REPLACE(startsAt, 'T', ' ') WHERE startsAt LIKE '%T%'");
        $pdo->exec("UPDATE slider SET expiresAt = REPLACE(expiresAt, 'T', ' ') WHERE expiresAt LIKE '%T%'");
        $pdo->exec("UPDATE announcement SET startsAt = REPLACE(startsAt, 'T', ' ') WHERE startsAt LIKE '%T%'");
        $pdo->exec("UPDATE announcement SET expiresAt = REPLACE(expiresAt, 'T', ' ') WHERE expiresAt LIKE '%T%'");
    }
}


