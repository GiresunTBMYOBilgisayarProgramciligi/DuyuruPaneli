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
            $q->execute($newUser);
            if ($q) {
                return ['success' => 'Slide eklendi'];
            } else {
                return ['error' => "Slide eklenemedi"];
            }
        }
    }

    public function updateUser(User $user) {
        if ($user->password == "") {
            unset($user->password);
        } else {
            $user->password = password_hash($user->password, PASSWORD_DEFAULT);
        }
        foreach ($user as $k => $v) {
            if (is_null($user->$k)) unset($user->$k);
        }
        $numItem = count((array)$user) - 1;// id sorguya kalıtmadığı için 1 çıkartıyorum
        $i = 0;

        $query = "UPDATE user SET ";
        foreach ($user as $k => $v) {
            if ($k !== 'id') {
                if (++$i === $numItem) $query .= $k . "='" . $v . "' "; else $query .= $k . "='" . $v . "', ";
            }
        }
        $query .= " WHERE id=" . $user->id;

        $u = $this->DB->query($query);
        if ($u) {
            return ['success' => 'Kullanıcı güncellendi'];
        } else {
            //return ['error' => "Slide güncellenemedi"];
            return ['error' => $query];

        }
    }

    public function deleteUser($id = null) {
        if (is_null($id)) return ['error' => "ID boş"];
        if ($id == 1) return ['error' => "Yönetici Silinemez"];
        $this->DB->query("DELETE FROM user WHERE id=:id")->execute(array(":id" => $id));
        return ['success' => "Kullanıcı silindi"];
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