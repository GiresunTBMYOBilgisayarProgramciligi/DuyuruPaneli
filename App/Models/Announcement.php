<?php
declare(strict_types=1);

namespace App\Models;

class Announcement
{
    public ?int $id = null;
    public string $title = '';
    public string $content = '';
    public string $qrCode = '';
    public string $createdDate = '';
    public ?int $userId = null;
    public ?string $link = null;
    public ?string $shortCode = null;
    public string $userFullName = '';
    public int $scanCount = 0;

    public function __construct(?object $data = null)
    {
        if ($data) {
            $this->id = isset($data->id) ? (int)$data->id : null;
            $this->title = (string)($data->title ?? '');
            $this->content = (string)($data->content ?? '');
            $this->qrCode = (string)($data->qrCode ?? '');
            $this->createdDate = (string)($data->createdDate ?? '');
            $this->userId = isset($data->userId) ? (int)$data->userId : null;
            $this->link = isset($data->link) ? (string)$data->link : null;
            $this->shortCode = isset($data->shortCode) ? (string)$data->shortCode : null;
            $this->userFullName = (string)($data->userFullName ?? '');
            $this->scanCount = isset($data->scanCount) ? (int)$data->scanCount : 0;
        }
    }
}
