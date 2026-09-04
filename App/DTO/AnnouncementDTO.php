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

    public function __construct(
        ?int $id,
        string $title,
        string $content,
        ?string $link,
        ?int $userId = null
    ) {
        $this->id = $id;
        $this->title = trim($title);
        $this->content = trim($content);
        $this->link = $link !== null && trim($link) !== '' ? trim($link) : null;
        $this->userId = $userId;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null,
            (string)($data['title'] ?? ''),
            (string)($data['content'] ?? ''),
            isset($data['link']) ? (string)$data['link'] : null,
            isset($data['userId']) ? (int)$data['userId'] : null
        );
    }
}
