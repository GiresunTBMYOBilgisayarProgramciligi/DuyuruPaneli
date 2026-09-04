<?php
declare(strict_types=1);

namespace App\Admin;
?>
<div class="tab-pane fade show active" id="slideTabContent" role="tabpanel" aria-labelledby="slide-tab">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title fw-bold text-dark">Slayt ve Afiş Yönetimi</h4>
                        <p class="card-description text-muted mb-0">Ana panoda tam ekran veya ortada gösterilen görsel duyurular</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-icon-text" data-bs-toggle="modal" data-bs-target="#newSlideModal">
                        <i class="ti-plus btn-icon-prepend"></i> Yeni Afiş Ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-2">
                        <table id="slidesTable" class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Başlık</th>
                                <th>İçerik</th>
                                <th>Görsel</th>
                                <th>QR Kod</th>
                                <th>Okutulma</th>
                                <th>Ekleyen</th>
                                <th>Tarih</th>
                                <th class="text-center">İşlemler</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Yeni Slayt -->
<div class="modal fade" id="newSlideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newSlideLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newSlideLabel">Yeni Afiş / Slayt Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="newSlideForm" id="newSlideForm" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label for="slide_title" class="form-label fw-semibold">Afiş Başlığı</label>
                                <input type="text" class="form-control" id="slide_title" name="title" placeholder="Örn: 2026 Bahar Şenliği">
                            </div>
                            <div class="form-group mb-3">
                                <label for="slide_content" class="form-label fw-semibold">Açıklama / İçerik</label>
                                <textarea class="form-control" id="slide_content" name="content" rows="3" placeholder="Afiş hakkında kısa açıklama"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="slide_link" class="form-label fw-semibold">Detay Bağlantısı (QR Kod İçin)</label>
                                <input type="url" class="form-control" id="slide_link" name="link" placeholder="https://ornek.edu.tr/etkinlik">
                                <small class="text-muted">Link girildiğinde öğrenciler için otomatik taranabilir QR kod ve istatistik üretilir.</small>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="fullWidth" name="fullWidth" value="1">
                                <label class="form-check-label fw-semibold" for="fullWidth">
                                    Tam Genişlik (Görsel tüm ekranı kaplasın)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Afiş Görseli *</label>
                            <input required type="file" name="image" id="slide_image" class="dropify" data-show-remove="true" accept="image/*"/>
                            <small class="text-muted d-block mt-1">Önerilen oran: 16:9 yatay ekran (JPG, PNG, WEBP, Maks. 10MB)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newSlideForm" class="btn btn-primary">Kaydet ve Yayınla</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Slayt Güncelle -->
<div class="modal fade" id="updateSlideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateSlideLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="updateSlideLabel">Afiş / Slayt Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="updateSlideForm" id="updateSlideForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="update_slide_id" name="id" value="">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label for="update_slide_title" class="form-label fw-semibold">Afiş Başlığı</label>
                                <input type="text" class="form-control" id="update_slide_title" name="title">
                            </div>
                            <div class="form-group mb-3">
                                <label for="update_slide_content" class="form-label fw-semibold">Açıklama / İçerik</label>
                                <textarea class="form-control" id="update_slide_content" name="content" rows="3"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="update_slide_link" class="form-label fw-semibold">Detay Bağlantısı (URL)</label>
                                <input type="url" class="form-control" id="update_slide_link" name="link">
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="update_fullWidth" name="fullWidth" value="1">
                                <label class="form-check-label fw-semibold" for="update_fullWidth">
                                    Tam Genişlik
                                </label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Görseli Değiştir (İsteğe Bağlı)</label>
                            <input type="file" name="image" id="update_slide_image" class="dropify" data-show-remove="true" accept="image/*"/>
                            <small class="text-muted d-block mt-1">Yalnızca afiş görselini değiştirmek istiyorsanız yeni dosya seçiniz.</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateSlideForm" class="btn btn-primary">Değişiklikleri Güncelle</button>
            </div>
        </div>
    </div>
</div>