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
    public $db;

    public function __construct($id = null) {
        $this->db = (new SQLiteConnection())->pdo;
        if (!is_null($id)) {
            $this->id = $id;
            try {
                $u = $this->db->query("select * from user where id={$this->id}")->fetch(PDO::FETCH_OBJ);
                foreach ($u as $k => $v) {
                    $this->$k = $v;
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

    }

    public function getFullName() {
        return $this->name . " " . $this->lastName;
    }
}