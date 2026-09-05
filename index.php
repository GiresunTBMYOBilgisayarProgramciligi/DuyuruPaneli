<?php
declare(strict_types=1);

namespace App;

date_default_timezone_set('Europe/Istanbul');
setlocale(LC_ALL, 'tr_TR.UTF-8');

require_once __DIR__ . '/vendor/autoload.php';

// .env dosyası mevcutsa çevresel değişkenleri güvenli şekilde yükle
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

// Global Hata ve İstisna Yakalayıcılarını Kaydet (Monolog)
Logger::registerHandlers();

Session::start();

$router = new Router();
Routes::register($router);

$request = new Request();

try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    Logger::exception($e, 'Uygulama çalışırken beklenmeyen bir hata oluştu');
    if ($request->isAjax()) {
        Response::error('Bir sunucu hatası meydana geldi.', 500);
    } else {
        http_response_code(500);
        echo "<!DOCTYPE html><html lang='tr'><head><meta charset='utf-8'><title>500 - Sunucu Hatası</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>500 - Sunucu Hatası</h1><p>İşlem gerçekleştirilirken beklenmeyen bir hata oluştu. Olay sistem günlüklerine kaydedildi.</p><a href='/'>Ana Sayfaya Dön</a></body></html>";
    }
}

