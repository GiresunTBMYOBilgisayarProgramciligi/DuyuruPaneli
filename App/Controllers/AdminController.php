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

    /**
     * Güvenli log dosyası indirme (Yalnızca yetkili yöneticiler)
     */
    public function downloadLog(Request $request): void
    {
        AuthMiddleware::handle($request);

        $filename = (string)$request->get('file', '');
        $filename = basename($filename); // Path traversal önlemi

        if (empty($filename)) {
            $files = glob(rtrim(Config::LOG_DIR, '/') . '/*.log');
            if (empty($files)) {
                Response::error('İndirilecek log dosyası bulunamadı.', 404);
            }
            usort($files, static fn(string $a, string $b) => filemtime($b) <=> filemtime($a));
            $filePath = $files[0];
            $filename = basename($filePath);
        } else {
            $filePath = rtrim(Config::LOG_DIR, '/') . '/' . $filename;
        }

        if (!file_exists($filePath) || !str_ends_with($filename, '.log')) {
            Response::error('Geçersiz log dosyası.', 404);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

