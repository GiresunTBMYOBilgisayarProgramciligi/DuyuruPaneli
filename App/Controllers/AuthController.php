<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Middlewares\CsrfMiddleware;
use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Yönetici giriş sayfasını render eder
     */
    public function showLogin(Request $request): void
    {
        Session::start();
        if (Session::isLoggedIn()) {
            Response::redirect('/admin');
        }

        $csrfToken = CsrfMiddleware::generateToken();
        View::render('admin.login', [
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * Kimlik doğrulama işlemi (POST)
     */
    public function login(Request $request): void
    {
        CsrfMiddleware::handle($request);

        $userName = (string)$request->post('userName', '');
        $password = (string)$request->post('password', '');

        if (empty($userName) || empty($password)) {
            Response::error('Kullanıcı adı ve şifre zorunludur.');
        }

        if ($this->authService->attempt($userName, $password)) {
            Response::success('Giriş başarılı.', ['redirect' => '/admin']);
        } else {
            Response::error('Kullanıcı adı veya şifre hatalı.', 401);
        }
    }

    /**
     * Oturumu güvenle sonlandırır (POST / GET)
     */
    public function logout(Request $request): void
    {
        $this->authService->logout();
        Response::redirect('/admin/login');
    }
}
