<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->getMethod();
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Parametreli rota kontrolü (Örn: /r/{code})
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerClass, $method] = $route['handler'];

                $controller = new $controllerClass();
                $controller->$method($request, ...array_values($params));
                return;
            }
        }

        // Bulunamadı
        http_response_code(404);
        if ($request->isAjax()) {
            Response::error('İstenen sayfa veya API bulunamadı.', 404);
        } else {
            echo "<h1>404 - Sayfa Bulunamadı</h1>";
        }
        exit;
    }
}
