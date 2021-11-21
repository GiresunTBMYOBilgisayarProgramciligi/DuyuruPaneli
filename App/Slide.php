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
    public $link;

    public function __construct($id = null) {
        if (!is_null($id)) {
            $db = (new SQLiteConnection())->pdo;
            $this->id = $id;
            try {
                $u = $db->query("select * from slider where id={$this->id}")->fetch(PDO::FETCH_OBJ);
                foreach ($u as $k => $v) {
                    $this->$k = $v;
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function fillSlide($data=[]){
        foreach ($this as $k => $v) {
            if (!is_null($data[$k]))  $this->$k = $data[$k];
        }
    }
}