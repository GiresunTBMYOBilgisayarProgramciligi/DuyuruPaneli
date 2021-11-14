<?php

namespace App;

use GabrielKaputa\Bitly\Bitly;
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
        $q = $this->DB->query("select a.*,u.name || ' ' || u.lastName as userFullName from announcement a inner join user u on u.id = a.userId", PDO::FETCH_OBJ);
        if ($q) return $q->fetchAll();
    }

    /**
     * database colums -> id, title, content,qrCode, createdDate, userId
     * gelen veriler içerisinde qrCode yok onun yerine link verisi geliyor.
     * @param array $arr Veriler
     */
    public function saveNewAnnouncement($arr = []) {
        $data = (object)$arr;
        $data->qrCode = $data->link != "" ? $this->createQrCode($data->link) : "";

        $q= $this->DB->prepare("INSERT INTO announcement(title, content,qrCode, createdDate, userId, link) values (:title, :content, :qrCode, :createdDate, :userId, :link)")->execute(array(":title" => $data->title, ":content" => $data->content, ":qrCode" => $data->qrCode, ":createdDate" => date("Y.m.d H:i:s"), ":userId" => (new UsersController())->getCurrentUserId(), ":link" => $data->link));
        if ($q) {
            return ['success' => 'Slide eklendi'];
        } else {
            return ['error' => "Slide eklenemedi"];
        }
    }

    /**
     * todo varsq qr code silinmeli
     * @param null $id
     * @return false|void
     */
    public function deleteAnnouncement($id = null) {
        if (is_null($id)) return false;
        $this->DB->query("DELETE FROM announcement WHERE id=:id")->execute(array(":id" => $id));
    }

    public function createQrCode($link = "") {
        $renderer = new ImageRenderer(new RendererStyle(400, 1), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCode = '/images/QRCodes/announcement-' . substr(md5($link), 0, 15) . '.svg';
        $qrCodeFile = Config::ROOT_PATH . 'images/QRCodes/announcement-' . substr(md5($link), 0, 15) . '.svg';
        $bitly = Bitly::withGenericAccessToken("34fd0f467aa181b58e72330dacf76726fe7c118f");
        $short_url = $bitly->shortenUrl($link);
        $writer->writeFile($short_url, $qrCodeFile);
        return $qrCode;
    }

    public function updateAnnouncement(Announcement $announcement) {
        if (!is_null($announcement->link)) {
            if ($announcement->link == "") {
                unlink(Config::ROOT_PATH . substr($announcement->qrCode, 1));
                $announcement->qrCode = "";
            } else {
                $announcement->qrCode = $this->createQrCode($announcement->link);
            }
        }

        foreach ($announcement as $k => $v) {
            if (is_null($v)) unset($announcement->$k);// Remove properties which not updated
        }
        $numItem = count((array)$announcement) - 1;// id sorguya kalıtmadığı için 2 çıkartıyorum
        $i = 0;
        $query = "UPDATE announcement SET ";
        foreach ($announcement as $k => $v) {
            if ($k !== 'id') {
                if (++$i === $numItem) $query .= $k . "='" . $v . "' "; else $query .= $k . "='" . $v . "', ";
            }
        }
        $query .= " WHERE id=" . $announcement->id;
        $this->DB->query($query);
    }
}