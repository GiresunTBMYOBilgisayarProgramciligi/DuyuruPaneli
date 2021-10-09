<?php

namespace App;

use Exception;
use PDO;

class User
{
    public $id;
    public $userName;
    public $password;
    public $mail;
    public $name;
    public $lastName;
    public $createdDate;
    private $db;

    public function __construct($id) {
        $this->db = (new SQLiteConnection())->pdo;
        $this->id=$id;
        try {
            $u = $this->db->query("select * from user where id={$this->id}")->fetch(PDO::FETCH_OBJ);
            foreach ($u as $k=>$v){
                $this->$k=$v;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getFullName(){
        return $this->name." ".$this->lastName;
    }
}