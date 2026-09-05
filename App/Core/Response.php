<?php
declare(strict_types=1);

namespace App\Core;

class Response
{
    /**
     * JSON yanıtı gönderir ve scripti sonlandırır
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Başarılı JSON yanıtı
     */
    public static function success(string $message = 'İşlem başarılı', array $extra = []): void
    {
        self::json(array_merge(['success' => true, 'message' => $message], $extra), 200);
    }

    /**
     * Hatalı JSON yanıtı
     */
    public static function error(string $message = 'Bir hata oluştu', int $statusCode = 400, array $extra = []): void
    {
        self::json(array_merge(['success' => false, 'error' => $message], $extra), $statusCode);
    }

    /**
     * HTTP yönlendirmesi yapar ve scripti sonlandırır
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: " . $url);
        exit;
    }
}
