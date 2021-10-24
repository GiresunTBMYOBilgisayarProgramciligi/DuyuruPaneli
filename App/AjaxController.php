<?php

namespace App;

use DateTime;

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
        $users = new UsersControler();
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
        $announcementControler->saveNewAnnouncement($data);
        return json_encode($this->response);
    }

    /**
     * Yeni slide oluşturma isteğini gönetir.
     * @param array $data ajax isteği ile gelir. @see ajax.php
     * @return false|string
     */
    public function saveSlide($data = []) {
        $data['image'] = $this->uploadImage();

        try {
            $sliderController = new SlideController();
            $sliderController->saveNewSlide($data);
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
        $this->response = (array)(new UsersControler())->getUsers();
        return json_encode($this->response);
    }

    public function deleteSlide($data = []) {
        $sC = new SlideController();
        $sC->deleteSlide($data['id']);
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
        foreach ($newSlide as $k => $v) {
            $newSlide->$k = $data[$k];
        }
        // check image update
        if ($_FILES['image']['name'] && $oldSlide->image !== "/images/" . $_FILES["image"]['name']) {

            $newSlide->image = $this->uploadImage();
        }
        //check fullWidth
        $newSlide->fullWidth = isset($newSlide->fullWidth) ? 1 : 0;

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
        foreach ($newAnnouncement as $k => $v) {
            $newAnnouncement->$k = $data[$k];
        }
        // check Link
        if ($newAnnouncement->link == $oldAnnouncement->link) {
            $newAnnouncement->link = null;
            $this->response["message"] = "Link değişmeyecek. new=" . $newAnnouncement->link . " | Eski= " . $oldAnnouncement->link;
        } else $newAnnouncement->qrCode = $oldAnnouncement->qrCode;

        try {
            $newAnnouncement->id = $oldAnnouncement->id;
            $announcementController->updateAnnouncement($newAnnouncement);
            $this->response['success'] = "Duyuru güncellendi";
        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
        }
        return json_encode($this->response);
    }

    public function uploadImage() {
        if ($_FILES['image']['name']) {
            try {
                move_uploaded_file($_FILES["image"]["tmp_name"], Config::ROOT_PATH . "images/" . $_FILES['image']['name']);
                return "/images/" . $_FILES["image"]['name'];
            } catch (\Exception $e) {
                $this->response['error'] = "Fotoğraf Yüklenemedi. Hata->" . $e->getMessage();
            }
        }
    }
}