<?php
declare(strict_types=1);

namespace App\Models;

class Slide
{
    public ?int $id = null;
    public string $title = '';
    public string $content = '';
    public string $image = '';
    public string $qrCode = '';
    public string $createdDate = '';
    public ?int $userId = null;
    public int $fullWidth = 0;
    public ?string $link = null;
    public ?string $shortCode = null;
    public string $userFullName = '';
    public int $scanCount = 0;
    public int $orderNumber = 0;
    public int $isActive = 1;
    public ?string $startsAt = null;
    public ?string $expiresAt = null;

    public function __construct(?object $data = null)
    {
        if ($data) {
            $this->id = isset($data->id) ? (int)$data->id : null;
            $this->title = (string)($data->title ?? '');
            $this->content = (string)($data->content ?? '');
            $this->image = (string)($data->image ?? '');
            $this->qrCode = (string)($data->qrCode ?? '');
            $this->createdDate = (string)($data->createdDate ?? '');
            $this->userId = isset($data->userId) ? (int)$data->userId : null;
            $this->fullWidth = isset($data->fullWidth) ? (int)$data->fullWidth : 0;
            $this->link = isset($data->link) ? (string)$data->link : null;
            $this->shortCode = isset($data->shortCode) ? (string)$data->shortCode : null;
            $this->userFullName = (string)($data->userFullName ?? '');
            $this->scanCount = isset($data->scanCount) ? (int)$data->scanCount : 0;
            $this->orderNumber = isset($data->orderNumber) ? (int)$data->orderNumber : 0;
            $this->isActive = isset($data->isActive) ? (int)$data->isActive : 1;
            $this->startsAt = isset($data->startsAt) ? (string)$data->startsAt : null;
            $this->expiresAt = isset($data->expiresAt) ? (string)$data->expiresAt : null;
        }
    }
}
