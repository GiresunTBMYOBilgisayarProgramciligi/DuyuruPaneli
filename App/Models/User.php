<?php
declare(strict_types=1);

namespace App\Models;

class User
{
    public ?int $id = null;
    public string $userName = '';
    public string $mail = '';
    public string $name = '';
    public string $lastName = '';
    public string $createdDate = '';

    public function __construct(?object $data = null)
    {
        if ($data) {
            $this->id = isset($data->id) ? (int)$data->id : null;
            $this->userName = (string)($data->userName ?? '');
            $this->mail = (string)($data->mail ?? '');
            $this->name = (string)($data->name ?? '');
            $this->lastName = (string)($data->lastName ?? '');
            $this->createdDate = (string)($data->createdDate ?? '');
        }
    }

    public function getFullName(): string
    {
        return trim("{$this->name} {$this->lastName}");
    }

    public function getGravatarURL(int $size = 80): string
    {
        $hash = md5(strtolower(trim($this->mail)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s={$size}";
    }
}
