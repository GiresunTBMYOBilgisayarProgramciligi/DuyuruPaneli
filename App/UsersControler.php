<?php

namespace App;

use PDO;

class UsersControler
{
    public $DB;

    public function __construct() {
        $this->DB= (new SQLiteConnection())->pdo;
    }

    public function getUser($id) {
        return new User($id);
    }

    public function getUsers(){
        return $this->DB->query(
            "select * from user",PDO::FETCH_OBJ)->fetchAll();
    }

    public function saveNewUser($arr) {
        $this->DB->prepare("INSERT INTO user( ) values (:title,:content,:image,:qrCode,:createdDate,:userId)")->execute($arr);
    }

    public function isLoggedIn() {
        if (isset($_COOKIE["tmyoOturum"])) return new User($_COOKIE["tmyoOturum"]); else
            return false;
    }

    /**
     * todo şifre hashlenecek
     * @param $arr
     * @return User
     * @throws \Exception
     */
    public function login($arr){
        $arr=(object) $arr;
        $u=$this->DB->query("Select * from user where userName='$arr->userName' and password='$arr->password'",PDO::FETCH_OBJ)->fetch();

        if(is_object($u)){
            setcookie("tmyoOturum",$u->id,time() + (86400 * 30));
        }else{
            throw new \Exception("Girdiğiniz Bilgiler Yalnış");
        }

    }
}