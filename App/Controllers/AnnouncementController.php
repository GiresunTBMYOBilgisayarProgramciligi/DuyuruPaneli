<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\DTO\AnnouncementDTO;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Repositories\AnnouncementRepository;
use App\Services\QrAnalyticsService;
use App\Validators\AnnouncementValidator;
use Exception;

class AnnouncementController
{
    private AnnouncementRepository $announcementRepository;
    private QrAnalyticsService $qrAnalyticsService;

    public function __construct()
    {
        $this->announcementRepository = new AnnouncementRepository();
        $this->qrAnalyticsService = new QrAnalyticsService();
    }

    public function list(Request $request): void
    {
        AuthMiddleware::handle($request);
        $announcements = $this->announcementRepository->getAll();
        Response::json($announcements);
    }

    public function create(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $data = $request->all();
        $data['userId'] = Session::getUserId();
        $dto = AnnouncementDTO::fromArray($data);

        $error = AnnouncementValidator::validate($dto);
        if ($error !== null) {
            Response::error($error);
        }

        try {
            $qrData = $this->qrAnalyticsService->generateForUrl((string)$dto->link, $dto->title);
            $id = $this->announcementRepository->create($dto, $qrData['qrSvg'], $qrData['shortCode']);
            Response::success('Duyuru başarıyla eklendi.', ['id' => $id]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function update(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $data = $request->all();
        $id = isset($data['id']) ? (int)$data['id'] : null;

        if ($id === null) {
            Response::error('Geçersiz duyuru kimliği.');
        }

        $existing = $this->announcementRepository->findById($id);
        if (!$existing) {
            Response::error('Güncellenecek duyuru bulunamadı.', 404);
        }

        $dto = AnnouncementDTO::fromArray($data);
        $error = AnnouncementValidator::validate($dto);
        if ($error !== null) {
            Response::error($error);
        }

        try {
            $qrSvg = null;
            $shortCode = null;
            if ($dto->link !== $existing->link) {
                $qrData = $this->qrAnalyticsService->generateForUrl((string)$dto->link, $dto->title);
                $qrSvg = $qrData['qrSvg'];
                $shortCode = $qrData['shortCode'];
            }

            $this->announcementRepository->update($dto, $qrSvg, $shortCode);
            Response::success('Duyuru başarıyla güncellendi.');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function delete(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::error('Geçersiz duyuru ID.');
        }

        $this->announcementRepository->delete($id);
        Response::success('Duyuru başarıyla silindi.');
    }
}
