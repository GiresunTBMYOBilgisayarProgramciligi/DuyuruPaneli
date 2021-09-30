<?php

namespace App;

class AjaxController
{
    public $response = [];

    public function checkAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0 && $_POST) return true; else return false;
    }

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
     * @param array $data
     * @return false|string
     */
    public function saveAnnouncement($data = []) {
        $announcementControler = new AnnouncementController();
        $announcementControler->saveNewAnnouncement($data);
        return json_encode($this->response);
    }

    public function saveSlide($data = []) {
        if ($_FILES['image']) {
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], Config::ROOT_PATH."images/" . $_FILES['image']['name'])) {
                $this->response['error'] = "Fotoğraf Yüklenemedi";
                return json_encode($this->response);
            } else
                $data['image'] = "/images/" . $_FILES["image"]['name'];
        }
        try{
            $sliderController = new SlideController();
            $sliderController->saveNewSlide($data);
        }catch (\Exception $e){
            $this->response['error']=$e->getMessage();
        }

        return json_encode($this->response);
    }

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
}