<?php
declare(strict_types=1);

namespace App\DTO;

class AnnouncementDTO
{
    public ?int $id;
    public string $title;
    public string $content;
    public ?string $link;
    public ?int $userId;
    public int $orderNumber;
    public int $isActive;
    public ?string $startsAt;
    public ?string $expiresAt;

    public function __construct(
        ?int $id,
        string $title,
        string $content,
        ?string $link,
        ?int $userId = null,
        int $orderNumber = 0,
        int $isActive = 1,
        ?string $startsAt = null,
        ?string $expiresAt = null
    ) {
        $this->id = $id;
        $this->title = trim($title);
        $this->content = trim($content);
        $this->link = $link !== null && trim($link) !== '' ? trim($link) : null;
        $this->userId = $userId;
        $this->orderNumber = $orderNumber;
        $this->isActive = $isActive;
        $this->startsAt = self::normalizeDateTime($startsAt);
        $this->expiresAt = self::normalizeDateTime($expiresAt);
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
        $isActive = 1;
        if (isset($data['isActive'])) {
            $isActive = (int)$data['isActive'];
        } elseif (isset($data['is-active'])) {
            $isActive = (int)$data['is-active'];
        }

        return new self(
            isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null,
            (string)($data['title'] ?? ''),
            (string)($data['content'] ?? ''),
            isset($data['link']) ? (string)$data['link'] : null,
            isset($data['userId']) ? (int)$data['userId'] : (isset($data['user_id']) ? (int)$data['user_id'] : null),
            isset($data['orderNumber']) ? (int)$data['orderNumber'] : (isset($data['order_number']) ? (int)$data['order_number'] : 0),
            $isActive,
            isset($data['startsAt']) ? (string)$data['startsAt'] : (isset($data['starts-at']) ? (string)$data['starts-at'] : null),
            isset($data['expiresAt']) ? (string)$data['expiresAt'] : (isset($data['expires-at']) ? (string)$data['expires-at'] : null)
        );
    }
}
