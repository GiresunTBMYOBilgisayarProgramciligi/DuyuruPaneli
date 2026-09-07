<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Core\Request;
use App\Core\View;
use App\Repositories\SlideRepository;
use App\Repositories\AnnouncementRepository;
use App\Services\WeatherService;
use DateTime;
use DateTimeZone;

class KioskController
{
    private SlideRepository $slideRepo;
    private AnnouncementRepository $announcementRepo;
    private WeatherService $weatherService;

    public function __construct()
    {
        $this->slideRepo = new SlideRepository();
        $this->announcementRepo = new AnnouncementRepository();
        $this->weatherService = new WeatherService();
    }

    public function index(Request $request): void
    {
        date_default_timezone_set('Europe/Istanbul');

        $slides = $this->slideRepo->getActiveSlides();
        $announcements = $this->announcementRepo->getActiveAnnouncements();
        $weather = $this->weatherService->getCurrentWeather();

        $host = $_SERVER['HTTP_HOST'] ?? 'unipano.loc';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

        $initialTickerData = [];
        foreach ($announcements as $announcement) {
            $dateString = '';
            if (!empty($announcement->createdDate)) {
                $dt = DateTime::createFromFormat('Y.m.d H:i:s', $announcement->createdDate);
                $dateString = $dt ? $dt->format('d.m.Y') : $announcement->createdDate;
            }

            $prefix = !empty($announcement->title) ? $announcement->title : $dateString;
            $shortCode = $announcement->shortCode ?? '';
            $shortUrl = !empty($shortCode) ? "{$scheme}://{$host}/r/{$shortCode}" : ($announcement->link ?? '');
            $shortPath = !empty($shortCode) ? "/r/{$shortCode}" : '';
            $shortDisplay = !empty($shortCode) ? "{$host}/r/{$shortCode}" : '';

            $initialTickerData[] = [
                'id' => $announcement->id,
                'prefix' => $prefix,
                'duyuru' => $announcement->content,
                'qrCode' => $announcement->qrCode ?? '',
                'link' => $announcement->link ?? '',
                'shortCode' => $shortCode,
                'shortUrl' => $shortUrl,
                'shortHost' => $host,
                'shortPath' => $shortPath,
                'shortDisplay' => $shortDisplay
            ];
        }

        $turkishMonths = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
            7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
        ];
        $turkishDays = [
            'Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar'
        ];

        $now = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
        $initialTime = $now->format('H:i:s');
        $monthName = $turkishMonths[(int)$now->format('n')] ?? '';
        $dayName = $turkishDays[$now->format('l')] ?? '';
        $initialDate = $now->format('j') . ' ' . $monthName . ' ' . $now->format('Y') . ', ' . $dayName;

        $firstAnnouncement = $initialTickerData[0] ?? null;
        $firstPrefix = $firstAnnouncement['prefix'] ?? 'DUYURULAR';
        $firstDuyuru = $firstAnnouncement['duyuru'] ?? 'Güncel duyuru bulunmamaktadır.';
        $firstQr = $firstAnnouncement['qrCode'] ?? '';
        $firstShortHost = $firstAnnouncement['shortHost'] ?? $host;
        $firstShortPath = $firstAnnouncement['shortPath'] ?? '';
        $firstShortDisplay = $firstAnnouncement['shortDisplay'] ?? '';
        $firstShortUrl = $firstAnnouncement['shortUrl'] ?? '';
        $hasFirstQr = !empty(trim($firstQr));

        $initialContentHash = md5(json_encode($slides) . json_encode($initialTickerData));

        $settingService = new \App\Services\SettingService();
        $slideIntervalMs = $settingService->getInt('slide_interval', 20) * 1000;
        $kioskVideoSound = $settingService->getBool('kiosk_video_sound', true);

        View::render('kiosk.index', [
            'slides' => $slides,
            'announcements' => $announcements,
            'weather' => $weather,
            'initialTickerData' => $initialTickerData,
            'initialTime' => $initialTime,
            'initialDate' => $initialDate,
            'firstPrefix' => $firstPrefix,
            'firstDuyuru' => $firstDuyuru,
            'firstQr' => $firstQr,
            'firstShortHost' => $firstShortHost,
            'firstShortPath' => $firstShortPath,
            'firstShortDisplay' => $firstShortDisplay,
            'firstShortUrl' => $firstShortUrl,
            'hasFirstQr' => $hasFirstQr,
            'initialContentHash' => $initialContentHash,
            'slideIntervalMs' => $slideIntervalMs,
            'kioskVideoSound' => $kioskVideoSound
        ]);
    }
}
