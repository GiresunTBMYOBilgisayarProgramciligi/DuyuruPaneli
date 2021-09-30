<?php

namespace App;

class AjaxController
{
    public $response = [];

    public function checkAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0 && $_POST) return true; else return false;
    }

    public function login($data=[]) {
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
    public function saveAnnouncement($data=[]){
        $announcementData=[];
            foreach ($data as $d){
                $announcementData[$d["name"]]=$d["value"];
            }
            $announcementControler = new AnnouncementController();
            $announcementControler->saveNewAnnouncement($announcementData);

        return json_encode($this->response);
    }

    public function getAnnouncementsList($data=[]){
        $this->response=(array) (new AnnouncementController())->getAnnouncements();
        return json_encode($this->response);
    }

    public function getSlidesList($data=[]){
        $this->response=(array) (new SlideController())->getSlides();
        return json_encode($this->response);
    }
}