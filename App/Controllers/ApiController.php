<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AnnouncementRepository;
use App\Repositories\SlideRepository;
use App\Services\WeatherService;
use DateTime;

class ApiController
{
    private SlideRepository $slideRepository;
    private AnnouncementRepository $announcementRepository;
    private WeatherService $weatherService;
    private \App\Services\SettingService $settingService;

    public function __construct()
    {
        $this->slideRepository = new SlideRepository();
        $this->announcementRepository = new AnnouncementRepository();
        $this->settingService = new \App\Services\SettingService();
        $this->weatherService = new WeatherService($this->settingService);
    }

    /**
     * Kiosk TV ekranı için tüm afiş, duyuru, hava durumu ve modül yapılandırmalarını tek pakette döner
     */
    public function getKioskData(Request $request): void
    {
        $slides = $this->slideRepository->getActiveSlides();
        $announcements = $this->announcementRepository->getActiveAnnouncements();
        $weather = $this->weatherService->getCurrentWeather();

        $tickerNews = [];
        foreach ($announcements as $announcement) {
            $dateString = '';
            if (!empty($announcement->createdDate)) {
                $dt = DateTime::createFromFormat('Y.m.d H:i:s', $announcement->createdDate);
                $dateString = $dt ? $dt->format('d.m.Y') : $announcement->createdDate;
            }

            $prefix = !empty($announcement->title) ? $announcement->title : $dateString;
            $tickerNews[] = [
                'id' => $announcement->id,
                'prefix' => $prefix,
                'duyuru' => $announcement->content,
                'qrCode' => $announcement->qrCode ?? '',
                'link' => $announcement->link ?? ''
            ];
        }

        // İçerik değişim hash'i (Kırpışmasız güncelleme kontrolü)
        $hash = md5(json_encode($slides) . json_encode($tickerNews));

        Response::json([
            'hash' => $hash,
            'slides' => $slides,
            'tickerNews' => $tickerNews,
            'weather' => $weather,
            'kioskSettings' => [
                'slideInterval' => $this->settingService->getInt('slide_interval', 20) * 1000,
                'videoSound' => $this->settingService->getBool('kiosk_video_sound', true)
            ],
            'modules' => [
                'weather' => $this->settingService->getBool('module_weather', Config::MODULE_WEATHER),
                'clock' => $this->settingService->getBool('module_clock', Config::MODULE_CLOCK),
                'ticker' => $this->settingService->getBool('module_ticker', Config::MODULE_TICKER),
                'qrAnalytics' => $this->settingService->getBool('module_qr_analytics', Config::MODULE_QR_ANALYTICS)
            ],
            'institution' => [
                'name' => $this->settingService->getString('institution_name', Config::INSTITUTION_NAME),
                'campus' => $this->settingService->getString('campus_name', Config::CAMPUS_NAME),
                'appName' => Config::APP_NAME,
                'appTagline' => $this->settingService->getString('app_tagline', Config::APP_TAGLINE)
            ]
        ]);
    }

    /**
     * Sadece hava durumu bilgisini döner (Widget periyodik güncellemesi için)
     */
    public function getWeatherData(Request $request): void
    {
        Response::json($this->weatherService->getCurrentWeather());
    }

    /**
     * breaking-news-ticker kütüphanesi ile geriye dönük uyumlu duyuru JSON listesi
     */
    public function getAnnouncementJSON(Request $request): void
    {
        $announcements = $this->announcementRepository->getActiveAnnouncements();
        $response = [];

        foreach ($announcements as $announcement) {
            $dateString = '';
            if (!empty($announcement->createdDate)) {
                $dt = DateTime::createFromFormat('Y.m.d H:i:s', $announcement->createdDate);
                $dateString = $dt ? $dt->format('d.m.Y') : $announcement->createdDate;
            }

            $prefix = !empty($announcement->title) ? $announcement->title : $dateString;
            $response[] = [
                'prefix' => $prefix,
                'duyuru' => $announcement->content,
                'qrCode' => $announcement->qrCode ?? ''
            ];
        }

        Response::json($response);
    }
}
