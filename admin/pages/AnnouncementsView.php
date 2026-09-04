<?php
declare(strict_types=1);

namespace App\Admin;
?>
<div class="tab-pane fade col-md-12 grid-margin stretch-card" id="duyuruTabContent" role="tabpanel" aria-labelledby="duyuru-tab">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="card-title fw-bold text-dark">Kayan Duyuru Yönetimi</h4>
                    <h5 class="card-subtitle text-muted mb-0">Ekranın en alt bandında kayan metin duyuruları</h5>
                </div>
                <button type="button" class="btn btn-primary btn-icon-text" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
                    <i class="ti-plus btn-icon-prepend"></i> Yeni Duyuru Ekle
                </button>
            </div>
            <div>
                <div class="table-responsive pt-2">
                    <table id="announcmentTable" class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Başlık / Ön Ek</th>
                            <th>Duyuru Metni</th>
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

<!-- Modal: Yeni Duyuru -->
<div class="modal fade" id="newAnnouncementModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newAnnouncementLabel">Yeni Kayan Duyuru Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="newAnnouncementForm" id="newAnnouncementForm" method="post">
                    <div class="form-group mb-3">
                        <label for="announcement_title" class="form-label fw-semibold">Başlık / Ön Etiket (İsteğe Bağlı)</label>
                        <input type="text" class="form-control" id="announcement_title" name="title" placeholder="Örn: ÖNEMLİ, SINAV TAKVİMİ (Boş bırakılırsa tarih yazılır)">
                    </div>
                    <div class="form-group mb-3">
                        <label for="announcement_content" class="form-label fw-semibold">Duyuru Metni *</label>
                        <textarea required class="form-control" id="announcement_content" name="content" rows="3" placeholder="Öğrencilere gösterilecek kayan duyuru metni"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="announcement_link" class="form-label fw-semibold">İlgili Bağlantı (QR Kod İçin)</label>
                        <input type="url" class="form-control" id="announcement_link" name="link" placeholder="https://ornek.edu.tr/duyuru-detay">
                        <small class="text-muted">Link girildiğinde duyurunun yanında taranabilir SVG QR kod gösterilir.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newAnnouncementForm" class="btn btn-primary">Kaydet ve Yayınla</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Duyuru Güncelle -->
<div class="modal fade" id="updateAnnouncementModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="updateAnnouncementLabel">Duyuruyu Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="updateAnnouncementForm" id="updateAnnouncementForm" method="post">
                    <input type="hidden" id="update_announcement_id" name="id" value="">
                    <div class="form-group mb-3">
                        <label for="update_announcement_title" class="form-label fw-semibold">Başlık / Ön Etiket</label>
                        <input type="text" class="form-control" id="update_announcement_title" name="title">
                    </div>
                    <div class="form-group mb-3">
                        <label for="update_announcement_content" class="form-label fw-semibold">Duyuru Metni *</label>
                        <textarea required class="form-control" id="update_announcement_content" name="content" rows="3"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="update_announcement_link" class="form-label fw-semibold">İlgili Bağlantı (URL)</label>
                        <input type="url" class="form-control" id="update_announcement_link" name="link">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateAnnouncementForm" class="btn btn-primary">Değişiklikleri Güncelle</button>
            </div>
        </div>
    </div>
</div>