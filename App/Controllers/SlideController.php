<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\DTO\SlideDTO;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Repositories\SlideRepository;
use App\Services\FileUploadService;
use App\Services\QrAnalyticsService;
use App\Validators\SlideValidator;
use Exception;

class SlideController
{
    private SlideRepository $slideRepository;
    private FileUploadService $fileUploadService;
    private QrAnalyticsService $qrAnalyticsService;

    public function __construct()
    {
        $this->slideRepository = new SlideRepository();
        $this->fileUploadService = new FileUploadService();
        $this->qrAnalyticsService = new QrAnalyticsService();
    }

    public function list(Request $request): void
    {
        AuthMiddleware::handle($request);
        $slides = $this->slideRepository->getAll();
        Response::json($slides);
    }

    public function create(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $data = $request->all();
        $data['userId'] = Session::getUserId();
        $file = $request->file('image');

        $hasUploadedImage = !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name']);
        $dto = SlideDTO::fromArray($data);

        $error = SlideValidator::validate($dto, false, $hasUploadedImage);
        if ($error !== null) {
            Response::error($error);
        }

        try {
            $imagePath = '';
            if ($hasUploadedImage) {
                $imagePath = $this->fileUploadService->uploadImage($file);
            } elseif ($dto->isYouTube()) {
                $imagePath = $this->fileUploadService->downloadYouTubeThumbnail((string)$dto->youtubeVideoId);
            }
            $dto->image = $imagePath;

            $qrData = $this->qrAnalyticsService->generateForUrl((string)$dto->link, $dto->title);

            $id = $this->slideRepository->create($dto, $qrData['qrSvg'], $qrData['shortCode']);
            Logger::audit("Yeni afiş oluşturuldu", ['id' => $id, 'title' => $dto->title]);
            Response::success('Slide başarıyla eklendi.', ['id' => $id]);
        } catch (Exception $e) {
            Logger::exception($e, 'Afiş oluşturulurken hata');
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
            Response::error('Geçersiz slide kimliği.');
        }

        $existing = $this->slideRepository->findById($id);
        if (!$existing) {
            Response::error('Güncellenecek slide bulunamadı.', 404);
        }

        $file = $request->file('image');
        $hasUploadedImage = !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name']);
        $dto = SlideDTO::fromArray($data);

        $error = SlideValidator::validate($dto, true, $hasUploadedImage);
        if ($error !== null) {
            Response::error($error);
        }

        try {
            // Yeni görsel yüklendiyse eskisini sil ve yenisini kaydet
            if ($hasUploadedImage) {
                $newImagePath = $this->fileUploadService->uploadImage($file);
                $this->fileUploadService->deleteImage($existing->image);
                $dto->image = $newImagePath;
            } elseif ($dto->isYouTube() && (empty($existing->image) || str_starts_with(basename((string)$existing->image), 'yt_'))) {
                // Eğer mevcut görsel yoksa veya önceki otomatik YouTube kapağıysa, yeni video kapağını al
                if ($dto->youtubeVideoId !== SlideDTO::extractYouTubeId($existing->link ?? null) || empty($existing->image)) {
                    $dto->image = $this->fileUploadService->downloadYouTubeThumbnail((string)$dto->youtubeVideoId);
                } else {
                    $dto->image = null; // Mevcut resmi koru
                }
            } else {
                $dto->image = null; // Mevcut resmi koru
            }

            // Link değiştiyse QR kodu ve analitiği yeniden üret
            $qrSvg = null;
            $shortCode = null;
            if ($dto->link !== $existing->link) {
                $qrData = $this->qrAnalyticsService->generateForUrl((string)$dto->link, $dto->title);
                $qrSvg = $qrData['qrSvg'];
                $shortCode = $qrData['shortCode'];
            }

            $this->slideRepository->update($dto, $qrSvg, $shortCode);
            Logger::audit("Afiş güncellendi", ['id' => $dto->id, 'title' => $dto->title]);
            Response::success('Slide başarıyla güncellendi.');
        } catch (Exception $e) {
            Logger::exception($e, 'Afiş güncellenirken hata');
            Response::error($e->getMessage());
        }
    }

    public function delete(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::error('Geçersiz slide ID.');
        }

        $existing = $this->slideRepository->findById($id);
        if ($existing) {
            // İlişkili görseli diskten temizle
            $this->fileUploadService->deleteImage($existing->image);
            $this->slideRepository->delete($id);
            Logger::audit("Afiş silindi", ['id' => $id, 'title' => $existing->title ?? '']);
        }

        Response::success('Slide ve ilişkili görsel başarıyla silindi.');
    }

    public function toggleStatus(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::error('Geçersiz slide ID.');
        }

        $existing = $this->slideRepository->findById($id);
        if (!$existing) {
            Response::error('Slide bulunamadı.', 404);
        }

        // Eğer duraklatılmış ve süresi dolmuşsa, doğrudan aktife almayı engelle
        if ((int)$existing->isActive === 0 && !empty($existing->expiresAt) && strtotime($existing->expiresAt) <= time()) {
            Response::error('Bu afişin yayın süresi dolmuştur. Tekrar yayına almak için lütfen düzenle (✏️) penceresinden bitiş tarihini güncelleyiniz veya kaldırınız.');
        }

        $this->slideRepository->toggleStatus($id);
        $newState = ((int)$existing->isActive === 1) ? 0 : 1;
        $isScheduled = !empty($existing->startsAt) && strtotime($existing->startsAt) > time();
        if ($newState === 1) {
            $msg = $isScheduled
                ? 'Afiş takvime alındı (belirlenen başlangıç tarihinde otomatik yayınlanacaktır).'
                : 'Afiş başarıyla yayına alındı.';
        } else {
            $msg = 'Afiş yayından kaldırıldı (duraklatıldı).';
        }

        Logger::audit("Afiş yayın durumu değiştirildi", [
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
            Response::error('Geçersiz slide ID.');
        }

        $this->slideRepository->updateOrder($id, $orderNumber);
        Response::success('Afiş sıralaması başarıyla güncellendi.', ['orderNumber' => $orderNumber]);
    }
}
