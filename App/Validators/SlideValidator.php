<?php
declare(strict_types=1);

namespace App\Validators;

use App\DTO\SlideDTO;

class SlideValidator
{
    public static function validate(SlideDTO $dto, bool $isUpdate = false, bool $hasUploadedImage = false): ?string
    {
        if (empty($dto->title) && empty($dto->content) && !$hasUploadedImage && empty($dto->image) && !$dto->isYouTube()) {
            return "Slide için en az bir görsel, YouTube video bağlantısı, başlık veya içerik girilmelidir.";
        }

        if (!$isUpdate && !$hasUploadedImage && empty($dto->image) && !$dto->isYouTube()) {
            return "Lütfen bir afiş görseli yükleyiniz veya geçerli bir YouTube video bağlantısı giriniz.";
        }

        if (!empty($dto->link) && !filter_var($dto->link, FILTER_VALIDATE_URL)) {
            return "Lütfen geçerli bir internet adresi (URL) giriniz.";
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
                return "Yayında olacak afiş için belirlenen bitiş tarihi gelecekte bir zaman olmalıdır.";
            }
        }

        if ($startTimestamp !== null && $expireTimestamp !== null && $startTimestamp >= $expireTimestamp) {
            return "Yayın bitiş tarihi, yayın başlangıç tarihinden sonra olmalıdır.";
        }

        return null;
    }
}
