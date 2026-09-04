<?php
declare(strict_types=1);

namespace App\Validators;

use App\DTO\SlideDTO;

class SlideValidator
{
    public static function validate(SlideDTO $dto, bool $isUpdate = false, bool $hasUploadedImage = false): ?string
    {
        if (empty($dto->title) && empty($dto->content) && !$hasUploadedImage && empty($dto->image)) {
            return "Slide için en az bir görsel, başlık veya içerik girilmelidir.";
        }

        if (!$isUpdate && !$hasUploadedImage && empty($dto->image)) {
            return "Lütfen bir afiş görseli yükleyiniz.";
        }

        if (!empty($dto->link) && !filter_var($dto->link, FILTER_VALIDATE_URL)) {
            return "Lütfen geçerli bir internet adresi (URL) giriniz.";
        }

        return null;
    }
}
