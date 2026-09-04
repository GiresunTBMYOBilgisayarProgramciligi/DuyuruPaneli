<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AnnouncementRepository;
use App\Repositories\SlideRepository;
use DateTime;

class ApiController
{
    private SlideRepository $slideRepository;
    private AnnouncementRepository $announcementRepository;

    public function __construct()
    {
        $this->slideRepository = new SlideRepository();
        $this->announcementRepository = new AnnouncementRepository();
    }

    /**
     * Kiosk TV ekranı için tüm afiş ve kayan duyuru verilerini tek pakette döner
     */
    public function getKioskData(Request $request): void
    {
        $slides = $this->slideRepository->getAll();
        $announcements = $this->announcementRepository->getAll();

        $tickerNews = [];
        foreach ($announcements as $announcement) {
            $dateString = '';
            if (!empty($announcement->createdDate)) {
                $dt = DateTime::createFromFormat('Y.m.d H:i:s', $announcement->createdDate);
                $dateString = $dt ? $dt->format('d.m.Y') : $announcement->createdDate;
            }

            $prefix = !empty($announcement->title) ? $announcement->title : $dateString;
            $tickerNews[] = [
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
            'tickerNews' => $tickerNews
        ]);
    }

    /**
     * breaking-news-ticker kütüphanesi ile geriye dönük uyumlu duyuru JSON listesi
     */
    public function getAnnouncementJSON(Request $request): void
    {
        $announcements = $this->announcementRepository->getAll();
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
