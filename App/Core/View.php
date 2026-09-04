<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    private const VIEWS_BASE_PATH = __DIR__ . '/../../views/';

    /**
     * Verilen görünüm (view) dosyasını değişkenlerle birlikte render eder.
     * Nokta notasyonunu destekler: 'admin.dashboard' -> 'views/admin/dashboard.php'
     *
     * @param string $viewPath Nokta notasyonu ile view adı
     * @param array $data Görünüme aktarılacak değişkenler
     * @throws RuntimeException Görünüm bulunamazsa
     */
    public static function render(string $viewPath, array $data = []): void
    {
        $relativePath = str_replace('.', '/', $viewPath) . '.php';
        $fullPath = self::VIEWS_BASE_PATH . $relativePath;

        if (!file_exists($fullPath)) {
            throw new RuntimeException("Görünüm dosyası bulunamadı: [{$viewPath}] ({$fullPath})");
        }

        // Değişkenleri görünüm kapsamına aktar
        extract($data, EXTR_SKIP);

        require $fullPath;
    }

    /**
     * Bir görünümün var olup olmadığını denetler
     */
    public static function exists(string $viewPath): bool
    {
        $relativePath = str_replace('.', '/', $viewPath) . '.php';
        return file_exists(self::VIEWS_BASE_PATH . $relativePath);
    }
}
