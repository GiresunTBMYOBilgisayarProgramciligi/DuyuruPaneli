<?php

namespace App;
use PDO;


class SlideController
{
    /**
     * @var \PDO
     */
    private $DB;


    public function __construct(){
    $this->DB =  (new SQLiteConnection())->pdo;

    }

    /**
     * @return array|false
     */
    public function getSlides()
    {
        return $this->DB->query(
            "Select * from slider",PDO::FETCH_OBJ)->fetchAll();
    }

    public function saveNewSlide($par){
        $this->DB->prepare("INSERT INTO slider( title, content,image, qrCode, createdDate, userId) values (:title,:content,:image,:qrCode,:createdDate,:userId)")->execute($par);
    }
}