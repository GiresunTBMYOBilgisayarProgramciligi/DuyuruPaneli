<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\DTO\SettingDTO;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Services\ImageOptimizerService;
use App\Services\SettingService;
use App\Validators\SettingValidator;
use Exception;

class SettingController
{
    private SettingService $settingService;
    private ImageOptimizerService $imageOptimizerService;

    public function __construct(?SettingService $settingService = null, ?ImageOptimizerService $imageOptimizerService = null)
    {
        $this->settingService = $settingService ?? new SettingService();
        $this->imageOptimizerService = $imageOptimizerService ?? new ImageOptimizerService($this->settingService);
    }

    /**
     * Tüm sistem ayarlarını JSON olarak döndürür
     */
    public function getSettings(Request $request): void
    {
        AuthMiddleware::handle($request);

        $settings = $this->settingService->getAll();

        // Sistem çalışma durumu bilgisi (System Info)
        $systemInfo = [
            'gd_available' => extension_loaded('gd'),
            'curl_available' => extension_loaded('curl'),
            'webp_supported' => function_exists('imagewebp'),
            'php_version' => PHP_VERSION,
            'tinypng_configured' => !empty($settings['tinypng_api_key'] ?? '')
        ];

        Response::json([
            'settings' => $settings,
            'system' => $systemInfo
        ]);
    }

    /**
     * Sistem ayarlarını günceller
     */
    public function saveSettings(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $data = $request->all();
        $dto = SettingDTO::fromArray($data);

        $error = SettingValidator::validate($dto);
        if ($error !== null) {
            Response::error($error);
            return;
        }

        try {
            $toSave = $dto->toArray();
            $this->settingService->saveSettings($toSave);

            Logger::audit("Sistem ayarları güncellendi", [
                'image_driver' => $dto->imageDriver,
                'resize_dim' => $dto->imageResizeDimension,
                'has_tinypng_key' => !empty($dto->tinyPngApiKey)
            ]);

            Response::success('Sistem ayarları başarıyla kaydedildi.');
        } catch (Exception $e) {
            Logger::exception($e, "Ayarlar kaydedilirken hata oluştu");
            Response::error('Ayarlar kaydedilirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * TinyPNG API bağlantısını ve aylık kotayı test eder
     */
    public function testTinyPng(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $apiKey = (string)$request->input('api_key', '');
        $result = $this->imageOptimizerService->testApiKey(!empty($apiKey) ? $apiKey : null);

        if ($result['valid']) {
            if (!empty($apiKey)) {
                // Başarıyla doğrulanan API anahtarını anında hem veritabanına hem .env dosyasına kaydet
                $this->settingService->saveSettings(['tinypng_api_key' => $apiKey]);
                Logger::audit("TinyPNG API anahtarı başarıyla test edildi ve .env dosyasına otomatik kaydedildi", [
                    'count' => $result['count']
                ]);
            }

            Response::success($result['message'] . ' (API anahtarı .env dosyasına ve sisteme otomatik olarak kaydedildi.)', [
                'compression_count' => $result['count']
            ]);
        } else {
            Response::error($result['message']);
        }
    }
}
