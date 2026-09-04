<?php
declare(strict_types=1);

namespace App\Validators;

use App\DTO\AnnouncementDTO;

class AnnouncementValidator
{
    public static function validate(AnnouncementDTO $dto): ?string
    {
        if (empty($dto->content)) {
            return "Duyuru içeriği boş bırakılamaz.";
        }

        if (!empty($dto->link) && !filter_var($dto->link, FILTER_VALIDATE_URL)) {
            return "Lütfen geçerli bir duyuru linki (URL) giriniz.";
        }

        return null;
    }
}
