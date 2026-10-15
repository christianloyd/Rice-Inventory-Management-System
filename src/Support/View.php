<?php

namespace App\Support;

class View
{
    public static function render(string $template, array $data = []): Response
    {
        extract($data);
        
        ob_start();
        require __DIR__ . "/../../views/{$template}.php";
        $content = ob_get_clean();

        return new Response($content);
    }
}


