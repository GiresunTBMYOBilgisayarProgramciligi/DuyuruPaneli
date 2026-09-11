/**
 * UniPano - Modern Minimalist Admin Yönetim Motoru
 * %100 Saf Vanilla JavaScript (ES6+) — Sıfır Bağımlılık, Sıfır jQuery
 * Bootstrap 5 Native JS API & Fetch API ile ultra hızlı ve modern yapı.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    if (window.location.pathname.indexOf("login") !== -1) {
        return;
    }

    // -----------------------------------------------------------------
    // 1. Evrensel Modal Açma / Kapatma Motoru (Bootstrap 5 Native)
    // -----------------------------------------------------------------
    function showModal(selectorOrEl) {
        const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl;
        if (!el) return;

        try {
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
                return;
            }
        } catch (e) {
            console.warn('[UniPano] Bootstrap modal error:', e);
        }

        // Native DOM Fallback
        el.classList.add('show');
        el.style.display = 'block';
        el.removeAttribute('aria-hidden');
        el.setAttribute('aria-modal', 'true');
        if (!document.querySelector('.modal-backdrop')) {
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    function hideModal(selectorOrEl) {
        const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl;
        if (!el) return;

        try {
            if (window.bootstrap && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
                if (inst) inst.hide();
                return;
            }
        } catch (e) {
            console.warn('[UniPano] Bootstrap modal hide error:', e);
        }

        // Native DOM Fallback
        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        el.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
    }

    // -----------------------------------------------------------------
    // 2. Sekme Yönetimi & Oturumda Hatırlama (Vanilla JS Tab Persistence)
    // -----------------------------------------------------------------
    document.querySelectorAll('.admin-tabs-nav button[data-bs-toggle="tab"]').forEach(function (tabBtn) {
        tabBtn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                try {
                    sessionStorage.setItem('unipano_active_tab', target);
                } catch (err) {}
            }
        });
    });

    try {
        const lastTab = sessionStorage.getItem('unipano_active_tab');
        if (lastTab) {
            const tabBtn = document.querySelector('.admin-tabs-nav button[data-bs-target="' + lastTab + '"]');
            if (tabBtn && window.bootstrap && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(tabBtn).show();
            }
        }
    } catch (err) {}

    // -----------------------------------------------------------------
    // 3. Yardımcılar & Toast Bildirim Sistemi
    // -----------------------------------------------------------------
    function getCsrfToken() {
        if (window.UNIPANO_CSRF) return window.UNIPANO_CSRF;
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(string) {
        if (string === null || string === undefined) return '';
        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toCamelCase(str) {
        return str.replace(/-([a-z0-9])/g, function (g) { return g[1].toUpperCase(); });
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('adminToastContainer');
        if (!container) return;

        let icon = '✅';
        let typeClass = 'toast-success';
        if (type === 'error') {
            icon = '❌';
            typeClass = 'toast-error';
        } else if (type === 'warning') {
            icon = '⚠️';
            typeClass = 'toast-warning';
        }

        const toast = document.createElement('div');
        toast.className = 'admin-toast ' + typeClass;
        toast.innerHTML = '<span class="toast-icon">' + icon + '</span>' +
            '<span class="toast-message">' + escapeHtml(message) + '</span>';

        container.appendChild(toast);

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(function () {
                toast.remove();
            }, 300);
        }, 3500);
    }

    // -----------------------------------------------------------------
    // 4. Canlı Dosya Yükleme Önizleme Mekanizması
    // -----------------------------------------------------------------
    function setupImagePreview(inputId, previewBoxId, previewImgId, infoId) {
        const input = document.getElementById(inputId);
        const box = document.getElementById(previewBoxId);
        const img = document.getElementById(previewImgId);
        const info = document.getElementById(infoId);

        if (!input || !box || !img) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const sizeKb = Math.round(file.size / 1024);
                const sizeText = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : sizeKb + ' KB';
                if (info) info.textContent = file.name + ' (' + sizeText + ')';

                const reader = new FileReader();
                reader.onload = function (e) {
                    img.setAttribute('src', e.target.result);
                    box.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                box.style.display = 'none';
            }
        });
    }

    setupImagePreview('slide_image', 'newSlidePreviewBox', 'newSlidePreviewImg', 'newSlideFileInfo');
    setupImagePreview('update_slide_image', 'updateSlidePreviewBox', 'updateSlidePreviewImg', 'updateSlideFileInfo');

    function setupYouTubeInputPreview(linkInputId, fileInputId, boxId, imgId, infoId) {
        const linkEl = document.getElementById(linkInputId);
        const fileEl = document.getElementById(fileInputId);
        const box = document.getElementById(boxId);
        const img = document.getElementById(imgId);
        const info = document.getElementById(infoId);
        if (!linkEl || !box || !img) return;

        const ytRegex = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;

        linkEl.addEventListener('input', function () {
            if (fileEl && fileEl.files && fileEl.files[0]) {
                return;
            }

            const val = linkEl.value.trim();
            const match = val.match(ytRegex);
            if (match && match[1]) {
                const videoId = match[1];
                img.setAttribute('src', 'https://img.youtube.com/vi/' + videoId + '/hqdefault.jpg');
                if (info) info.textContent = '▶️ YouTube Video Kapağı (Otomatik İndirilecek)';
                box.style.display = 'block';
            } else if (!fileEl || !fileEl.files || !fileEl.files[0]) {
                box.style.display = 'none';
            }
        });
    }

    setupYouTubeInputPreview('slide_link', 'slide_image', 'newSlidePreviewBox', 'newSlidePreviewImg', 'newSlideFileInfo');
    setupYouTubeInputPreview('update_slide_link', 'update_slide_image', 'updateSlidePreviewBox', 'updateSlidePreviewImg', 'updateSlideFileInfo');

    // -----------------------------------------------------------------
    // 5. Tablo Aksiyon Butonları (İşlemler)
    // -----------------------------------------------------------------
    function renderActions(itemType, id, updateData, isActive = 1) {
        let updateAttrs = '';
        Object.entries(updateData).forEach(function (entry) {
            const k = entry[0];
            const v = entry[1];
            if (v !== null && v !== undefined) {
                updateAttrs += ' data-bs-' + k + '="' + escapeHtml(v) + '" ';
            }
        });

        let toggleBtnHtml = '';
        if (itemType === 'deleteSlide') {
            const isAct = parseInt(isActive, 10) === 1;
            toggleBtnHtml = '<button type="button" class="btn-icon-action btn-toggle-status ' + (isAct ? 'btn-pause' : 'btn-resume') + '" title="' + (isAct ? 'Yayından Kaldır / Duraklat' : 'Tekrar Yayına Al') + '" data-type="slide" data-id="' + id + '">' +
                (isAct ? '⏸️' : '▶️') +
                '</button>';
        } else if (itemType === 'deleteAnnouncement') {
            const isAct = parseInt(isActive, 10) === 1;
            toggleBtnHtml = '<button type="button" class="btn-icon-action btn-toggle-status ' + (isAct ? 'btn-pause' : 'btn-resume') + '" title="' + (isAct ? 'Yayından Kaldır / Duraklat' : 'Tekrar Yayına Al') + '" data-type="announcement" data-id="' + id + '">' +
                (isAct ? '⏸️' : '▶️') +
                '</button>';
        }

        return '<div class="btn-action-group">' +
            toggleBtnHtml +
            '<button type="button" class="btn-icon-action btn-edit" title="Düzenle" data-bs-target="#' + updateData.modalName + '" ' + updateAttrs + '>' +
            '✏️' +
            '</button>' +
            '<button type="button" class="btn-icon-action btn-icon-delete btn-delete" title="Sil" data-type="' + itemType + '" data-id="' + id + '">' +
            '🗑️' +
            '</button>' +
            '</div>';
    }

    // Düzenleme Butonu Dinleyicisi (Delegated Event)
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.btn-edit');
        if (!editBtn) return;
        e.preventDefault();

        const targetModalSelector = editBtn.getAttribute('data-bs-target') || editBtn.getAttribute('data-target');
        const modalEl = document.querySelector(targetModalSelector);
        if (!modalEl) return;

        Array.from(editBtn.attributes).forEach(function (attr) {
            if (attr.name.indexOf("data-bs-") === 0) {
                const key = attr.name.replace("data-bs-", "");
                if (key !== 'toggle' && key !== 'target') {
                    const val = attr.value;
                    if (key === 'full-width' || key === 'fullwidth') {
                        const fwInput = modalEl.querySelector('input[name="fullWidth"]');
                        if (fwInput) fwInput.checked = (val == 1 || val === 'true');
                    } else if (key === 'show-caption' || key === 'showcaption') {
                        const scInput = modalEl.querySelector('input[name="showCaption"]');
                        if (scInput) scInput.checked = (val == 1 || val === 'true');
                    } else if (key === 'qr-position' || key === 'qrposition') {
                        const qrSelect = modalEl.querySelector('select[name="qrPosition"]');
                        if (qrSelect) qrSelect.value = String(val);
                    } else if (key === 'isactive' || key === 'is-active') {
                        const activeSelect = modalEl.querySelector('select[name="isActive"]');
                        if (activeSelect) activeSelect.value = String(val);
                    } else if (key === 'startsat' || key === 'starts-at') {
                        const startsInput = modalEl.querySelector('input[name="startsAt"]');
                        if (startsInput) {
                            startsInput.value = val ? val.replace(' ', 'T').substring(0, 16) : '';
                        }
                    } else if (key === 'expiresat' || key === 'expires-at') {
                        const expiresInput = modalEl.querySelector('input[name="expiresAt"]');
                        if (expiresInput) {
                            expiresInput.value = val ? val.replace(' ', 'T').substring(0, 16) : '';
                        }
                    } else if (key === 'ordernumber' || key === 'order-number') {
                        const orderInput = modalEl.querySelector('input[name="orderNumber"]');
                        if (orderInput) orderInput.value = val || '0';
                    } else {
                        const inputs = modalEl.querySelectorAll('input[name="' + key + '"], textarea[name="' + key + '"], select[name="' + key + '"]');
                        inputs.forEach(function (inp) { inp.value = val; });

                        const camelKey = toCamelCase(key);
                        const camelInputs = modalEl.querySelectorAll('input[name="' + camelKey + '"], textarea[name="' + camelKey + '"], select[name="' + camelKey + '"]');
                        camelInputs.forEach(function (inp) { inp.value = val; });
                    }
                }
            }
        });

        showModal(modalEl);
    });

    // Duraklat / Yayına Al Butonu Dinleyicisi
    document.addEventListener('click', async function (e) {
        const toggleBtn = e.target.closest('.btn-toggle-status');
        if (!toggleBtn) return;
        e.preventDefault();

        const itemType = toggleBtn.getAttribute('data-type');
        const id = toggleBtn.getAttribute('data-id');
        if (!itemType || !id) return;

        const actionName = (itemType === 'slide') ? 'toggleSlideStatus' : 'toggleAnnouncementStatus';
        const formData = new FormData();
        formData.append('functionName', actionName);
        formData.append('id', id);
        formData.append('csrf_token', getCsrfToken());

        try {
            toggleBtn.disabled = true;
            const res = await fetch("/admin/ajax", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                if (itemType === 'slide') {
                    getSlides();
                } else {
                    getAnnouncements();
                }
            } else {
                showToast(data.message || 'İşlem gerçekleştirilemedi.', 'error');
            }
        } catch (err) {
            console.error('[UniPano] Durum değiştirilemedi:', err);
            showToast('Bağlantı hatası oluştu.', 'error');
        } finally {
            toggleBtn.disabled = false;
        }
    });

    // Sıralama Değiştirme Dinleyicisi (Hızlı Kayıt)
    document.addEventListener('change', async function (e) {
        const orderInput = e.target.closest('.item-order-input');
        if (!orderInput) return;

        const itemType = orderInput.getAttribute('data-type');
        const id = orderInput.getAttribute('data-id');
        const orderNumber = parseInt(orderInput.value || '0', 10);
        if (!itemType || !id) return;

        const actionName = (itemType === 'slide') ? 'updateSlideOrder' : 'updateAnnouncementOrder';
        const formData = new FormData();
        formData.append('functionName', actionName);
        formData.append('id', id);
        formData.append('orderNumber', String(orderNumber));
        formData.append('csrf_token', getCsrfToken());

        try {
            const res = await fetch("/admin/ajax", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                if (itemType === 'slide') {
                    getSlides();
                } else {
                    getAnnouncements();
                }
            } else {
                showToast(data.message || 'Sıralama güncellenemedi.', 'error');
            }
        } catch (err) {
            console.error('[UniPano] Sıralama güncellenemedi:', err);
            showToast('Bağlantı hatası oluştu.', 'error');
        }
    });

    // -----------------------------------------------------------------
    // 6. Silme Onayı Modalı (Modern Native Fetch)
    // -----------------------------------------------------------------
    let pendingDelete = null;

    document.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.btn-delete');
        if (!delBtn) return;
        e.preventDefault();

        pendingDelete = {
            type: delBtn.getAttribute('data-type'),
            id: delBtn.getAttribute('data-id')
        };
        showModal('#confirmDeleteModal');
    });

    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            if (!pendingDelete) return;

            const functionName = pendingDelete.type;
            const id = pendingDelete.id;

            const formData = new FormData();
            formData.append('functionName', functionName);
            formData.append('id', id);
            formData.append('csrf_token', getCsrfToken());

            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.textContent = 'Siliniyor...';

            try {
                const response = await fetch("/admin/ajax", {
                    method: "POST",
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const res = await response.json();
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.textContent = 'Evet, Sil';
                hideModal('#confirmDeleteModal');

                if (res.error) {
                    showToast(res.error, 'error');
                } else {
                    showToast("Kayıt başarıyla silindi.", 'success');
                    if (functionName === 'deleteSlide') getSlides();
                    else if (functionName === 'deleteAnnouncement') getAnnouncements();
                    else if (functionName === 'deleteUser') getUsers();
                }
            } catch (err) {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.textContent = 'Evet, Sil';
                hideModal('#confirmDeleteModal');
                showToast("Silme işlemi sırasında bağlantı hatası oluştu.", 'error');
            }
        });
    }

    // -----------------------------------------------------------------
    // 7. Form Bağlama (Ekleme & Güncelleme - Modern Native Fetch)
    // -----------------------------------------------------------------
    function bindAjaxForm(formName, functionName, modalId, refreshFn, successMsg) {
        const form = document.querySelector('form[name="' + formName + '"]');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('functionName', functionName);
            formData.append('csrf_token', getCsrfToken());

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.textContent : 'Kaydet';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'İşleniyor...';
            }

            try {
                const response = await fetch("/admin/ajax", {
                    method: "POST",
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const res = await response.json();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }

                if (res.error) {
                    showToast(res.error, 'error');
                } else {
                    hideModal('#' + modalId);
                    form.reset();
                    const previewBox = form.querySelector('.file-upload-preview-box');
                    if (previewBox) previewBox.style.display = 'none';

                    showToast(successMsg || "İşlem başarıyla tamamlandı.", 'success');
                    refreshFn();
                }
            } catch (err) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
                showToast("İşlem sırasında sunucu bağlantı hatası oluştu.", 'error');
            }
        });
    }

    bindAjaxForm('newSlideForm', 'saveSlide', 'newSlideModal', getSlides, 'Yeni afiş başarıyla yayınlandı.');
    bindAjaxForm('updateSlideForm', 'updateSlide', 'updateSlideModal', getSlides, 'Afiş bilgileri güncellendi.');
    bindAjaxForm('newAnnouncementForm', 'saveAnnouncement', 'newAnnouncementModal', getAnnouncements, 'Yeni kayan duyuru eklendi.');
    bindAjaxForm('updateAnnouncementForm', 'updateAnnouncement', 'updateAnnouncementModal', getAnnouncements, 'Duyuru güncellendi.');
    bindAjaxForm('newUserForm', 'saveUser', 'newUserModal', getUsers, 'Yeni kullanıcı oluşturuldu.');
    bindAjaxForm('updateUserForm', 'updateUser', 'updateUserModal', getUsers, 'Kullanıcı güncellendi.');

    // -----------------------------------------------------------------
    // 8. Veri Listeleri ve KPI İstatistik Hesaplamaları
    // -----------------------------------------------------------------
    let totalSlideScans = 0;
    let totalAnnouncementScans = 0;

    function updateKpiScans() {
        const el = document.getElementById('statScansCount');
        if (el) el.textContent = totalSlideScans + totalAnnouncementScans;
    }

    // 1. Slaytlar Listesi
    async function getSlides() {
        const formData = new FormData();
        formData.append('functionName', 'getSlidesList');
        formData.append('csrf_token', getCsrfToken());

        try {
            const response = await fetch("/admin/ajax", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const slides = await response.json();
            let outHTML = "";
            totalSlideScans = 0;

            const countEl = document.getElementById('statSlidesCount');
            const tbody = document.querySelector("#slidesTable tbody");

            if (!slides || slides.length === 0) {
                outHTML = '<tr><td colspan="10" class="text-center text-muted py-5">Henüz kayıtlı bir afiş bulunmamaktadır.</td></tr>';
                if (countEl) countEl.textContent = '0';
            } else {
                if (countEl) countEl.textContent = String(slides.length);

                slides.forEach(function (slide, i) {
                    const scanCount = parseInt(slide.scanCount || 0, 10);
                    totalSlideScans += scanCount;

                    const scanBadge = scanCount > 0
                        ? '<span class="badge-scan badge-scan-hot">🔥 ' + scanCount + ' Okutma</span>'
                        : '<span class="badge-scan badge-scan-zero">0 Okutma</span>';

                    const isYt = Boolean(slide.youtubeVideoId || (slide.link && /(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i.test(slide.link)));

                    let imgHtml = '';
                    if (slide.image) {
                        const imgSrc = '/' + escapeHtml(slide.image.replace(/^\/+/, ''));
                        imgHtml = '<div class="position-relative d-inline-block">' +
                            '<img src="' + imgSrc + '" class="table-thumb" alt="Afiş" data-title="' + escapeHtml(slide.title) + '" data-full="' + imgSrc + '">' +
                            (isYt ? '<span class="position-absolute bottom-0 end-0 badge bg-danger p-1 rounded-circle m-1 shadow-sm" title="YouTube Video Afişi" style="font-size:0.6rem;line-height:1;"><svg width="10" height="10" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg></span>' : '') +
                            '</div>';
                    } else if (isYt) {
                        imgHtml = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">▶️ YouTube</span>';
                    } else {
                        imgHtml = '<span class="text-muted small">Resim Yok</span>';
                    }

                    const qrHtml = slide.qrCode
                        ? '<div class="table-qr-mini" title="QR Detayı" data-title="' + escapeHtml(slide.title) + '" data-url="' + escapeHtml(slide.link || '') + '" data-scans="' + scanCount + '">' + slide.qrCode + '</div>'
                        : '<span class="text-muted small">-</span>';

                    const contentSummary = slide.content ? '<div class="text-muted small text-truncate" style="max-width:260px;">' + escapeHtml(slide.content) + '</div>' : '';

                    const isAct = parseInt(slide.isActive ?? 1, 10) === 1;
                    const nowMs = Date.now();
                    const isExpired = Boolean(slide.expiresAt && (new Date(slide.expiresAt.replace(' ', 'T')).getTime() <= nowMs));
                    const isScheduled = Boolean(slide.startsAt && (new Date(slide.startsAt.replace(' ', 'T')).getTime() > nowMs));

                    let statusBadge = '';
                    if (isExpired) {
                        statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fw-semibold" title="Yayın süresi dolduğu için otomatik olarak durduruldu">⏱️ Süresi Doldu</span>';
                    } else if (!isAct) {
                        statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fw-semibold">⏸️ Duraklatıldı</span>';
                    } else if (isScheduled) {
                        statusBadge = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fw-semibold" title="Başlangıç tarihi geldiğinde otomatik olarak yayına girecek">🗓️ Zamanlandı</span>';
                    } else {
                        statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-semibold">🟢 Yayında</span>';
                    }

                    let scheduleHtml = '<div class="d-flex flex-column gap-1" style="font-size: 0.82rem; line-height: 1.3;">';
                    if (slide.startsAt) {
                        const sDate = escapeHtml(slide.startsAt.replace('T', ' ').substring(0, 16));
                        if (isScheduled) {
                            scheduleHtml += '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" title="Otomatik yayınlanma tarihi">🗓️ ' + sDate + '</span>';
                        } else {
                            scheduleHtml += '<span class="text-muted" title="Yayın başlangıç tarihi">🚀 ' + sDate + '</span>';
                        }
                    } else {
                        scheduleHtml += '<span class="text-muted small">🚀 Hemen</span>';
                    }

                    if (slide.expiresAt) {
                        const eDate = escapeHtml(slide.expiresAt.replace('T', ' ').substring(0, 16));
                        if (isExpired) {
                            scheduleHtml += '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" title="Yayın bitiş süresi doldu">⌛ ' + eDate + '</span>';
                        } else {
                            scheduleHtml += '<span class="text-secondary" title="Yayın bitiş tarihi">⏱️ ' + eDate + '</span>';
                        }
                    } else {
                        scheduleHtml += '<span class="text-muted small">⏱️ Süresiz</span>';
                    }
                    scheduleHtml += '</div>';

                    const orderInput = '<input type="number" class="form-control form-control-sm item-order-input" data-type="slide" data-id="' + slide.id + '" value="' + (slide.orderNumber || 0) + '" min="0" title="Sıra Numarası (0: Otomatik son eklenen)">';
                    const ytBadge = isYt ? '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size:0.68rem;" title="Afiş olarak YouTube videosu oynatılır">▶️ YouTube Video</span>' : '';

                    outHTML += '<tr' + (!isAct || isExpired ? ' class="table-light opacity-75"' : (isScheduled ? ' class="table-info bg-opacity-10"' : '')) + '>' +
                        '<td>' + orderInput + '</td>' +
                        '<td>' + imgHtml + '</td>' +
                        '<td>' +
                        '  <div class="fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">' +
                        '    <span>' + escapeHtml(slide.title) + '</span>' +
                        ytBadge +
                        (slide.showCaption == 1 ? '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size:0.68rem;" title="Kiosk ekranında başlık ve açıklama kartı gösteriliyor">📺 Başlık Açık</span>' : '<span class="badge bg-light text-muted border" style="font-size:0.68rem;" title="Kiosk ekranında afiş sade gösteriliyor (Başlık kartı kapalı)">📺 Başlık Gizli</span>') +
                        '  </div>' +
                        contentSummary +
                        '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td>' + scheduleHtml + '</td>' +
                        '<td>' + qrHtml + '</td>' +
                        '<td>' + scanBadge + '</td>' +
                        '<td><small class="fw-semibold">' + escapeHtml(slide.userFullName) + '</small></td>' +
                        '<td><small class="text-muted">' + escapeHtml(slide.createdDate) + '</small></td>' +
                        '<td class="text-center">' + renderActions('deleteSlide', slide.id, {
                            modalName: 'updateSlideModal',
                            id: slide.id,
                            title: slide.title,
                            content: slide.content,
                            link: slide.link,
                            'full-width': slide.fullWidth,
                            'show-caption': slide.showCaption ?? 1,
                            'qr-position': slide.qrPosition || 'bottom-right',
                            ordernumber: slide.orderNumber || 0,
                            isactive: slide.isActive ?? 1,
                            startsat: slide.startsAt || '',
                            expiresat: slide.expiresAt || ''
                        }, slide.isActive ?? 1) + '</td>' +
                        '</tr>';
                });
            }

            if (tbody) tbody.innerHTML = outHTML;
            updateKpiScans();
        } catch (err) {
            console.error('[UniPano] Slaytlar yüklenemedi:', err);
        }
    }

    // 2. Duyurular Listesi
    async function getAnnouncements() {
        const formData = new FormData();
        formData.append('functionName', 'getAnnouncementsList');
        formData.append('csrf_token', getCsrfToken());

        try {
            const response = await fetch("/admin/ajax", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const list = await response.json();
            let outHTML = "";
            totalAnnouncementScans = 0;

            const countEl = document.getElementById('statAnnouncementsCount');
            const tbody = document.querySelector("#announcmentTable tbody");

            if (!list || list.length === 0) {
                outHTML = '<tr><td colspan="10" class="text-center text-muted py-5">Henüz kayıtlı bir kayan duyuru bulunmamaktadır.</td></tr>';
                if (countEl) countEl.textContent = '0';
            } else {
                if (countEl) countEl.textContent = String(list.length);

                list.forEach(function (a, i) {
                    const scanCount = parseInt(a.scanCount || 0, 10);
                    totalAnnouncementScans += scanCount;

                    const scanBadge = scanCount > 0
                        ? '<span class="badge-scan badge-scan-hot">🔥 ' + scanCount + ' Okutma</span>'
                        : '<span class="badge-scan badge-scan-zero">0 Okutma</span>';

                    const qrHtml = a.qrCode
                        ? '<div class="table-qr-mini" title="QR Detayı" data-title="' + escapeHtml(a.title || 'Duyuru') + '" data-url="' + escapeHtml(a.link || '') + '" data-scans="' + scanCount + '">' + a.qrCode + '</div>'
                        : '<span class="text-muted small">-</span>';

                    const tagBadge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">' + escapeHtml(a.title || 'Duyuru') + '</span>';

                    const isAct = parseInt(a.isActive ?? 1, 10) === 1;
                    const nowMs = Date.now();
                    const isExpired = Boolean(a.expiresAt && (new Date(a.expiresAt.replace(' ', 'T')).getTime() <= nowMs));
                    const isScheduled = Boolean(a.startsAt && (new Date(a.startsAt.replace(' ', 'T')).getTime() > nowMs));

                    let statusBadge = '';
                    if (isExpired) {
                        statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fw-semibold" title="Yayın süresi dolduğu için otomatik olarak durduruldu">⏱️ Süresi Doldu</span>';
                    } else if (!isAct) {
                        statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fw-semibold">⏸️ Duraklatıldı</span>';
                    } else if (isScheduled) {
                        statusBadge = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fw-semibold" title="Başlangıç tarihi geldiğinde otomatik olarak yayına girecek">🗓️ Zamanlandı</span>';
                    } else {
                        statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-semibold">🟢 Yayında</span>';
                    }

                    let scheduleHtml = '<div class="d-flex flex-column gap-1" style="font-size: 0.82rem; line-height: 1.3;">';
                    if (a.startsAt) {
                        const sDate = escapeHtml(a.startsAt.replace('T', ' ').substring(0, 16));
                        if (isScheduled) {
                            scheduleHtml += '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" title="Otomatik yayınlanma tarihi">🗓️ ' + sDate + '</span>';
                        } else {
                            scheduleHtml += '<span class="text-muted" title="Yayın başlangıç tarihi">🚀 ' + sDate + '</span>';
                        }
                    } else {
                        scheduleHtml += '<span class="text-muted small">🚀 Hemen</span>';
                    }

                    if (a.expiresAt) {
                        const eDate = escapeHtml(a.expiresAt.replace('T', ' ').substring(0, 16));
                        if (isExpired) {
                            scheduleHtml += '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" title="Yayın bitiş süresi doldu">⌛ ' + eDate + '</span>';
                        } else {
                            scheduleHtml += '<span class="text-secondary" title="Yayın bitiş tarihi">⏱️ ' + eDate + '</span>';
                        }
                    } else {
                        scheduleHtml += '<span class="text-muted small">⏱️ Süresiz</span>';
                    }
                    scheduleHtml += '</div>';

                    const orderInput = '<input type="number" class="form-control form-control-sm item-order-input" data-type="announcement" data-id="' + a.id + '" value="' + (a.orderNumber || 0) + '" min="0" title="Sıra Numarası (0: Otomatik son eklenen)">';

                    outHTML += '<tr' + (!isAct || isExpired ? ' class="table-light opacity-75"' : (isScheduled ? ' class="table-info bg-opacity-10"' : '')) + '>' +
                        '<td>' + orderInput + '</td>' +
                        '<td>' + tagBadge + '</td>' +
                        '<td><div class="fw-semibold text-dark text-break">' + escapeHtml(a.content) + '</div></td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td>' + scheduleHtml + '</td>' +
                        '<td>' + qrHtml + '</td>' +
                        '<td>' + scanBadge + '</td>' +
                        '<td><small class="fw-semibold">' + escapeHtml(a.userFullName) + '</small></td>' +
                        '<td><small class="text-muted">' + escapeHtml(a.createdDate) + '</small></td>' +
                        '<td class="text-center">' + renderActions('deleteAnnouncement', a.id, {
                            modalName: 'updateAnnouncementModal',
                            id: a.id,
                            title: a.title,
                            content: a.content,
                            link: a.link,
                            ordernumber: a.orderNumber || 0,
                            isactive: a.isActive ?? 1,
                            startsat: a.startsAt || '',
                            expiresat: a.expiresAt || ''
                        }, a.isActive ?? 1) + '</td>' +
                        '</tr>';
                });
            }

            if (tbody) tbody.innerHTML = outHTML;
            updateKpiScans();
        } catch (err) {
            console.error('[UniPano] Duyurular yüklenemedi:', err);
        }
    }

    // 3. Kullanıcılar Listesi
    async function getUsers() {
        const formData = new FormData();
        formData.append('functionName', 'getUsersList');
        formData.append('csrf_token', getCsrfToken());

        try {
            const response = await fetch("/admin/ajax", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const users = await response.json();
            let outHTML = "";

            const countEl = document.getElementById('statUsersCount');
            const tbody = document.querySelector("#usersTable tbody");

            if (!users || users.length === 0) {
                outHTML = '<tr><td colspan="6" class="text-center text-muted py-5">Kayıtlı kullanıcı bulunamadı.</td></tr>';
                if (countEl) countEl.textContent = '0';
            } else {
                if (countEl) countEl.textContent = String(users.length);

                users.forEach(function (user, i) {
                    outHTML += '<tr>' +
                        '<td class="text-muted fw-bold">' + (i + 1) + '</td>' +
                        '<td class="fw-bold text-dark">👤 ' + escapeHtml(user.userName) + '</td>' +
                        '<td><a href="mailto:' + escapeHtml(user.mail) + '" class="text-decoration-none">' + escapeHtml(user.mail) + '</a></td>' +
                        '<td>' + escapeHtml(user.name + ' ' + user.lastName) + '</td>' +
                        '<td><small class="text-muted">' + escapeHtml(user.createdDate) + '</small></td>' +
                        '<td class="text-center">' + renderActions('deleteUser', user.id, {
                            modalName: "updateUserModal",
                            id: user.id,
                            'user-name': user.userName,
                            mail: user.mail,
                            name: user.name,
                            'last-name': user.lastName
                        }) + '</td>' +
                        '</tr>';
                });
            }

            if (tbody) tbody.innerHTML = outHTML;
        } catch (err) {
            console.error('[UniPano] Kullanıcılar yüklenemedi:', err);
        }
    }

    // -----------------------------------------------------------------
    // 9. İnteraktif QR Kod & Görsel Önizleme Modalları
    // -----------------------------------------------------------------
    document.addEventListener('click', function (e) {
        const qrBox = e.target.closest('.table-qr-mini');
        if (qrBox) {
            e.preventDefault();
            const qrSvg = qrBox.innerHTML;
            const title = qrBox.getAttribute('data-title') || 'QR Kod';
            const url = qrBox.getAttribute('data-url') || 'Bağlantı Yok';
            const scans = qrBox.getAttribute('data-scans') || 0;

            const modalQrContainer = document.getElementById('modalQrCodeContainer');
            const modalQrTitle = document.getElementById('modalQrTitle');
            const modalQrUrl = document.getElementById('modalQrTargetUrl');
            const modalQrBadge = document.getElementById('modalQrScanBadge');

            if (modalQrContainer) modalQrContainer.innerHTML = qrSvg;
            if (modalQrTitle) modalQrTitle.textContent = title;
            if (modalQrUrl) modalQrUrl.textContent = url;
            if (modalQrBadge) modalQrBadge.textContent = '🔥 ' + scans + ' Toplam Okutma';

            showModal('#qrPreviewModal');
            return;
        }

        const thumb = e.target.closest('.table-thumb');
        if (thumb) {
            e.preventDefault();
            const fullSrc = thumb.getAttribute('data-full');
            const title = thumb.getAttribute('data-title') || '';

            const previewImg = document.getElementById('modalPreviewFullImg');
            const previewTitle = document.getElementById('modalPreviewImgTitle');

            if (previewImg) previewImg.setAttribute('src', fullSrc);
            if (previewTitle) previewTitle.textContent = title;

            showModal('#imagePreviewModal');
        }
    });

    const downloadQrBtn = document.getElementById('modalDownloadQrBtn');
    if (downloadQrBtn) {
        downloadQrBtn.addEventListener('click', function () {
            const svgEl = document.querySelector('#modalQrCodeContainer svg');
            if (!svgEl) return;

            const svgData = new XMLSerializer().serializeToString(svgEl);
            const blob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = 'unipano-qr-' + Date.now() + '.svg';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }

    // -----------------------------------------------------------------
    // 10. Sistem Günlükleri (Log Viewer) Motoru
    // -----------------------------------------------------------------
    let cachedLogs = [];

    function loadLogs(file = '') {
        const tableBody = document.getElementById('logTableBody');
        const countBadge = document.getElementById('logCountBadge');
        const fileSelect = document.getElementById('logFileSelect');
        const downloadBtn = document.getElementById('downloadLogBtn');

        if (!tableBody) return;

        const formData = new FormData();
        formData.append('functionName', 'getLogs');
        if (file) {
            formData.append('file', file);
        }

        fetch('/admin/ajax', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (!res || !res.logs) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Günlük kaydı bulunamadı.</td></tr>';
                return;
            }

            cachedLogs = res.logs;

            // Dosya dropdown'ını güncelle
            if (fileSelect && res.files && res.files.length > 0) {
                fileSelect.innerHTML = '';
                res.files.forEach((f, idx) => {
                    const opt = document.createElement('option');
                    opt.value = f.filename;
                    opt.textContent = f.filename + ' (' + f.sizeHuman + ' - ' + f.modifiedAt + ')';
                    if ((!file && idx === 0) || (file && file === f.filename)) {
                        opt.selected = true;
                    }
                    fileSelect.appendChild(opt);
                });
            }

            const activeFile = (fileSelect && fileSelect.value) ? fileSelect.value : '';
            if (downloadBtn) {
                downloadBtn.href = '/admin/logs/download' + (activeFile ? '?file=' + encodeURIComponent(activeFile) : '');
            }

            renderLogs();
        })
        .catch(err => {
            console.error('[UniPano] Log loading error:', err);
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Loglar yüklenirken bir hata oluştu.</td></tr>';
        });
    }

    function renderLogs() {
        const tableBody = document.getElementById('logTableBody');
        const countBadge = document.getElementById('logCountBadge');
        const levelFilter = document.getElementById('logLevelFilter')?.value || 'ALL';
        const searchVal = (document.getElementById('logSearchInput')?.value || '').trim().toLowerCase();

        if (!tableBody) return;

        let filtered = cachedLogs.filter(item => {
            if (levelFilter !== 'ALL') {
                const lvl = (item.level || '').toUpperCase();
                const ch = (item.channel || '').toUpperCase();
                if (lvl !== levelFilter && ch !== levelFilter) {
                    return false;
                }
            }

            if (searchVal !== '') {
                const raw = (item.raw || '').toLowerCase();
                const msg = (item.message || '').toLowerCase();
                if (!raw.includes(searchVal) && !msg.includes(searchVal)) {
                    return false;
                }
            }

            return true;
        });

        if (countBadge) countBadge.textContent = filtered.length;

        if (filtered.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted"><em>Kriterlere uygun günlük kaydı bulunamadı.</em></td></tr>';
            return;
        }

        let html = '';
        filtered.forEach(log => {
            const lvl = (log.level || 'INFO').toLowerCase();
            const ch = (log.channel || 'app').toLowerCase();

            html += '<tr>' +
                '<td class="text-nowrap text-secondary">' + escapeHtml(log.datetime) + '</td>' +
                '<td><span class="badge-log-level level-' + lvl + '">' + escapeHtml(log.level) + '</span></td>' +
                '<td><span class="badge-log-channel channel-' + ch + '">' + escapeHtml(log.channel) + '</span></td>' +
                '<td class="log-message-text">' + escapeHtml(log.message) + '</td>' +
                '</tr>';
        });

        tableBody.innerHTML = html;
    }

    // Olay Dinleyicileri
    document.getElementById('refreshLogsBtn')?.addEventListener('click', function () {
        const fileSelect = document.getElementById('logFileSelect');
        loadLogs(fileSelect ? fileSelect.value : '');
        showToast('Sistem günlükleri güncellendi.');
    });

    document.getElementById('logFileSelect')?.addEventListener('change', function () {
        loadLogs(this.value);
    });

    document.getElementById('logLevelFilter')?.addEventListener('change', renderLogs);
    document.getElementById('logSearchInput')?.addEventListener('input', renderLogs);

    document.getElementById('logs-tab')?.addEventListener('shown.bs.tab', function () {
        sessionStorage.setItem('unipano_active_tab', '#logsTabContent');
        loadLogs();
    });

    // -----------------------------------------------------------------
    // 12. Sistem & Görsel Optimizasyon Ayarları Yönetimi (Issue #11)
    // -----------------------------------------------------------------
    function loadSettings() {
        const formData = new FormData();
        formData.append('action', 'getSettings');

        fetch('/admin/ajax', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.settings) return;

            const s = data.settings;
            const sys = data.system || {};

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el && val !== undefined) el.value = val;
            };

            const setChecked = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.checked = (val === '1' || val === 1 || val === true);
            };

            setVal('setting_image_driver', s.image_driver || 'hybrid');
            setVal('setting_image_resize_dimension', s.image_resize_dimension || '1920');
            setVal('setting_image_quality', s.image_quality || '85');
            const qValBadge = document.getElementById('imageQualityVal');
            if (qValBadge) qValBadge.textContent = (s.image_quality || '85') + '%';

            setVal('setting_tinypng_api_key', s.tinypng_api_key || '');
            setVal('setting_url_shortener_provider', s.url_shortener_provider || 'auto');
            setVal('setting_tinyurl_api_key', s.tinyurl_api_key || '');
            setVal('setting_bitly_access_token', s.bitly_access_token || '');
            setVal('setting_slide_interval', s.slide_interval || '20');
            setChecked('setting_kiosk_video_sound', s.kiosk_video_sound);

            setChecked('setting_module_weather', s.module_weather);
            setChecked('setting_module_clock', s.module_clock);
            setChecked('setting_module_ticker', s.module_ticker);
            setChecked('setting_module_qr_analytics', s.module_qr_analytics);

            setVal('setting_institution_name', s.institution_name || '');
            setVal('setting_campus_name', s.campus_name || '');
            setVal('setting_app_tagline', s.app_tagline || '');
            setVal('setting_weather_city', s.weather_city || '');
            setVal('setting_weather_latitude', s.weather_latitude || '');
            setVal('setting_weather_longitude', s.weather_longitude || '');

            updateDriverBadge(s.image_driver || 'hybrid');
            updateShortenerBadge(s.url_shortener_provider || 'auto');

            // Sistem sağlığı göstergelerini güncelle
            const sysPhp = document.getElementById('sysPhpVer');
            if (sysPhp) sysPhp.textContent = sys.php_version || 'PHP 8.x';

            const sysGd = document.getElementById('sysGdStatus');
            if (sysGd) {
                sysGd.textContent = sys.gd_available ? 'Aktif (Kullanılabilir)' : 'Pasif';
                sysGd.className = 'badge ' + (sys.gd_available ? 'bg-success' : 'bg-danger');
            }

            const sysWebp = document.getElementById('sysWebpStatus');
            if (sysWebp) {
                sysWebp.textContent = sys.webp_supported ? 'Destekleniyor' : 'Desteklenmiyor';
                sysWebp.className = 'badge ' + (sys.webp_supported ? 'bg-success' : 'bg-warning text-dark');
            }

            const sysCurl = document.getElementById('sysCurlStatus');
            if (sysCurl) {
                sysCurl.textContent = sys.curl_available ? 'Aktif' : 'Pasif';
                sysCurl.className = 'badge ' + (sys.curl_available ? 'bg-success' : 'bg-danger');
            }

            const sysTiny = document.getElementById('sysTinyPngStatus');
            if (sysTiny) {
                if (sys.tinypng_configured) {
                    sysTiny.textContent = 'API Anahtarı Yapılandırıldı';
                    sysTiny.className = 'badge bg-success';
                } else {
                    sysTiny.textContent = 'API Anahtarı Tanımlanmamış';
                    sysTiny.className = 'badge bg-secondary';
                }
            }

            const sysTinyUrl = document.getElementById('sysTinyUrlStatus');
            if (sysTinyUrl) {
                if (sys.tinyurl_configured) {
                    sysTinyUrl.textContent = 'API Anahtarı Yapılandırıldı';
                    sysTinyUrl.className = 'badge bg-success';
                } else {
                    sysTinyUrl.textContent = 'Anonim Mod (API Anahtarsız)';
                    sysTinyUrl.className = 'badge bg-info text-dark';
                }
            }

            const sysBitly = document.getElementById('sysBitlyStatus');
            if (sysBitly) {
                if (sys.bitly_configured) {
                    sysBitly.textContent = 'API Token Yapılandırıldı';
                    sysBitly.className = 'badge bg-success';
                } else {
                    sysBitly.textContent = 'Tanımlanmamış';
                    sysBitly.className = 'badge bg-secondary';
                }
            }

            const sysShortener = document.getElementById('sysShortenerStatus');
            if (sysShortener) {
                const provMap = {
                    'auto': 'Otomatik Hibrit',
                    'tinyurl': 'TinyURL (Genel)',
                    'bitly': 'Bitly API v4',
                    'isgd': 'is.gd',
                    'internal': 'Dahili Yönlendirme'
                };
                sysShortener.textContent = provMap[s.url_shortener_provider || 'auto'] || 'Otomatik';
            }
        })
        .catch(err => {
            console.error('[UniPano] Ayarlar yüklenemedi:', err);
        });
    }

    function updateDriverBadge(driver) {
        const badge = document.getElementById('optDriverBadge');
        if (!badge) return;

        switch (driver) {
            case 'hybrid':
                badge.textContent = 'Hibrit Mod (Önerilen)';
                badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small';
                break;
            case 'gd':
                badge.textContent = 'Yerel GD Motoru';
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small';
                break;
            case 'tinypng':
                badge.textContent = 'TinyPNG Bulut API';
                badge.className = 'badge bg-info-subtle text-info border border-info-subtle px-2 py-1 small';
                break;
            case 'off':
                badge.textContent = 'Devre Dışı';
                badge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small';
                break;
        }
    }

    function updateShortenerBadge(provider) {
        const badge = document.getElementById('shortenerProviderBadge');
        if (!badge) return;

        switch (provider) {
            case 'auto':
                badge.textContent = 'Otomatik (Bitly + TinyURL)';
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small';
                break;
            case 'tinyurl':
                badge.textContent = 'TinyURL (Genel)';
                badge.className = 'badge bg-info-subtle text-info border border-info-subtle px-2 py-1 small';
                break;
            case 'bitly':
                badge.textContent = 'Bitly API v4';
                badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small';
                break;
            case 'isgd':
                badge.textContent = 'is.gd';
                badge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small';
                break;
            case 'internal':
                badge.textContent = 'Dahili Yönlendirme';
                badge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small';
                break;
        }
    }

    // Sürücü ve Kalite Slider Olayları
    document.getElementById('setting_image_driver')?.addEventListener('change', function () {
        updateDriverBadge(this.value);
    });

    document.getElementById('setting_url_shortener_provider')?.addEventListener('change', function () {
        updateShortenerBadge(this.value);
    });

    document.getElementById('setting_image_quality')?.addEventListener('input', function () {
        const qValBadge = document.getElementById('imageQualityVal');
        if (qValBadge) qValBadge.textContent = this.value + '%';
    });

    // API Anahtarı Göster / Gizle
    document.getElementById('toggleApiKeyVisibility')?.addEventListener('click', function () {
        const input = document.getElementById('setting_tinypng_api_key');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🔒';
        } else {
            input.type = 'password';
            this.textContent = '👁️';
        }
    });

    // TinyPNG API Sına & Kota Kontrolü
    document.getElementById('testTinyPngBtn')?.addEventListener('click', function () {
        const btn = this;
        const keyInput = document.getElementById('setting_tinypng_api_key');
        const resultDiv = document.getElementById('tinyPngTestResult');
        const apiKey = keyInput ? keyInput.value.trim() : '';

        if (!apiKey) {
            if (resultDiv) {
                resultDiv.className = 'alert alert-warning py-2 px-3 small mb-2';
                resultDiv.textContent = 'Lütfen önce bir TinyPNG API anahtarı giriniz.';
                resultDiv.classList.remove('d-none');
            }
            return;
        }

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sınanıyor...';

        const formData = new FormData();
        formData.append('action', 'testTinyPng');
        formData.append('csrf_token', getCsrfToken());
        formData.append('api_key', apiKey);

        fetch('/admin/ajax', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                if (data.success) {
                    resultDiv.className = 'alert alert-success py-2 px-3 small mb-2';
                    resultDiv.innerHTML = '<strong>✅ Başarılı:</strong> ' + escapeHtml(data.message);
                    showToast('TinyPNG API bağlantısı doğrulandı ve .env dosyasına kaydedildi!', 'success');
                    loadSettings(); // Sistem durumu rozetlerini ve inputları anında güncelle
                } else {
                    resultDiv.className = 'alert alert-danger py-2 px-3 small mb-2';
                    resultDiv.innerHTML = '<strong>❌ Bağlantı Başarısız:</strong> ' + escapeHtml(data.message || 'Hata oluştu.');
                    showToast('TinyPNG API bağlantısı başarısız.', 'error');
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                resultDiv.className = 'alert alert-danger py-2 px-3 small mb-2';
                resultDiv.textContent = 'Sunucu ile iletişim hatası: ' + err.message;
            }
        });
    });

    // TinyURL API Anahtarı Göster / Gizle
    document.getElementById('toggleTinyUrlKeyVisibility')?.addEventListener('click', function () {
        const input = document.getElementById('setting_tinyurl_api_key');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🔒';
        } else {
            input.type = 'password';
            this.textContent = '👁️';
        }
    });

    // Bitly Belirteci Göster / Gizle
    document.getElementById('toggleBitlyKeyVisibility')?.addEventListener('click', function () {
        const input = document.getElementById('setting_bitly_access_token');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🔒';
        } else {
            input.type = 'password';
            this.textContent = '👁️';
        }
    });

    // URL Kısaltıcı Servislerini (Bitly ve TinyURL) Sına & Bağlantı Kontrolü
    document.getElementById('testShortenerBtn')?.addEventListener('click', function () {
        const btn = this;
        const bitlyInput = document.getElementById('setting_bitly_access_token');
        const tinyUrlInput = document.getElementById('setting_tinyurl_api_key');
        const resultDiv = document.getElementById('shortenerTestResult');

        const bitlyToken = bitlyInput ? bitlyInput.value.trim() : '';
        const tinyUrlToken = tinyUrlInput ? tinyUrlInput.value.trim() : '';

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sınanıyor...';

        const formData = new FormData();
        formData.append('action', 'testShortener');
        formData.append('csrf_token', getCsrfToken());
        formData.append('bitly_token', bitlyToken);
        formData.append('tinyurl_token', tinyUrlToken);

        fetch('/admin/ajax', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                if (data.success) {
                    resultDiv.className = 'alert alert-success py-2 px-3 small mb-2';
                    resultDiv.innerHTML = '<strong>✅ Başarılı:</strong> ' + escapeHtml(data.message);
                    showToast('Kısaltma servis bağlantısı doğrulandı ve kaydedildi!', 'success');
                    loadSettings(); // Rozetleri ve ayarları güncelle
                } else {
                    resultDiv.className = 'alert alert-danger py-2 px-3 small mb-2';
                    resultDiv.innerHTML = '<strong>❌ Bağlantı Hatası:</strong> ' + escapeHtml(data.message || 'Hata oluştu.');
                    showToast('Kısaltma bağlantısı başarısız.', 'error');
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                resultDiv.className = 'alert alert-danger py-2 px-3 small mb-2';
                resultDiv.textContent = 'Sunucu ile iletişim hatası: ' + err.message;
            }
        });
    });

    // Tüm QR Kodları Yeniden Üret
    document.getElementById('regenerateQrBtn')?.addEventListener('click', function () {
        const btn = this;
        const resultDiv = document.getElementById('regenerateQrResult');

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Yeniden Üretiliyor...';

        const formData = new FormData();
        formData.append('action', 'regenerateQr');
        formData.append('csrf_token', getCsrfToken());

        fetch('/admin/ajax', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                if (data.success) {
                    resultDiv.className = 'alert alert-success py-2 px-3 small mt-2 mb-0';
                    resultDiv.innerHTML = '<strong>✅ Tamamlandı:</strong> ' + escapeHtml(data.message);
                    showToast(data.message, 'success');
                } else {
                    resultDiv.className = 'alert alert-danger py-2 px-3 small mt-2 mb-0';
                    resultDiv.innerHTML = '<strong>❌ Hata:</strong> ' + escapeHtml(data.message || 'Hata oluştu.');
                    showToast('QR kodlar yeniden üretilemedi.', 'error');
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                resultDiv.className = 'alert alert-danger py-2 px-3 small mt-2 mb-0';
                resultDiv.textContent = 'Sunucu ile iletişim hatası: ' + err.message;
            }
        });
    });

    // Ayarları Kaydetme İşlevi (Üst ve Alt Butonlar)
    function handleSaveSettings(triggerBtn) {
        const form = document.getElementById('settingsForm');
        if (!form) return;

        const formData = new FormData(form);
        formData.append('action', 'saveSettings');
        formData.append('csrf_token', getCsrfToken());

        const checkboxes = ['kiosk_video_sound', 'module_weather', 'module_clock', 'module_ticker', 'module_qr_analytics'];
        checkboxes.forEach(name => {
            if (!formData.has(name)) {
                formData.append(name, '0');
            }
        });

        const origHtml = triggerBtn.innerHTML;
        triggerBtn.disabled = true;
        triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Kaydediliyor...';

        fetch('/admin/ajax', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            triggerBtn.disabled = false;
            triggerBtn.innerHTML = origHtml;

            if (data.success) {
                showToast(data.message || 'Ayarlar başarıyla güncellendi.', 'success');
                loadSettings();
            } else {
                showToast(data.message || 'Ayarlar kaydedilemedi.', 'error');
            }
        })
        .catch(err => {
            triggerBtn.disabled = false;
            triggerBtn.innerHTML = origHtml;
            showToast('Sunucu hatası: ' + err.message, 'error');
        });
    }

    document.getElementById('saveSettingsBtn')?.addEventListener('click', function () {
        handleSaveSettings(this);
    });

    document.getElementById('saveSettingsBtnBottom')?.addEventListener('click', function () {
        handleSaveSettings(this);
    });

    document.getElementById('settings-tab')?.addEventListener('shown.bs.tab', function () {
        sessionStorage.setItem('unipano_active_tab', '#settingsTabContent');
        loadSettings();
    });

    // -----------------------------------------------------------------
    // 13. Sayfa Başlangıcı: Veri Listelerini Çek
    // -----------------------------------------------------------------
    getSlides();
    getAnnouncements();
    getUsers();

    const activeTab = sessionStorage.getItem('unipano_active_tab');
    if (window.location.pathname.endsWith('/admin/settings') || activeTab === '#settingsTabContent') {
        const settingsTabBtn = document.getElementById('settings-tab');
        if (settingsTabBtn && window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(settingsTabBtn).show();
            loadSettings();
        }
    } else if (activeTab === '#logsTabContent') {
        loadLogs();
    }
});
