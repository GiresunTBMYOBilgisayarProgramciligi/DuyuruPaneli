<?php
declare(strict_types=1);

namespace App\Controllers;

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
            }
            $dto->image = $imagePath;

            $qrData = $this->qrAnalyticsService->generateForUrl((string)$dto->link, $dto->title);

            $id = $this->slideRepository->create($dto, $qrData['qrSvg'], $qrData['shortCode']);
            Response::success('Slide başarıyla eklendi.', ['id' => $id]);
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
            Response::success('Slide başarıyla güncellendi.');
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
            Response::error('Geçersiz slide ID.');
        }

        $existing = $this->slideRepository->findById($id);
        if ($existing) {
            // İlişkili görseli diskten temizle
            $this->fileUploadService->deleteImage($existing->image);
            $this->slideRepository->delete($id);
        }

        Response::success('Slide ve ilişkili görsel başarıyla silindi.');
    }
}
