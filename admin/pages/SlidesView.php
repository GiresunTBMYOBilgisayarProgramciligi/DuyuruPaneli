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
                    <button type="button" class="btn btn-success btn-icon-text" data-bs-toggle="modal" data-bs-target="#newSlideModal">
                        <i class="ti-plus btn-icon-prepend"></i>
                        Yeni ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-3">
                        <table id="slidesTable" class="table table-bordered">
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


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade " id="newSlideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Yeni Slide Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="newSlideForm" id="newSlideForm" method="post">

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-primary">Ekle</button>
            </div>
        </div>
    </div>
</div>