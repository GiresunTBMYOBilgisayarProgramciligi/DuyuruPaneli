<?php

namespace App;

use Exception;
use PDO;

class Announcement
{

    public $id;
    public $title;
    public $content;
    public $qrCode;
    public $createdDate;
    public $userId;
    public $link;

    public function __construct($id = null) {
        if (!is_null($id)) {
            $db = (new SQLiteConnection())->pdo;
            $this->id = $id;
            try {
                $u = $db->query("select * from announcement where id={$this->id}")->fetch(PDO::FETCH_OBJ);
                foreach ($u as $k => $v) {
                    $this->$k = $v;
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function fillAnnouncement($data=[]){
        foreach ($this as $k => $v) {
            if (!is_null($data[$k]))  $this->$k = $data[$k];
        }
    }
}