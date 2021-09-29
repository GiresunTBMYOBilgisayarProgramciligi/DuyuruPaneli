<?php
namespace App\Admin;

use App\SlideController;

$slideControler=new SlideController();
$slides= $slideControler->getSlides();
?>
<div class="tab-pane fade show active" id="slideTabContent" role="tabpanel" aria-labelledby="slide-tab">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="card-title">Slide yönetimi</h4>
                        <p class="card-description">Ekranın ortsında bulunan büyük resim duyuruları ayarları</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-icon-text">
                        <i class="ti-plus btn-icon-prepend"></i>
                        Yeni ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-3">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>
                                    #
                                </th>
                                <th>
                                    Başlık
                                </th>
                                <th>
                                    İçerik
                                </th>
                                <th>
                                    Görsel
                                </th>
                                <th>
                                    QR kod
                                </th>
                                <th>
                                    Ekleyen
                                </th>
                                <th>
                                    Eklenme Tarihi
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $sayac=1;
                                foreach ($slides as $slide){
                            ?>
                                    <tr>
                                        <td>
                                            <?= $sayac?>
                                        </td>
                                        <td>
                                            <?= $slide->title?>
                                        </td>
                                        <td>
                                            <?= $slide->content?>
                                        </td>
                                        <td>
                                            <?= $slide->image?>
                                        </td>
                                        <td>
                                            <?= $slide->qrCode?>
                                        </td>
                                        <td>
                                            <?= $slide->userFullName?>
                                        </td>
                                        <td>
                                            <?= $slide->createdDate?>
                                        </td>
                                    </tr>
                            <?php
                                    $sayac++;
                                }
                            ?>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>