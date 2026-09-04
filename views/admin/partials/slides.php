<?php
declare(strict_types=1);
?>
<div class="tab-pane fade show active" id="slideTabContent" role="tabpanel" aria-labelledby="slide-tab">
    <div class="admin-panel-card">
        <div class="panel-header-row">
            <div>
                <h3 class="panel-title">🖼️ Afiş ve Slayt Yönetimi</h3>
                <p class="panel-subtitle">Kampüs TV ekranında tam ekran veya ortalanmış olarak gösterilen görsel duyurular</p>
            </div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newSlideModal" style="border-radius: var(--radius-md); padding: 8px 16px; font-weight: 600;">
                <span>➕</span> Yeni Afiş Ekle
            </button>
        </div>

        <div class="table-responsive">
            <table id="slidesTable" class="table admin-table align-middle">
                <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 90px;">Görsel</th>
                    <th>Afiş Başlığı & Açıklama</th>
                    <th style="width: 130px;">QR Kod</th>
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

<!-- Modal: Yeni Slayt -->
<div class="modal fade" id="newSlideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newSlideLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSlideLabel">➕ Yeni Afiş / Slayt Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="newSlideForm" id="newSlideForm" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="slide_title" class="form-label">Afiş Başlığı</label>
                                <input type="text" class="form-control" id="slide_title" name="title" placeholder="Örn: 2026 Bahar Şenliği">
                            </div>
                            <div class="mb-3">
                                <label for="slide_content" class="form-label">Açıklama / Alt Metin</label>
                                <textarea class="form-control" id="slide_content" name="content" rows="3" placeholder="Afiş hakkında kısa bilgilendirme"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="slide_link" class="form-label">Detay Bağlantısı (QR Kod İçin)</label>
                                <input type="url" class="form-control" id="slide_link" name="link" placeholder="https://ornek.edu.tr/etkinlik">
                                <div class="form-text text-muted">Link girildiğinde afiş için otomatik taranabilir QR kod ve okutulma analitiği oluşturulur.</div>
                            </div>
                            <div class="form-check mt-3 p-2 bg-light rounded-3 border">
                                <input class="form-check-input ms-1" type="checkbox" id="fullWidth" name="fullWidth" value="1">
                                <label class="form-check-label fw-bold ms-2" for="fullWidth">
                                    Tam Genişlik (Görsel ekranı kaplasın)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Afiş Görseli *</label>
                            <input required type="file" name="image" id="slide_image" class="form-control" accept="image/*"/>
                            <div class="form-text text-muted mb-2">JPG, PNG, WEBP (Önerilen: 16:9 yatay ekran, Maks. 10MB)</div>
                            
                            <!-- Canlı Önizleme Kutusu -->
                            <div class="file-upload-preview-box" id="newSlidePreviewBox">
                                <img src="" alt="Önizleme" id="newSlidePreviewImg">
                                <div class="small text-muted mt-1" id="newSlideFileInfo"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newSlideForm" class="btn btn-primary fw-bold px-4">Kaydet ve Yayınla</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Slayt Güncelle -->
<div class="modal fade" id="updateSlideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateSlideLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateSlideLabel">✏️ Afiş / Slayt Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="updateSlideForm" id="updateSlideForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="update_slide_id" name="id" value="">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="update_slide_title" class="form-label">Afiş Başlığı</label>
                                <input type="text" class="form-control" id="update_slide_title" name="title">
                            </div>
                            <div class="mb-3">
                                <label for="update_slide_content" class="form-label">Açıklama / Alt Metin</label>
                                <textarea class="form-control" id="update_slide_content" name="content" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="update_slide_link" class="form-label">Detay Bağlantısı (URL)</label>
                                <input type="url" class="form-control" id="update_slide_link" name="link">
                            </div>
                            <div class="form-check mt-3 p-2 bg-light rounded-3 border">
                                <input class="form-check-input ms-1" type="checkbox" id="update_fullWidth" name="fullWidth" value="1">
                                <label class="form-check-label fw-bold ms-2" for="update_fullWidth">
                                    Tam Genişlik
                                </label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Görseli Değiştir (İsteğe Bağlı)</label>
                            <input type="file" name="image" id="update_slide_image" class="form-control" accept="image/*"/>
                            <div class="form-text text-muted mb-2">Yalnızca görseli değiştirmek istiyorsanız yeni dosya seçin.</div>
                            
                            <!-- Canlı Önizleme Kutusu -->
                            <div class="file-upload-preview-box" id="updateSlidePreviewBox">
                                <img src="" alt="Yeni Görsel Önizleme" id="updateSlidePreviewImg">
                                <div class="small text-muted mt-1" id="updateSlideFileInfo"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateSlideForm" class="btn btn-primary fw-bold px-4">Değişiklikleri Güncelle</button>
            </div>
        </div>
    </div>
</div>