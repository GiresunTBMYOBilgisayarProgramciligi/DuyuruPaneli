<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Repositories\UserRepository;

class AdminController
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    /**
     * Yönetim paneli ana kontrol ekranı
     */
    public function index(Request $request): void
    {
        AuthMiddleware::handle($request);

        $userId = Session::get(Config::SESSION_AUTH_KEY);
        $currentUser = $userId ? $this->userRepo->findById((int)$userId) : null;
        $csrfToken = CsrfMiddleware::generateToken();

        View::render('admin.dashboard', [
            'currentUser' => $currentUser,
            'csrfToken' => $csrfToken
        ]);
    }
}
