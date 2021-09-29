<?php

namespace App;

use PDO;

class AnnouncementController
{
    public $DB;

    public function __construct() {
        $this->DB= (new SQLiteConnection())->pdo;
    }

    public function getAnnouncements(){
        return $this->DB->query(
            "select a.*,u.name || ' ' || u.lastName as userFullName from announcement a inner join user u on u.id = a.userId",PDO::FETCH_OBJ)->fetchAll();
    }
}