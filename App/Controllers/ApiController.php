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

    public function __construct()
    {
        $this->slideRepository = new SlideRepository();
        $this->announcementRepository = new AnnouncementRepository();
        $this->weatherService = new WeatherService();
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
            'modules' => [
                'weather' => Config::MODULE_WEATHER,
                'clock' => Config::MODULE_CLOCK,
                'ticker' => Config::MODULE_TICKER,
                'qrAnalytics' => Config::MODULE_QR_ANALYTICS
            ],
            'institution' => [
                'name' => Config::INSTITUTION_NAME,
                'campus' => Config::CAMPUS_NAME,
                'appName' => Config::APP_NAME,
                'appTagline' => Config::APP_TAGLINE
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
