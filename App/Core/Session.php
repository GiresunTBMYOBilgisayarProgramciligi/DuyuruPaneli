<?php
declare(strict_types=1);

namespace App\Core;

use App\Config;

class Session
{
    /**
     * Güvenli PHP oturumunu başlatır
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

                session_set_cookie_params([
                    'lifetime' => 86400 * 30, // 30 gün
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                session_name(Config::LOGIN_COOKIE_NAME);
            }
            @session_start();
        }
    }

    /**
     * Oturuma kullanıcı kimliğini kaydeder
     */
    public static function login(int $userId): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION[Config::SESSION_AUTH_KEY] = $userId;
    }

    /**
     * Oturumu kapatır
     */
    public static function logout(): void
    {
        self::start();
        unset($_SESSION[Config::SESSION_AUTH_KEY]);
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Giriş yapmış kullanıcının ID'sini döndürür (veya null)
     */
    public static function getUserId(): ?int
    {
        self::start();
        if (isset($_SESSION[Config::SESSION_AUTH_KEY])) {
            return (int)$_SESSION[Config::SESSION_AUTH_KEY];
        }
        return null;
    }

    /**
     * Kullanıcı giriş yapmış mı kontrol eder
     */
    public static function isLoggedIn(): bool
    {
        return self::getUserId() !== null;
    }

    /**
     * CSRF Token oluşturur veya mevcut olanı döndürür
     */
    public static function getCsrfToken(): string
    {
        self::start();
        if (empty($_SESSION[Config::SESSION_CSRF_KEY])) {
            $_SESSION[Config::SESSION_CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[Config::SESSION_CSRF_KEY];
    }

    /**
     * Gönderilen CSRF token'ını doğrular
     */
    public static function validateCsrfToken(?string $token): bool
    {
        self::start();
        if (empty($token) || empty($_SESSION[Config::SESSION_CSRF_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[Config::SESSION_CSRF_KEY], $token);
    }

    /**
     * Oturumdan belirtilen anahtarı alır
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Oturuma anahtar-değer çifti kaydeder
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }
}
