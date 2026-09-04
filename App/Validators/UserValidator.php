<?php
declare(strict_types=1);

namespace App\Validators;

use App\DTO\UserDTO;

class UserValidator
{
    public static function validate(UserDTO $dto, bool $isUpdate = false): ?string
    {
        if (empty($dto->userName) || mb_strlen($dto->userName) < 3) {
            return "Kullanıcı adı en az 3 karakter olmalıdır.";
        }

        if (empty($dto->mail) || !filter_var($dto->mail, FILTER_VALIDATE_EMAIL)) {
            return "Geçerli bir e-posta adresi giriniz.";
        }

        if (!$isUpdate && (empty($dto->password) || mb_strlen($dto->password) < 6)) {
            return "Şifre en az 6 karakter olmalıdır.";
        }

        if ($isUpdate && $dto->password !== null && mb_strlen($dto->password) < 6) {
            return "Yeni şifre en az 6 karakter olmalıdır.";
        }

        return null;
    }
}
