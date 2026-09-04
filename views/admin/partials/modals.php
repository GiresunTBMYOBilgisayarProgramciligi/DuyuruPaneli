<?php
declare(strict_types=1);
?>
<!-- Modal: QR Kod İnceleme & İndirme -->
<div class="modal fade" id="qrPreviewModal" tabindex="-1" aria-labelledby="qrPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="qrPreviewLabel">📱 QR Kod Detayı</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body py-3">
                <div id="modalQrCodeContainer" class="p-3 bg-white border rounded-3 d-inline-block shadow-sm mb-3">
                    <!-- SVG QR Kod Buraya Basılır -->
                </div>
                <div class="small fw-bold text-dark mb-1" id="modalQrTitle">Duyuru / Afiş Başlığı</div>
                <div class="small text-muted text-break mb-2" id="modalQrTargetUrl"></div>
                <div class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill" id="modalQrScanBadge">0 Okutma</div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-primary" id="modalDownloadQrBtn">SVG İndir</button>
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Görsel Büyütme (Image Lightbox) -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Kapat"></button>
                <img src="" id="modalPreviewFullImg" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh;" alt="Önizleme">
                <div class="mt-2 text-white small fw-semibold" id="modalPreviewImgTitle"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Silme Onayı -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div class="text-danger mb-3" style="font-size: 3rem; line-height: 1;">⚠️</div>
                <h5 class="fw-bold mb-2">Kaydı Sil</h5>
                <p class="text-muted small mb-4">Bu kaydı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">Evet, Sil</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Bildirim Konteyneri -->
<div id="adminToastContainer"></div>
