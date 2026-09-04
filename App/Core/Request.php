<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;

        // JSON body desteği
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawInput = file_get_contents('php://input');
            $jsonData = json_decode($rawInput, true);
            if (is_array($jsonData)) {
                $this->post = array_merge($this->post, $jsonData);
            }
        }
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isAjax(): bool
    {
        $requestedWith = $this->server['HTTP_X_REQUESTED_WITH'] ?? '';
        return strcasecmp($requestedWith, 'xmlhttprequest') === 0;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function getHeader(string $name): ?string
    {
        $headerName = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$headerName] ?? null;
    }

    public function getCsrfToken(): ?string
    {
        return (string)($this->post('csrf_token') ?? $this->getHeader('X-CSRF-TOKEN') ?? '');
    }

    public function getIp(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function getUserAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function getReferer(): string
    {
        return $this->server['HTTP_REFERER'] ?? '';
    }
}
