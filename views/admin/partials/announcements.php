<?php
declare(strict_types=1);
?>
<div class="tab-pane fade" id="duyuruTabContent" role="tabpanel" aria-labelledby="duyuru-tab">
    <div class="admin-panel-card">
        <div class="panel-header-row">
            <div>
                <h3 class="panel-title">📢 Kayan Duyuru Yönetimi</h3>
                <p class="panel-subtitle">Ekranın alt bandında dönen metin duyuruları ve QR bağlantıları</p>
            </div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal" style="border-radius: var(--radius-md); padding: 8px 16px; font-weight: 600;">
                <span>➕</span> Yeni Duyuru Ekle
            </button>
        </div>

        <div class="table-responsive">
            <table id="announcmentTable" class="table admin-table align-middle">
                <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 140px;">Kategori / Ön Ek</th>
                    <th>Duyuru Metni</th>
                    <th style="width: 120px;">QR Kod</th>
                    <th style="width: 120px;">Okutulma</th>
                    <th>Ekleyen</th>
                    <th>Tarih</th>
                    <th class="text-center" style="width: 100px;">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <!-- AJAX ile doldurulur -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Yeni Duyuru -->
<div class="modal fade" id="newAnnouncementModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newAnnouncementLabel">➕ Yeni Kayan Duyuru Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="newAnnouncementForm" id="newAnnouncementForm" method="post">
                    <div class="mb-3">
                        <label for="announcement_title" class="form-label">Kategori / Ön Ek (İsteğe Bağlı)</label>
                        <input type="text" class="form-control" id="announcement_title" name="title" placeholder="Örn: ÖNEMLİ, DERS KAYITLARI, ETKİNLİK (Boş bırakılırsa tarih yazılır)">
                    </div>
                    <div class="mb-3">
                        <label for="announcement_content" class="form-label">Duyuru Metni *</label>
                        <textarea required class="form-control" id="announcement_content" name="content" rows="3" placeholder="Öğrencilere panoda gösterilecek duyuru metni"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="announcement_link" class="form-label">İlgili Web Bağlantısı (QR Kod İçin)</label>
                        <input type="url" class="form-control" id="announcement_link" name="link" placeholder="https://ornek.edu.tr/duyuru-detay">
                        <div class="form-text text-muted">Link girildiğinde duyurunun yanında taranabilir SVG QR kod ve okutulma istatistikleri üretilir.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newAnnouncementForm" class="btn btn-primary fw-bold px-4">Kaydet ve Yayınla</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Duyuru Güncelle -->
<div class="modal fade" id="updateAnnouncementModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateAnnouncementLabel">✏️ Duyuruyu Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="updateAnnouncementForm" id="updateAnnouncementForm" method="post">
                    <input type="hidden" id="update_announcement_id" name="id" value="">
                    <div class="mb-3">
                        <label for="update_announcement_title" class="form-label">Kategori / Ön Ek</label>
                        <input type="text" class="form-control" id="update_announcement_title" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="update_announcement_content" class="form-label">Duyuru Metni *</label>
                        <textarea required class="form-control" id="update_announcement_content" name="content" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="update_announcement_link" class="form-label">İlgili Web Bağlantısı (URL)</label>
                        <input type="url" class="form-control" id="update_announcement_link" name="link">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateAnnouncementForm" class="btn btn-primary fw-bold px-4">Değişiklikleri Güncelle</button>
            </div>
        </div>
    </div>
</div>