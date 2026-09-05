<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function attempt(string $userName, string $password): bool
    {
        $user = $this->userRepository->findByUsername($userName);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user->password)) {
            Session::login((int)$user->id);
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        Session::logout();
    }

    public function getCurrentUser(): ?object
    {
        $userId = Session::getUserId();
        if ($userId === null) {
            return null;
        }
        return $this->userRepository->findById($userId);
    }
}
