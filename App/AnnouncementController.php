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

    public function __construct()
    {
        $this->DB = (new SQLiteConnection())->pdo;
    }

    public function getAnnouncements()
    {
        $q = $this->DB->query("select a.*,u.name || ' ' || u.lastName as userFullName from announcement a inner join user u on u.id = a.userId", PDO::FETCH_OBJ);
        if ($q) return $q->fetchAll();
    }

    /**
     * database colums -> id, title, content,qrCode, createdDate, userId
     * gelen veriler içerisinde qrCode yok onun yerine link verisi geliyor.
     * @param array $arr Veriler
     */
    public function saveNewAnnouncement($arr = [])
    {
        try {
            $data = (object)$arr;
            $short = new ShortlinkAndQRController();
            $data->qrCode = $data->link != "" ? $this->createQrCode($short->create_short_link($data->link)) : "";

            $q = $this->DB->prepare("INSERT INTO announcement(title, content,qrCode, createdDate, userId, link) values (:title, :content, :qrCode, :createdDate, :userId, :link)")->execute(array(":title" => $data->title, ":content" => $data->content, ":qrCode" => $data->qrCode, ":createdDate" => date("Y.m.d H:i:s"), ":userId" => (new UsersController())->getCurrentUserId(), ":link" => $data->link));
            if ($q) {
                return ['success' => 'Duyuru eklendi'];
            } else {
                return ['error' => "Duyuru eklenemedi"];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

    /**
     * todo varsq qr code silinmeli
     * @param $id
     * @return array|bool
     */
    public function deleteAnnouncement($id = null)
    {
        try {
            if (is_null($id)) return false;
            $this->DB->query("DELETE FROM announcement WHERE id=:id")->execute(array(":id" => $id));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
        return ['success' => 'Duyuru Silindi'];
    }

    public function updateAnnouncement(Announcement $announcement)
    {
        try {
            if (!is_null($announcement->link)) {
                if ($announcement->link == "") {
                    $announcement->qrCode = "";
                } else {
                    $short = new ShortlinkAndQRController();
                    $announcement->qrCode = $announcement->link != "" ? $this->createQrCode($short->create_short_link($announcement->link)) : "";
                }
            }

            foreach ($announcement as $k => $v) {
                if (is_null($v)) unset($announcement->$k);// Remove properties which not updated
            }
            $numItem = count((array)$announcement) - 1;// id sorguya kalıtmadığı için 1 çıkartıyorum
            $i = 0;
            $query = "UPDATE announcement SET ";
            foreach ($announcement as $k => $v) {
                if ($k !== 'id') {
                    if (++$i === $numItem) $query .= $k . "='" . $v . "' "; else $query .= $k . "='" . $v . "', ";
                }
            }
            $query .= " WHERE id=" . $announcement->id;

            $q = $this->DB->query($query);
            if ($q) {
                return ['success' => 'Duyuru güncellendi'];
            } else {
                return ['error' => "Duyuru güncellenemedi"];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public function createQrCode($short_url = "") {
        $renderer = new ImageRenderer(new RendererStyle(50, 1), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        /** @var TYPE_NAME $writer */
        $qrCode= $writer->writeString($short_url);
        $qrCode = substr($qrCode, strpos($qrCode, "\n") + 1);
        return $qrCode;
    }
}