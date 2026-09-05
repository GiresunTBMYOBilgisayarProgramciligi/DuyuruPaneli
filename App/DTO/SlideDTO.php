<?php
declare(strict_types=1);

namespace App\DTO;

class SlideDTO
{
    public ?int $id;
    public string $title;
    public string $content;
    public ?string $image;
    public ?string $link;
    public ?string $youtubeVideoId;
    public int $fullWidth;
    public ?int $userId;
    public int $orderNumber;
    public int $isActive;
    public ?string $startsAt;
    public ?string $expiresAt;
    public int $showCaption;
    public string $qrPosition;

    public function __construct(
        ?int $id,
        string $title,
        string $content,
        ?string $image,
        ?string $link,
        int $fullWidth = 0,
        ?int $userId = null,
        int $orderNumber = 0,
        int $isActive = 1,
        ?string $startsAt = null,
        ?string $expiresAt = null,
        int $showCaption = 1,
        string $qrPosition = 'bottom-right'
    ) {
        $this->id = $id;
        $this->title = trim($title);
        $this->content = trim($content);
        $this->image = $image;
        $this->link = $link !== null && trim($link) !== '' ? trim($link) : null;
        $this->youtubeVideoId = self::extractYouTubeId($this->link);
        $this->fullWidth = $fullWidth;
        $this->userId = $userId;
        $this->orderNumber = $orderNumber;
        $this->isActive = $isActive;
        $this->startsAt = self::normalizeDateTime($startsAt);
        $this->expiresAt = self::normalizeDateTime($expiresAt);
        $this->showCaption = $showCaption;
        $this->qrPosition = in_array($qrPosition, ['bottom-right', 'bottom-left', 'top-right', 'top-left', 'none'], true) ? $qrPosition : 'bottom-right';
    }

    public function isYouTube(): bool
    {
        return !empty($this->youtubeVideoId);
    }

    public static function extractYouTubeId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $pattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';
        if (preg_match($pattern, trim($url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function normalizeDateTime(?string $dateTime): ?string
    {
        if ($dateTime === null) {
            return null;
        }
        $trimmed = trim($dateTime);
        if ($trimmed === '') {
            return null;
        }
        $clean = str_replace('T', ' ', $trimmed);
        $ts = strtotime($clean);
        if ($ts === false) {
            return $clean;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    public static function fromArray(array $data): self
    {
        // HTML formlarında checkbox seçili olmadığında POST verisine dahil edilmez.
        // Form gönderimi sırasında (title veya id varken) checkbox gönderilmediyse 0 kabul edilir.
        $isFormSubmit = array_key_exists('title', $data) || array_key_exists('id', $data);
        $showCaption = $isFormSubmit ? 0 : 1;

        if (!empty($data['showCaption']) || !empty($data['show-caption'])) {
            $showCaption = 1;
        } elseif (isset($data['showCaption']) && ((string)$data['showCaption'] === '0' || (int)$data['showCaption'] === 0)) {
            $showCaption = 0;
        }

        $qrPosition = (string)($data['qrPosition'] ?? $data['qr-position'] ?? 'bottom-right');

        return new self(
            isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null,
            (string)($data['title'] ?? ''),
            (string)($data['content'] ?? ''),
            isset($data['image']) ? (string)$data['image'] : null,
            isset($data['link']) ? (string)$data['link'] : null,
            !empty($data['fullWidth']) || !empty($data['full-width']) ? 1 : 0,
            isset($data['userId']) ? (int)$data['userId'] : null,
            isset($data['orderNumber']) ? (int)$data['orderNumber'] : 0,
            isset($data['isActive']) ? (int)$data['isActive'] : 1,
            isset($data['startsAt']) ? (string)$data['startsAt'] : null,
            isset($data['expiresAt']) ? (string)$data['expiresAt'] : null,
            $showCaption,
            $qrPosition
        );
    }
}
