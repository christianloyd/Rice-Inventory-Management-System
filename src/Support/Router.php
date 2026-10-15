<?php

namespace App\Support;

class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes as $route) {
            [$rtMethod, $rtPath, $controllerAction, $roles] = $route;
            
            if ($method !== $rtMethod) continue;

            // Simple {id} parameter matching
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $rtPath);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->handle($controllerAction, $roles, $params, $request);
            }
        }

        return new Response('404 Not Found', 404);
    }

    private function handle(string $controllerAction, array $roles, array $params, Request $request): Response
    {
        // 1. Check Auth & Roles
        if ($request->path() !== '/login') {
            \App\Middleware\AuthMiddleware::handle();
        }
        
        if (!empty($roles)) {
            \App\Middleware\RoleMiddleware::handle($roles);
        }

        // 2. Csrf Check automatically for POST
        if ($request->isPost()) {
            \App\Middleware\CsrfMiddleware::handle($request);
        }

        // 3. Dispatch to Controller
        [$controllerName, $action] = explode('@', $controllerAction);
        $fullControllerName = "\\App\\Controllers\\$controllerName";
        
        if (!class_exists($fullControllerName)) {
            throw new \RuntimeException("Controller not found: $fullControllerName");
        }

        $controller = new $fullControllerName();
        // Pack params alongside request
        return $controller->$action($request, ...array_values($params));
    }
}
