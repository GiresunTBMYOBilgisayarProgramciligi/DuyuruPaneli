<?php
namespace App\Admin;

use App\SlideController;

$slideControler = new SlideController();
$slides = $slideControler->getSlides();
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
                <form name="newSlideForm" id="newSlideForm" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Başlık</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Başlık">
                            </div>
                            <div class="form-group">
                                <label for="content">İçerik</label>
                                <input type="text" class="form-control" id="content" name="content"
                                       placeholder="İçerik">
                            </div>
                            <div class="form-group">
                                <label for="link">Link</label>
                                <input type="url" class="form-control" id="link" name="link" placeholder="Duyuru linki">
                            </div>

                        </div>
                        <div class="col-md-4">
                            <input required type="file" name="image" id="image" class="dropify" data-show-remove="true"/>

                            <div class="form-check form-check-flat form-check-primary">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="fullWidth" name="fullWidth">
                                    Tam Genişlik
                                    <i class="input-helper"></i></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newSlideForm" class="btn btn-primary">Ekle</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade " id="updateSlideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Slide Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="updateSlideForm" id="updateSlideForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="id" name="id" value="">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Başlık</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Başlık" value="">
                            </div>
                            <div class="form-group">
                                <label for="content">İçerik</label>
                                <input type="text" class="form-control" id="content" name="content"
                                       placeholder="İçerik">
                            </div>
                            <div class="form-group">
                                <label for="link">Link</label>
                                <input type="url" class="form-control" id="link" name="link" placeholder="Duyuru linki">
                            </div>

                        </div>
                        <div class="col-md-4">
                            <input type="file" name="image" id="image" class="dropify" data-show-remove="true"/>

                            <div class="form-check form-check-flat form-check-primary">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="fullWidth" name="fullWidth">
                                    Tam Genişlik
                                    <i class="input-helper"></i></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateSlideForm" class="btn btn-primary">Güncelle</button>
            </div>
        </div>
    </div>
</div>