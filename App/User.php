<?php

namespace App;

use Exception;
use PDO;

class User
{
    public $id = null;
    public $userName = null;
    public $password = null;
    public $mail = null;
    public $name = null;
    public $lastName = null;
    public $createdDate = null;

    public function __construct($id = null) {
        $db = (new SQLiteConnection())->pdo;
        if (!is_null($id)) {
            $this->id = $id;
            try {
                $u = $db->query("select * from user where id={$this->id}");
                if ($u) {
                    $u = $u->fetch(PDO::FETCH_OBJ);
                    foreach ($u as $k => $v) {
                        $this->$k = $v;
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

    }

    public function fillUser($data=[]){
        foreach ($this as $k => $v) {
            if (!is_null($data[$k]))  $this->$k = $data[$k];
        }
    }

    public function getFullName() {
        return $this->name . " " . $this->lastName;
    }
}