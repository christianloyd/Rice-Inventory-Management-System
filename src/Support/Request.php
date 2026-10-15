<?php

namespace App\Support;

class Request
{
    private array $get;
    private array $post;
    private array $server;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Dynamically strip the sub-directory prefix so the app works
        // whether deployed at domain root OR in a subfolder (e.g. /riceinventory/public)
        $scriptDir = rtrim(dirname($this->server['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        return $uri;
    }

    public function input(string $key, $default = null)
    {
        $value = $this->post[$key] ?? $this->get[$key] ?? $default;
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    public function all(): array
    {
        $all = array_merge($this->get, $this->post);
        array_walk_recursive($all, function (&$val) {
            if (is_string($val)) {
                $val = trim($val);
            }
        });
        return $all;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
