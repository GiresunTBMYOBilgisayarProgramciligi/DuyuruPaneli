<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    /**
     * GET rotası tanımlar
     */
    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * POST rotası tanımlar
     */
    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Hem GET hem POST isteklerini kabul eden rota tanımlar
     */
    public function any(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('ANY', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array $handler, array $middlewares): void
    {
        // Yolu normalize et (başında '/' olmalı, sondaki '/' fazlalığı temizlenmeli - kök hariç)
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath === '//') {
            $normalizedPath = '/';
        }

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $normalizedPath,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Gelen isteği rotalarla eşleştirir ve ilgili Controller'a iletir
     */
    public function dispatch(Request $request): void
    {
        $requestMethod = strtoupper($request->getMethod());
        $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $normalizedUri = '/' . trim($rawUri, '/');
        if ($normalizedUri === '//') {
            $normalizedUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                // GET rotaları aynı zamanda HEAD isteklerini de karşılar
                if (!($route['method'] === 'GET' && $requestMethod === 'HEAD')) {
                    continue;
                }
            }

            // Parametreli rota deseni ({param} -> (?P<param>[^/]+))
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $normalizedUri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // 1. Rota Middleware Zincirini Çalıştır
                foreach ($route['middlewares'] as $middlewareClass) {
                    if (class_exists($middlewareClass) && method_exists($middlewareClass, 'handle')) {
                        $middlewareClass::handle($request);
                    }
                }

                // 2. Controller & Metot Çağrısı
                [$controllerClass, $method] = $route['handler'];

                if (!class_exists($controllerClass)) {
                    Response::error("Controller sınıfı bulunamadı: {$controllerClass}", 500);
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $method)) {
                    Response::error("Metot bulunamadı: {$controllerClass}::{$method}", 500);
                }

                $controller->$method($request, ...array_values($params));
                return;
            }
        }

        // Eşleşen rota bulunamadı -> 404
        http_response_code(404);
        if ($request->isAjax()) {
            Response::error('İstenen sayfa veya uç nokta (endpoint) bulunamadı.', 404);
        } else {
            if (View::exists('errors.404')) {
                View::render('errors.404');
            } else {
                echo "<!DOCTYPE html><html lang='tr'><head><meta charset='utf-8'><title>404 Bulunamadı</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>404 - Sayfa Bulunamadı</h1><p>Aradığınız sayfa mevcut değil veya taşınmış olabilir.</p><a href='/'>Ana Sayfaya Dön</a></body></html>";
            }
        }
        exit;
    }
}
