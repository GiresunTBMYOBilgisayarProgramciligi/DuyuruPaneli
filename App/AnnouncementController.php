<?php

namespace App;

use PDO;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class AnnouncementController
{
    public $DB;

    public function __construct() {
        $this->DB = (new SQLiteConnection())->pdo;
    }

    public function getAnnouncements() {
        return $this->DB->query("select a.*,u.name || ' ' || u.lastName as userFullName from announcement a inner join user u on u.id = a.userId", PDO::FETCH_OBJ)->fetchAll();
    }

    /**
     * database colums -> id, title, content,qrCode, createdDate, userId
     * gelen veriler içerisinde qrCode yok onun yerine link verisi geliyor.
     * @param array $arr Veriler
     */
    public function saveNewAnnouncement($arr = []) {
        $data = (object)$arr;
        $data->qrCode = $data->link != "" ? $this->createQrCode($data->link) : "";

        $this->DB->prepare("INSERT INTO announcement(title, content,qrCode, createdDate, userId) values (:title, :content, :qrCode, :createdDate, :userId)")->execute(array(":title" => $data->title, ":content" => $data->content, ":qrCode" => $data->qrCode, ":createdDate" => date("Y.m.d H:i:s"), "userId" => (new UsersControler())->getCurrentUserId()));

    }

    public function createQrCode($link = "") {
        $renderer = new ImageRenderer(new RendererStyle(400, 1), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCode = '/images/QRCodes/announcement-' . substr(md5($link), 0, 15) . '.svg';
        $qrCodeFile = Config::ROOT_PATH . 'images/QRCodes/announcement-' . substr(md5($link), 0, 15) . '.svg';
        $writer->writeFile($link, $qrCodeFile);
        return $qrCode;
    }
}