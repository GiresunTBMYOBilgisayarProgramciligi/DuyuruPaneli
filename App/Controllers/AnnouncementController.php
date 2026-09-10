<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
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
            $id = $this->announcementRepository->create($dto, $qrData['qrSvg'], $qrData['shortCode'], $qrData['externalShortUrl'] ?? null);
            Logger::audit("Yeni kayan duyuru oluşturuldu", ['id' => $id, 'title' => $dto->title]);
            Response::success('Duyuru başarıyla eklendi.', ['id' => $id]);
        } catch (Exception $e) {
            Logger::exception($e, 'Kayan duyuru oluşturulurken hata');
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
            $externalShortUrl = null;
            if ($dto->link !== $existing->link) {
                $qrData = $this->qrAnalyticsService->generateForUrl((string)$dto->link, $dto->title);
                $qrSvg = $qrData['qrSvg'];
                $shortCode = $qrData['shortCode'];
                $externalShortUrl = $qrData['externalShortUrl'] ?? null;
            }

            $this->announcementRepository->update($dto, $qrSvg, $shortCode, $externalShortUrl);
            Logger::audit("Kayan duyuru güncellendi", ['id' => $dto->id, 'title' => $dto->title]);
            Response::success('Duyuru başarıyla güncellendi.');
        } catch (Exception $e) {
            Logger::exception($e, 'Kayan duyuru güncellenirken hata');
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

        $existing = $this->announcementRepository->findById($id);
        $this->announcementRepository->delete($id);
        Logger::audit("Kayan duyuru silindi", ['id' => $id, 'title' => $existing->title ?? '']);

        Response::success('Duyuru başarıyla silindi.');
    }

    public function toggleStatus(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::error('Geçersiz duyuru ID.');
        }

        $existing = $this->announcementRepository->findById($id);
        if (!$existing) {
            Response::error('Duyuru bulunamadı.', 404);
        }

        // Eğer duraklatılmış ve süresi dolmuşsa, doğrudan aktife almayı engelle
        if ((int)$existing->isActive === 0 && !empty($existing->expiresAt) && strtotime($existing->expiresAt) <= time()) {
            Response::error('Bu duyurunun yayın süresi dolmuştur. Tekrar yayına almak için lütfen düzenle (✏️) penceresinden bitiş tarihini güncelleyiniz veya kaldırınız.');
        }

        $this->announcementRepository->toggleStatus($id);
        $newState = ((int)$existing->isActive === 1) ? 0 : 1;
        $isScheduled = !empty($existing->startsAt) && strtotime($existing->startsAt) > time();
        if ($newState === 1) {
            $msg = $isScheduled
                ? 'Duyuru takvime alındı (belirlenen başlangıç tarihinde otomatik yayınlanacaktır).'
                : 'Duyuru başarıyla yayına alındı.';
        } else {
            $msg = 'Duyuru yayından kaldırıldı (duraklatıldı).';
        }

        Logger::audit("Kayan duyuru yayın durumu değiştirildi", [
            'id' => $id,
            'title' => $existing->title ?? '',
            'isActive' => $newState
        ]);

        Response::success($msg, ['isActive' => $newState]);
    }

    public function updateOrder(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $id = (int)$request->input('id', 0);
        $orderNumber = (int)$request->input('orderNumber', 0);

        if ($id <= 0) {
            Response::error('Geçersiz duyuru ID.');
        }

        $this->announcementRepository->updateOrder($id, $orderNumber);
        Response::success('Duyuru sıralaması güncellendi.', ['orderNumber' => $orderNumber]);
    }
}
