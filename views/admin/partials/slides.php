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
                    <th style="width: 75px;">Sıra</th>
                    <th style="width: 90px;">Görsel</th>
                    <th>Afiş Başlığı & Açıklama</th>
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
                                <label for="slide_link" class="form-label fw-semibold">Detay Bağlantısı / YouTube Video Adresi & QR Kod Konumu</label>
                                <div class="input-group">
                                    <input type="url" class="form-control" id="slide_link" name="link" placeholder="https://www.youtube.com/watch?v=... veya https://ornek.edu.tr">
                                    <select class="form-select" id="slide_qrPosition" name="qrPosition" style="max-width: 205px;" title="QR Kodun Kiosk Ekranındaki Konumu">
                                        <option value="bottom-right" selected>↘️ Sağ Alt (Önerilen)</option>
                                        <option value="bottom-left">↙️ Sol Alt</option>
                                        <option value="top-right">↗️ Sağ Üst</option>
                                        <option value="top-left">↖️ Sol Üst</option>
                                        <option value="none">🚫 Gösterme (Gizle)</option>
                                    </select>
                                </div>
                                <div class="form-text text-muted">Afiş linkine <strong>YouTube video adresi</strong> girilirse afiş olarak video oynatılır ve video bitene kadar geçiş duraklatılır (Görsel yüklenmezse video kapağı otomatik alınır). Diğer web adreslerinde otomatik QR kod üretilir.</div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="slide_orderNumber" class="form-label">Sıralama / Öncelik</label>
                                    <input type="number" class="form-control" id="slide_orderNumber" name="orderNumber" value="0" min="0" placeholder="0: Otomatik">
                                    <div class="form-text text-muted">Küçük numaralı afiş önce gösterilir. 0: En son eklenen ilk gösterilir.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="slide_isActive" class="form-label">Yayın Durumu</label>
                                    <select class="form-select" id="slide_isActive" name="isActive">
                                        <option value="1" selected>🟢 Yayında (Aktif)</option>
                                        <option value="0">⏸️ Duraklatıldı (Gizli)</option>
                                    </select>
                                    <div class="form-text text-muted">Afiş silinmeden geçici olarak yayından kaldırılabilir.</div>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="slide_startsAt" class="form-label">Yayın Başlangıç Tarihi</label>
                                    <input type="datetime-local" class="form-control" id="slide_startsAt" name="startsAt">
                                    <div class="form-text text-muted">Boşsa hemen yayına girer. İleri tarih seçilirse otomatik yayınlanır.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="slide_expiresAt" class="form-label">Yayın Bitiş Tarihi</label>
                                    <input type="datetime-local" class="form-control" id="slide_expiresAt" name="expiresAt">
                                    <div class="form-text text-muted">Ayarlandığı tarih geldiğinde afiş otomatik durdurulur. Boşsa süresiz kalır.</div>
                                </div>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-md-6">
                                    <div class="form-check p-2 bg-light rounded-3 border h-100">
                                        <input class="form-check-input ms-1" type="checkbox" id="fullWidth" name="fullWidth" value="1">
                                        <label class="form-check-label fw-bold ms-2" for="fullWidth">
                                            Tam Genişlik
                                        </label>
                                        <div class="form-text text-muted ms-2 small">Görsel ekranı kaplasın</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-2 bg-light rounded-3 border h-100">
                                        <input class="form-check-input ms-1" type="checkbox" id="slide_showCaption" name="showCaption" value="1" checked>
                                        <label class="form-check-label fw-bold ms-2" for="slide_showCaption">
                                            Başlık ve Açıklama Göster
                                        </label>
                                        <div class="form-text text-muted ms-2 small">Kiosk ekranında sol altta gösterilsin</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Afiş Görseli <span class="text-muted fw-normal" style="font-size: 0.82rem;">(YouTube linkinde isteğe bağlı)</span></label>
                            <input type="file" name="image" id="slide_image" class="form-control" accept="image/*"/>
                            <div class="form-text text-muted mb-2">JPG, PNG, WEBP (YouTube linki girildiğinde boş bırakılırsa video kapak görseli otomatik indirilir)</div>
                            
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
                                <label for="update_slide_link" class="form-label fw-semibold">Detay Bağlantısı / YouTube Video Adresi & QR Kod Konumu</label>
                                <div class="input-group">
                                    <input type="url" class="form-control" id="update_slide_link" name="link" placeholder="https://www.youtube.com/watch?v=... veya https://ornek.edu.tr">
                                    <select class="form-select" id="update_slide_qrPosition" name="qrPosition" style="max-width: 205px;">
                                        <option value="bottom-right">↘️ Sağ Alt (Önerilen)</option>
                                        <option value="bottom-left">↙️ Sol Alt</option>
                                        <option value="top-right">↗️ Sağ Üst</option>
                                        <option value="top-left">↖️ Sol Üst</option>
                                        <option value="none">🚫 Gösterme (Gizle)</option>
                                    </select>
                                </div>
                                <div class="form-text text-muted">YouTube adresi girildiğinde kiosk ekranında afiş yerine video oynatılır.</div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="update_slide_orderNumber" class="form-label">Sıralama / Öncelik</label>
                                    <input type="number" class="form-control" id="update_slide_orderNumber" name="orderNumber" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="update_slide_isActive" class="form-label">Yayın Durumu</label>
                                    <select class="form-select" id="update_slide_isActive" name="isActive">
                                        <option value="1">🟢 Yayında (Aktif)</option>
                                        <option value="0">⏸️ Duraklatıldı (Gizli)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="update_slide_startsAt" class="form-label">Yayın Başlangıç Tarihi</label>
                                    <input type="datetime-local" class="form-control" id="update_slide_startsAt" name="startsAt">
                                    <div class="form-text text-muted">Boşsa hemen yayına girer.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="update_slide_expiresAt" class="form-label">Yayın Bitiş Tarihi</label>
                                    <input type="datetime-local" class="form-control" id="update_slide_expiresAt" name="expiresAt">
                                    <div class="form-text text-muted">Tarih dolduğunda otomatik durdurulur. Boşsa süresiz kalır.</div>
                                </div>
                            </div>
                            <div class="row g-2 mt-1">
                                 <div class="col-md-6">
                                     <div class="form-check p-2 bg-light rounded-3 border h-100">
                                         <input class="form-check-input ms-1" type="checkbox" id="update_fullWidth" name="fullWidth" value="1">
                                         <label class="form-check-label fw-bold ms-2" for="update_fullWidth">
                                             Tam Genişlik
                                         </label>
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-check p-2 bg-light rounded-3 border h-100">
                                         <input class="form-check-input ms-1" type="checkbox" id="update_showCaption" name="showCaption" value="1">
                                         <label class="form-check-label fw-bold ms-2" for="update_showCaption">
                                             Başlık ve Açıklama Göster
                                         </label>
                                     </div>
                                 </div>
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