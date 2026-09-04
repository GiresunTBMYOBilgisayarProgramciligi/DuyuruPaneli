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

        $startTimestamp = null;
        if (!empty($dto->startsAt)) {
            $startTimestamp = strtotime($dto->startsAt);
            if ($startTimestamp === false) {
                return "Lütfen geçerli bir yayın başlangıç tarihi seçiniz.";
            }
        }

        $expireTimestamp = null;
        if (!empty($dto->expiresAt)) {
            $expireTimestamp = strtotime($dto->expiresAt);
            if ($expireTimestamp === false) {
                return "Lütfen geçerli bir yayın bitiş tarihi seçiniz.";
            }

            if ($dto->isActive === 1 && $expireTimestamp <= time()) {
                return "Yayında olacak duyuru için belirlenen bitiş tarihi gelecekte bir zaman olmalıdır.";
            }
        }

        if ($startTimestamp !== null && $expireTimestamp !== null && $startTimestamp >= $expireTimestamp) {
            return "Yayın bitiş tarihi, yayın başlangıç tarihinden sonra olmalıdır.";
        }

        return null;
    }
}
