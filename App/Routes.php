<?php
declare(strict_types=1);

namespace App;

use App\Controllers\AdminController;
use App\Controllers\AjaxController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\KioskController;
use App\Controllers\RedirectController;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;

class Routes
{
    /**
     * Uygulamanın tüm web, admin ve API rotalarını kaydeder
     */
    public static function register(Router $router): void
    {
        // 1. Kiosk Ekranı (Dijital Pano Vitrini)
        $router->get('/', [KioskController::class, 'index']);

        // 2. Yönetim Paneli Rotaları (Clean Rewrite)
        $router->get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class]);
        $router->get('/admin/login', [AuthController::class, 'showLogin']);
        $router->post('/admin/login', [AuthController::class, 'login']);
        $router->post('/admin/logout', [AuthController::class, 'logout']);
        $router->get('/admin/logout', [AuthController::class, 'logout']);
        $router->get('/admin/logs/download', [AdminController::class, 'downloadLog'], [AuthMiddleware::class]);

        // 3. AJAX & API Uç Noktaları
        $router->any('/admin/ajax', [AjaxController::class, 'handle']);
        $router->any('/api/ajax', [AjaxController::class, 'handle']);
        $router->any('/ajax.php', [AjaxController::class, 'handle']); // Geriye dönük uyumluluk
        $router->get('/api/kiosk-data', [ApiController::class, 'getKioskData']);
        $router->get('/api/announcements', [ApiController::class, 'getAnnouncementJSON']);
        $router->get('/api/weather', [ApiController::class, 'getWeatherData']);

        // 4. Kısa Link & QR Kod Yönlendirmesi
        $router->get('/r/{code}', [RedirectController::class, 'handle']);
        $router->get('/r.php', [RedirectController::class, 'handleQuery']);
    }
}
