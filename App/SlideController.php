<?php

namespace App;

use PDO;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class SlideController
{
    /**
     * @var \PDO
     */
    private $DB;

    /**
     *
     */
    public function __construct()
    {
        $this->DB = (new SQLiteConnection())->pdo;

    }

    /**
     * @return array|false
     */
    public function getSlides()
    {
        $q = $this->DB->query("select slider.*, u.name || ' ' || u.lastName as userFullName from slider inner join user u on u.id = slider.userId", PDO::FETCH_OBJ);
        if ($q) return $q->fetchAll();
    }

    /**
     * @param $data
     * @return array|string[]
     */
    public function saveNewSlide($data)
    {
        $data = (object)$data;
        try {
            $short = new ShortlinkAndQRController();
            $data->qrCode = $data->link != "" ? $this->createQrCode($short->create_short_link($data->link)) : "";
            $fullWidth = isset($data->fullWidth) ? 1 : 0;
            $a = $this->DB->prepare("INSERT INTO slider (title, content, image, qrCode, createdDate, userId, fullWidth, link) values (:title,:content,:image,:qrCode,:createdDate,:userId,:fullWidth, :link )")->execute(
                array(
                    ":title" => $data->title,
                    ":content" => $data->content,
                    ":image" => $data->image,
                    ":qrCode" => $data->qrCode,
                    ":createdDate" => date("Y.m.d H:i:s"),
                    ":userId" => (new UsersController())->getCurrentUserId(),
                    ":fullWidth" => $fullWidth,
                    ":link" => $data->link)
            );
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
        if ($a) {
            return ['success' => 'Slide eklendi'];
        } else {
            return ['error' => "Slide eklenemedi"];
        }
    }

    /**
     * @param Slide $slide
     * @return array|true
     */
    public function updateSlide(Slide $slide)
    {
        try {
            if (!is_null($slide->link)) {
                $short = new ShortlinkAndQRController();

                $slide->qrCode = $slide->link != "" ? $this->createQrCode($short->create_short_link($slide->link)) : "";
            }
            $query = "UPDATE slider SET ";
            foreach ($slide as $k => $v) {
                if (is_null($v)) unset($slide->$k);

            }
            //var_export($slide);
            $numItem = count((array)$slide) - 1;// id sorguya kalıtmadığı için 2 çıkartıyorum
            $i = 0;
            foreach ($slide as $k => $v) {
                if ($k !== 'id') {
                    if (++$i === $numItem) $query .= $k . "='" . $v . "' "; else $query .= $k . "='" . $v . "', ";
                }
            }
            $query .= " WHERE id=" . $slide->id;
            //echo $query;
            $this->DB->query($query);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
        return true;
    }

    /**
     * todo silindiğinde fotoğrafın da silmek lazım
     * @param null $id
     * @return false|void
     */
    public function deleteSlide($id = null)
    {
        if (is_null($id)) return false;
        $this->DB->query("DELETE FROM slider WHERE id=:id")->execute(array(":id" => $id));
    }

    public function createQrCode($short_url = "")
    {
        $renderer = new ImageRenderer(new RendererStyle(50, 1), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        /** @var TYPE_NAME $writer */
        $qrCode = $writer->writeString($short_url);
        $qrCode = substr($qrCode, strpos($qrCode, "\n") + 1);
        return $qrCode;
    }
}