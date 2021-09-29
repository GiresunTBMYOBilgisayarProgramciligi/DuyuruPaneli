<?php
namespace App\Admin;

use App\AnnouncementController;

$announcementController = new AnnouncementController();
$announcements=$announcementController->getAnnouncements();
?>
<div class="tab-pane fade col-md-12 grid-margin stretch-card" id="duyuruTabContent" role="tabpanel" aria-labelledby="duyuru-tab">
    <div class="card">
        <div class="card-body">
            <div class="d-sm-flex justify-content-between align-items-start">
                <div>
                    <h4 class="card-title card-tit-dash">Duyuru Yönetimi</h4>
                    <h5 class="card-subtitle card-subtitle-dash">Ekranın en alt kısmında kayan duyuruların yönetimi</h5>
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
                        foreach ($announcements as $announcement){
                            ?>
                            <tr>
                                <td>
                                    <?= $sayac?>
                                </td>
                                <td>
                                    <?= $announcement->title?>
                                </td>
                                <td>
                                    <?= $announcement->content?>
                                </td>
                                <td>
                                    <?= $announcement->image?>
                                </td>
                                <td>
                                    <?= $announcement->qrCode?>
                                </td>
                                <td>
                                    <?= $announcement->userFullName?>
                                </td>
                                <td>
                                    <?= $announcement->createdDate?>
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