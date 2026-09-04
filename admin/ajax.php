<?php
declare(strict_types=1);

namespace App\Admin;

require_once __DIR__ . "/../vendor/autoload.php";

use App\Controllers\AnnouncementController;
use App\Controllers\AuthController;
use App\Controllers\SlideController;
use App\Controllers\UserController;
use App\Controllers\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;

Session::start();
$request = new Request();

$action = (string)$request->input('functionName', $request->input('action', ''));

if (empty($action)) {
    Response::error('Geçersiz istek. Eylem belirtilmedi.', 400);
}

// 1. Kimlik Doğrulama Eylemleri (Oturumsuz izin verilenler)
if ($action === 'login') {
    $authController = new AuthController();
    $authController->login($request);
    exit;
}

if ($action === 'logout') {
    $authController = new AuthController();
    $authController->logout($request);
    exit;
}

// 2. Kiosk Ekranı Herkese Açık API Eylemleri (TV Ekranı için)
if ($action === 'getAnnouncementJSON') {
    $apiController = new ApiController();
    $apiController->getAnnouncementJSON($request);
    exit;
}

if ($action === 'getKioskData') {
    $apiController = new ApiController();
    $apiController->getKioskData($request);
    exit;
}

if ($action === 'getWeatherData') {
    $apiController = new ApiController();
    $apiController->getWeatherData($request);
    exit;
}

// 3. Yönetim Paneli Eylemleri - Zorunlu Oturum Kontrolü
AuthMiddleware::handle($request);

// 4. Güvenli Eylem Eşleştirme (Whitelist Dispatcher)
switch ($action) {
    // Slayt İşlemleri
    case 'getSlidesList':
        (new SlideController())->list($request);
        break;

    case 'saveSlide':
        (new SlideController())->create($request);
        break;

    case 'updateSlide':
        (new SlideController())->update($request);
        break;

    case 'deleteSlide':
        (new SlideController())->delete($request);
        break;

    // Duyuru İşlemleri
    case 'getAnnouncementsList':
        (new AnnouncementController())->list($request);
        break;

    case 'saveAnnouncement':
        (new AnnouncementController())->create($request);
        break;

    case 'updateAnnouncement':
        (new AnnouncementController())->update($request);
        break;

    case 'deleteAnnouncement':
        (new AnnouncementController())->delete($request);
        break;

    // Kullanıcı İşlemleri
    case 'getUsersList':
        (new UserController())->list($request);
        break;

    case 'saveUser':
        (new UserController())->create($request);
        break;

    case 'updateUser':
        (new UserController())->update($request);
        break;

    case 'deleteUser':
        (new UserController())->delete($request);
        break;

    default:
        Response::error("Tanımsız eylem: {$action}", 400);
        break;
}