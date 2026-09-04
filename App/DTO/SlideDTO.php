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
    public int $fullWidth;
    public ?int $userId;

    public function __construct(
        ?int $id,
        string $title,
        string $content,
        ?string $image,
        ?string $link,
        int $fullWidth = 0,
        ?int $userId = null
    ) {
        $this->id = $id;
        $this->title = trim($title);
        $this->content = trim($content);
        $this->image = $image;
        $this->link = $link !== null && trim($link) !== '' ? trim($link) : null;
        $this->fullWidth = $fullWidth;
        $this->userId = $userId;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null,
            (string)($data['title'] ?? ''),
            (string)($data['content'] ?? ''),
            isset($data['image']) ? (string)$data['image'] : null,
            isset($data['link']) ? (string)$data['link'] : null,
            !empty($data['fullWidth']) || !empty($data['full-width']) ? 1 : 0,
            isset($data['userId']) ? (int)$data['userId'] : null
        );
    }
}
