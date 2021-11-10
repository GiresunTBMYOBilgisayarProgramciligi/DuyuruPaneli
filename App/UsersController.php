<?php

namespace App;

use PDO;

class UsersController
{
    public $DB;

    public function __construct() {
        $this->DB = (new SQLiteConnection())->pdo;
    }

    public function getUser($id = null) {
        if ($id === null && $_COOKIE[Config::LOGIN_COOKIE_NAME] > 0) {
            return new User($_COOKIE[Config::LOGIN_COOKIE_NAME]);
        } else
            return new User($id);
    }

    public function getCurrentUserId() {
        return $this->getUser()->id;
    }

    public function getUsers() {
        return $this->DB->query("select * from user", PDO::FETCH_OBJ)->fetchAll();
    }

    public function saveNewUser(User $newUser) {
        $q = $this->DB->prepare("INSERT INTO user(userName, mail, password, name, lastName, createdDate) values  (:userName,:mail,:password,:name,:lastName,:createdDate)");
        if ($q) {
            $newUser = (array)$newUser;
            unset($newUser["db"]);
            unset($newUser['id']);
            $newUser["password"] = password_hash($newUser["password"], PASSWORD_DEFAULT);
            $newUser["createdDate"] = date("Y.m.d H:i:s");
            //var_export($newUser);
            $q->execute($newUser);
        }
    }

    public function isLoggedIn() {
        if (isset($_COOKIE[Config::LOGIN_COOKIE_NAME])) return new User($_COOKIE[Config::LOGIN_COOKIE_NAME]); else
            return false;
    }

    /**
     *
     * @param $arr
     * @return User
     * @throws \Exception
     */
    public function login($arr) {
        $arr = (object)$arr;
        $user = $this->DB->query("Select * from user where userName='$arr->userName'", PDO::FETCH_OBJ);
        if ($user) {
            $user = $user->fetch();
            if ($user) {
                if (password_verify($arr->password, $user->password)) {
                    setcookie(Config::LOGIN_COOKIE_NAME, $user->id, time() + (86400 * 30));
                } else throw new \Exception("Şifre Yanlış");
            } else throw new \Exception("Kullanıcı kayıtlı değil");
        } else throw new \Exception("Hiçbir kullanıcı kayıtlı değil");

    }
}