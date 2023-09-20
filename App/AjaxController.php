<?php

namespace App;

use DateTime;
use App\Helper;

class AjaxController
{
    public $response = [];

    public function checkAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0) return true; else return false;
    }

    /**
     * Kullanıcı oturum açma isteğini yönetir
     * @param array $data ajax isteği ile gelir. @see ajax.php
     * @return false|string
     */
    public function login($data = []) {
        $users = new UsersController();
        try {
            $users->login($data);
        } catch (\Exception $e) {
            $this->response ['error'] = $e->getMessage();
        }
        return json_encode($this->response);
    }

    /**
     * Yeni duyuru oluşturma isteğini yönetir.
     * @param array $data ajax isteği ile gelir. @see ajax.php
     * @return false|string
     *
     */
    public function saveAnnouncement($data = []) {
        $announcementControler = new AnnouncementController();
        $this->response = $announcementControler->saveNewAnnouncement($data);
        return json_encode($this->response);
    }

    /**
     * Yeni slide oluşturma isteğini gönetir.
     * @param array $data ajax isteği ile gelir. @see ajax.php
     * @return false|string
     */
    public function saveSlide($data = []) {
        try {
            if ($image = $this->uploadImage()) {
                $data['image'] = $image;
            } else return json_encode($this->response);
            $sliderController = new SlideController();
            $this->response = $sliderController->saveNewSlide($data);
        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
        }

        return json_encode($this->response);
    }

    public function saveUser($data = []) {
        $newUser = new User();
        $newUser->fillUser($data);
        try {
            $userController = new UsersController();
            $this->response = $userController->saveNewUser($newUser);
        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
        }

        return json_encode($this->response);
    }

    /**
     * Duyuruların bilgilerini içeren bir liste oluşturmak için json verisi döndürür.
     * @param array $data
     * @return false|string Announcement list in json
     */
    public function getAnnouncementsList($data = []) {
        $this->response = (array)(new AnnouncementController())->getAnnouncements();
        return json_encode($this->response);
    }

    public function getSlidesList($data = []) {
        $this->response = (array)(new SlideController())->getSlides();
        return json_encode($this->response);
    }

    public function getUsersList($data = []) {
        $this->response = (array)(new UsersController())->getUsers();
        return json_encode($this->response);
    }

    public function deleteSlide($data = []) {
        $sC = new SlideController();
        $sC->deleteSlide($data['id']);
        return json_encode($this->response);
    }

    public function deleteUser($data = []) {
        $UC = new UsersController();
        $this->response = $UC->deleteUser($data['id']);
        return json_encode($this->response);
    }

    public function deleteAnnouncement($data = []) {
        $aC = new AnnouncementController();
        $aC->deleteAnnouncement($data['id']);
        return json_encode($this->response);
    }

    public function getAnnouncementJSON() {
        $ac = new AnnouncementController();
        $a = $ac->getAnnouncements();

        foreach ($a as $announcement) {
            $myDateTime = DateTime::createFromFormat('Y.m.d H:i:s', $announcement->createdDate);
            $newDateString = $myDateTime->format('d.m.Y');
            $this->response[] = ["prefix" => $announcement->title == "" ? $newDateString : $announcement->title, "duyuru" => $announcement->content, "qrCode" => $announcement->qrCode];
        }
        return json_encode($this->response);
    }

    /**
     * Ajax ile gelen slide güncelleme isteğini yönetir.
     * @param array $data
     */
    public function updateSlide($data = []) {
        if (count($data) == 0) {
            $this->response['error'] = "Gelen veri yok";
            return json_encode($this->response);
        }
        $sliderController = new SlideController();

        $oldSlide = new Slide($data["id"]);
        $newSlide = new Slide();
        $newSlide->fillSlide($data);
        // check image update
        if ($_FILES['image']['name'] && $oldSlide->image !== "/images/" . $_FILES["image"]['name']) {
            if ($image = $this->uploadImage())
                $newSlide->image = $image;
        }
        //check fullWidth
        $newSlide->fullWidth = isset($newSlide->fullWidth) ? 1 : 0;

        // check Link
        if ($newSlide->link == $oldSlide->link) {
            $newSlide->link = null;
            $this->response["message"] = "Link değişmeyecek. new=" . $newSlide->link . " | Eski= " . $oldSlide->link;
        } else $newSlide->qrCode = $oldSlide->qrCode;
        try {
            $newSlide->id = $oldSlide->id;
            $sliderController->updateSlide($newSlide);
            $this->response['success'] = "Slide güncellendi";
        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
        }
        return json_encode($this->response);
    }

    public function updateAnnouncement($data = []) {
        if (count($data) == 0) {
            $this->response['error'] = "Gelen veri yok";
            return json_encode($this->response);
        }
        $announcementController = new AnnouncementController();

        $oldAnnouncement = new Announcement($data['id']);
        $newAnnouncement = new Announcement();
        $newAnnouncement->fillAnnouncement($data);
        // check Link
        if ($newAnnouncement->link == $oldAnnouncement->link) {
            $newAnnouncement->link = null;
            $this->response["message"] = "Link değişmeyecek. new=" . $newAnnouncement->link . " | Eski= " . $oldAnnouncement->link;
        } else $newAnnouncement->qrCode = $oldAnnouncement->qrCode;

        try {
            $newAnnouncement->id = $oldAnnouncement->id;
            $this->response = $announcementController->updateAnnouncement($newAnnouncement);
        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
        }
        return json_encode($this->response);
    }

    public function updateUser($data = []) {
        if (count($data) == 0) {
            $this->response['error'] = "Gelen veri yok";
            return json_encode($this->response);
        }
        $oldUser = new User($data['id']);
        $newUser = new User();
        $newUser->fillUser($data);

        try {
            $userController = new UsersController();
            $this->response = $userController->updateUser($newUser);
        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
        }

        return json_encode($this->response);
    }

    /**
     * @return string|void
     *
     */
    public function uploadImage() {
        if ($_FILES['image']['name']) {
            $file = Config::ROOT_PATH."images/" . basename($_FILES["image"]["name"]);

            // Dosya türünü kontrol etmek için izin verilen dosya uzantıları
            $allowedExtensions = array("jpg", "jpeg", "png", "gif");

            // Dosya uzantısını al
            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Try to upload file
            try {
                // Dosyanın izin verilen uzantılardan birine sahip olup olmadığını kontrol et
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new \Exception("Hata: Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir.");
                } elseif ($_FILES["image"]["size"] > Helper::getUploadMaxSizeToBytes()) {
                    throw new \Exception("Hata: Dosya boyutu çok büyük.");
                } else {
                    // Dosyayı yükle
                    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $file)) {
                        $error = error_get_last();
                        throw new \Exception("Hata: Dosya yüklenirken bir sorun oluştu.\n" . $error['message'] . "\n" . $error['file'] . ":" . $error['line']);
                    }
                }
                return "/images/" . $_FILES["image"]['name'];

            } catch (\Exception $e) {
                // Fail!
                $this->response['error'] = $e->getMessage();
                return false;
            }
        }
    }
}