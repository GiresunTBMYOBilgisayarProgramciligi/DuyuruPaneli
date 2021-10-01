<?php

namespace App;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use GabrielKaputa\Bitly\Bitly;
use PDO;


class SlideController
{
    /**
     * @var \PDO
     */
    private $DB;


    public function __construct() {
        $this->DB = (new SQLiteConnection())->pdo;

    }

    /**
     * @return array|false
     */
    public function getSlides() {
        return $this->DB->query("select slider.*, u.name || ' ' || u.lastName as userFullName from slider inner join user u on u.id = slider.userId", PDO::FETCH_OBJ)->fetchAll();
    }

    public function saveNewSlide($data) {
        $data = (object)$data;

        $data->qrCode = $data->link != "" ? $this->createQrCode($data->link) : "";
        $fullWidth = isset($data->fullWidth) ? 1 : 0;
        $a=$this->DB->prepare("INSERT INTO slider (title, content, image, qrCode, createdDate, userId, fullWidth) values (:title,:content,:image,:qrCode,:createdDate,:userId,:fullWidth )")->execute(
            array(
                ":title" => $data->title,
                ":content" => $data->content,
                ":image" => $data->image,
                ":qrCode" => $data->qrCode,
                ":createdDate" => date("Y.m.d H:i:s"),
                ":userId" => (new UsersControler())->getCurrentUserId(),
                ":fullWidth" => $fullWidth
            )
        );
    }

    /**
     * todo silindiğinde fotoğrafın da silmek lazım
     * @param null $id
     * @return false|void
     */
    public function deleteSlide($id=null){
        if(is_null($id)) return false;
        $this->DB->query("DELETE FROM slider WHERE id=:id")->execute(array(":id" => $id));
    }

    public function createQrCode($link = "") {
        $renderer = new ImageRenderer(new RendererStyle(400, 1), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCode = '/images/QRCodes/slide-' . substr(md5($link), 0, 15) . '.svg';
        $qrCodeFile = Config::ROOT_PATH . 'images/QRCodes/slide-' . substr(md5($link), 0, 15) . '.svg';
        $bitly = Bitly::withGenericAccessToken("34fd0f467aa181b58e72330dacf76726fe7c118f");
        $short_url = $bitly->shortenUrl($link);
        $writer->writeFile($short_url, $qrCodeFile);
        return $qrCode;
    }
}