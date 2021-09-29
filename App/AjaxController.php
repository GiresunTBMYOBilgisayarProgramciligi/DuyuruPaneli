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
        $u = ['userName' => $data['userName'], 'password' => $data['password']];
        try {
            $users->login($u);
        } catch (\Exception $e) {
            $this->response ['error'] = $e->getMessage();
        }
        return json_encode($this->response);
    }
}