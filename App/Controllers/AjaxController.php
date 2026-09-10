<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;

class AjaxController
{
    /**
     * AJAX eylemlerini güvenli whitelist mantığı ile karşılar ve yönlendirir
     */
    public function handle(Request $request): void
    {
        Session::start();

        $action = (string)$request->input('functionName', $request->input('action', ''));

        if (empty($action)) {
            Response::error('Geçersiz istek. Eylem belirtilmedi.', 400);
        }

        // 1. Herkese Açık Eylemler (Giriş & Kiosk API)
        if ($action === 'login') {
            (new AuthController())->login($request);
            return;
        }

        if ($action === 'logout') {
            (new AuthController())->logout($request);
            return;
        }

        if ($action === 'getAnnouncementJSON') {
            (new ApiController())->getAnnouncementJSON($request);
            return;
        }

        if ($action === 'getKioskData') {
            (new ApiController())->getKioskData($request);
            return;
        }

        if ($action === 'getWeatherData') {
            (new ApiController())->getWeatherData($request);
            return;
        }

        // 2. Yönetim Paneli Eylemleri - Zorunlu Oturum & Yetki Kontrolü
        AuthMiddleware::handle($request);

        // Değişiklik yapan POST eylemlerinde CSRF doğrulaması
        if (in_array($action, [
            'saveSlide', 'updateSlide', 'deleteSlide', 'toggleSlideStatus', 'updateSlideOrder',
            'saveAnnouncement', 'updateAnnouncement', 'deleteAnnouncement', 'toggleAnnouncementStatus', 'updateAnnouncementOrder',
            'saveUser', 'updateUser', 'deleteUser',
            'saveSettings', 'testTinyPng', 'testShortener', 'regenerateQr'
        ], true)) {
            CsrfMiddleware::handle($request);
        }

        // 3. Eylemi İlgili Controller'a Delege Et
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

            case 'toggleSlideStatus':
                (new SlideController())->toggleStatus($request);
                break;

            case 'updateSlideOrder':
                (new SlideController())->updateOrder($request);
                break;

            // Kayan Duyuru İşlemleri
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

            case 'toggleAnnouncementStatus':
                (new AnnouncementController())->toggleStatus($request);
                break;

            case 'updateAnnouncementOrder':
                (new AnnouncementController())->updateOrder($request);
                break;

            // Kullanıcı Hesap İşlemleri
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

            // Sistem Günlükleri (Log) İşlemleri
            case 'getLogs':
                $file = (string)$request->input('file', '');
                $limit = (int)$request->input('limit', 200);
                if ($limit <= 0 || $limit > 500) {
                    $limit = 200;
                }
                $files = Logger::getLogFiles();
                $logs = Logger::readLogLines($file, $limit);
                Response::json([
                    'files' => $files,
                    'logs' => $logs
                ]);
                break;

            // Sistem ve Optimizasyon Ayarları
            case 'getSettings':
                (new SettingController())->getSettings($request);
                break;

            case 'saveSettings':
                (new SettingController())->saveSettings($request);
                break;

            case 'testTinyPng':
                (new SettingController())->testTinyPng($request);
                break;

            case 'testShortener':
                (new SettingController())->testShortener($request);
                break;

            case 'regenerateQr':
                (new SettingController())->regenerateQr($request);
                break;

            default:
                Response::error("Tanımsız veya yetkisiz eylem: {$action}", 400);
                break;
        }
    }
}
