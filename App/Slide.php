<?php

namespace App;

use Exception;
use PDO;

class Slide
{

    public $id;
    public $title;
    public $content;
    public $image;
    public $qrCode;
    public $createdDate;
    public $userId;
    public $fullWidth;
    public $db;
    public $link;

    public function __construct($id = null) {
        if (!is_null($id)) {
            $this->db = (new SQLiteConnection())->pdo;
            $this->id = $id;
            try {
                $u = $this->db->query("select * from slider where id={$this->id}")->fetch(PDO::FETCH_OBJ);
                foreach ($u as $k => $v) {
                    $this->$k = $v;
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}