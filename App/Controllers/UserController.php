<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\DTO\UserDTO;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Repositories\UserRepository;
use App\Validators\UserValidator;
use Exception;

class UserController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function list(Request $request): void
    {
        AuthMiddleware::handle($request);
        $users = $this->userRepository->getAll();
        Response::json($users);
    }

    public function create(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $data = $request->all();
        $dto = UserDTO::fromArray($data);

        // Şifre tekrar kontrolü
        $pass2 = (string)$request->post('password2', '');
        if ($dto->password !== $pass2) {
            Response::error('Girdiğiniz şifreler birbiriyle uyuşmuyor.');
        }

        $error = UserValidator::validate($dto, false);
        if ($error !== null) {
            Response::error($error);
        }

        // Kullanıcı adı benzersizlik kontrolü
        if ($this->userRepository->findByUsername($dto->userName)) {
            Response::error('Bu kullanıcı adı zaten kullanılmaktadır.');
        }

        try {
            $id = $this->userRepository->create($dto);
            Response::success('Kullanıcı başarıyla oluşturuldu.', ['id' => $id]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function update(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $data = $request->all();
        $id = isset($data['id']) ? (int)$data['id'] : null;

        if ($id === null) {
            Response::error('Geçersiz kullanıcı kimliği.');
        }

        $existing = $this->userRepository->findById($id);
        if (!$existing) {
            Response::error('Güncellenecek kullanıcı bulunamadı.', 404);
        }

        $dto = UserDTO::fromArray($data);

        // Şifre girildiyse tekrar kontrolü
        if ($dto->password !== null) {
            $pass2 = (string)$request->post('password2', '');
            if ($dto->password !== $pass2) {
                Response::error('Girdiğiniz şifreler birbiriyle uyuşmuyor.');
            }
        }

        $error = UserValidator::validate($dto, true);
        if ($error !== null) {
            Response::error($error);
        }

        try {
            $this->userRepository->update($dto);
            Response::success('Kullanıcı bilgileri güncellendi.');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function delete(Request $request): void
    {
        AuthMiddleware::handle($request);
        CsrfMiddleware::handle($request);

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            Response::error('Geçersiz kullanıcı ID.');
        }

        if ($id === 1) {
            Response::error('Ana yönetici hesabı silinemez.');
        }

        $success = $this->userRepository->delete($id);
        if ($success) {
            Response::success('Kullanıcı başarıyla silindi.');
        } else {
            Response::error('Kullanıcı silinemedi.');
        }
    }
}
