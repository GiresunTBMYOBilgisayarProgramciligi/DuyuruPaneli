<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public static function generateToken(): string
    {
        return Session::getCsrfToken();
    }

    public static function handle(Request $request): void
    {
        // Yalnızca veri değiştiren (POST) isteklerde CSRF denetimi yapılır
        if ($request->isPost()) {
            $token = $request->getCsrfToken();
            if (!Session::validateCsrfToken($token)) {
                Response::error('Geçersiz veya süresi dolmuş CSRF güvenlik anahtarı.', 403);
            }
        }
    }
}
