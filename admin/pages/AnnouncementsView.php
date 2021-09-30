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
                <button type="button" class="btn btn-success btn-icon-text" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
                    <i class="ti-plus btn-icon-prepend"></i>
                    Yeni ekle
                </button>
            </div>
            <div>
                <div class="table-responsive pt-3">
                    <table id="announcmentTable" class="table table-bordered">
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
                                QR kod
                            </th>
                            <th>
                                Ekleyen
                            </th>
                            <th>
                                Eklenme Tarihi
                            </th>
                            <th>
                                İşlemler
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade " id="newAnnouncementModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Yeni Duyuru Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="newAnnouncementForm" id="newAnnouncementForm" method="post" >
                    <div class="form-group">
                        <label for="title">Başlık</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Başlık">
                    </div>
                    <div class="form-group">
                        <label for="content">İçerik</label>
                        <input required type="text" class="form-control" id="content" name="content" placeholder="İçerik">
                    </div>
                    <div class="form-group">
                        <label for="link">Link</label>
                        <input type="url" class="form-control" id="link" name="link" placeholder="Duyuru linki">
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newAnnouncementForm" class="btn btn-primary" >Ekle</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

</script>