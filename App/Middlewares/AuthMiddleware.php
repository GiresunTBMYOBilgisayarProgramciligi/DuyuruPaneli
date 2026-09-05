<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public static function handle(Request $request): void
    {
        if (!Session::isLoggedIn()) {
            Logger::security("Yetkisiz yönetim paneli erişim girişimi engellendi", [
                'ip' => $request->getIp(),
                'uri' => $_SERVER['REQUEST_URI'] ?? ''
            ]);

            if ($request->isAjax()) {
                Response::error('Oturum süreniz dolmuş veya giriş yapmamışsınız.', 401);
            } else {
                Response::redirect('/admin/login');
            }
        }
    }
}
