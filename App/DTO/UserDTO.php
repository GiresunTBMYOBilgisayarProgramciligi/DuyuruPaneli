<?php
declare(strict_types=1);

namespace App\DTO;

class UserDTO
{
    public ?int $id;
    public string $userName;
    public string $mail;
    public ?string $password;
    public string $name;
    public string $lastName;

    public function __construct(
        ?int $id,
        string $userName,
        string $mail,
        ?string $password,
        string $name,
        string $lastName
    ) {
        $this->id = $id;
        $this->userName = trim($userName);
        $this->mail = trim($mail);
        $this->password = $password !== null && trim($password) !== '' ? trim($password) : null;
        $this->name = trim($name);
        $this->lastName = trim($lastName);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null,
            (string)($data['userName'] ?? $data['user-name'] ?? ''),
            (string)($data['mail'] ?? ''),
            isset($data['password']) && $data['password'] !== '' ? (string)$data['password'] : null,
            (string)($data['name'] ?? ''),
            (string)($data['lastName'] ?? $data['last-name'] ?? '')
        );
    }
}
