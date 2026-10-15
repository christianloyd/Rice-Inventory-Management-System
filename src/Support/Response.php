<?php

namespace App\Support;

class Response
{
    private mixed $content;
    private int $statusCode;
    private array $headers;

    public function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function json(array $data, int $statusCode = 200): Response
    {
        return new self(json_encode($data), $statusCode, ['Content-Type' => 'application/json']);
    }

    public static function redirect(string $url): Response
    {
        return new self('', 302, ['Location' => $url]);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->content;
        exit;
    }

    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type, // success, error, warning, info
            'message' => $message
        ];
    }
}
