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
                    <th style="width: 75px;">Sıra</th>
                    <th style="width: 140px;">Kategori / Ön Ek</th>
                    <th>Duyuru Metni</th>
                    <th style="width: 110px;">Durum</th>
                    <th style="width: 170px;">Yayın Takvimi</th>
                    <th style="width: 110px;">QR Kod</th>
                    <th style="width: 110px;">Okutulma</th>
                    <th>Ekleyen</th>
                    <th>Tarih</th>
                    <th class="text-center" style="width: 130px;">İşlemler</th>
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
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="announcement_orderNumber" class="form-label">Sıralama / Öncelik</label>
                            <input type="number" class="form-control" id="announcement_orderNumber" name="orderNumber" value="0" min="0" placeholder="0: Otomatik">
                            <div class="form-text text-muted">Küçük numaralı duyuru önce gösterilir. 0: En son eklenen ilk akar.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="announcement_isActive" class="form-label">Yayın Durumu</label>
                            <select class="form-select" id="announcement_isActive" name="isActive">
                                <option value="1" selected>🟢 Yayında (Aktif)</option>
                                <option value="0">⏸️ Duraklatıldı (Gizli)</option>
                            </select>
                            <div class="form-text text-muted">Duyuru silinmeden geçici olarak yayından kaldırılabilir.</div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="announcement_startsAt" class="form-label">Yayın Başlangıç Tarihi</label>
                            <input type="datetime-local" class="form-control" id="announcement_startsAt" name="startsAt">
                            <div class="form-text text-muted">Boşsa hemen yayına girer. İleri tarih seçilirse otomatik yayınlanır.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="announcement_expiresAt" class="form-label">Yayın Bitiş Tarihi</label>
                            <input type="datetime-local" class="form-control" id="announcement_expiresAt" name="expiresAt">
                            <div class="form-text text-muted">Ayarlandığı tarih geldiğinde duyuru otomatik durdurulur. Boşsa süresiz kalır.</div>
                        </div>
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
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="update_announcement_orderNumber" class="form-label">Sıralama / Öncelik</label>
                            <input type="number" class="form-control" id="update_announcement_orderNumber" name="orderNumber" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="update_announcement_isActive" class="form-label">Yayın Durumu</label>
                            <select class="form-select" id="update_announcement_isActive" name="isActive">
                                <option value="1">🟢 Yayında (Aktif)</option>
                                <option value="0">⏸️ Duraklatıldı (Gizli)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="update_announcement_startsAt" class="form-label">Yayın Başlangıç Tarihi</label>
                            <input type="datetime-local" class="form-control" id="update_announcement_startsAt" name="startsAt">
                            <div class="form-text text-muted">Boşsa hemen yayına girer.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="update_announcement_expiresAt" class="form-label">Yayın Bitiş Tarihi</label>
                            <input type="datetime-local" class="form-control" id="update_announcement_expiresAt" name="expiresAt">
                            <div class="form-text text-muted">Tarih dolduğunda otomatik durdurulur. Boşsa süresiz kalır.</div>
                        </div>
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