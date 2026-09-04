/**
 * UniPano Admin Yönetim Scripti
 * Bağımlılık gerektirmeden doğrudan tarayıcıda çalışır (ES5/ES6)
 */
$(function () {
    if (window.location.pathname.indexOf("loginView.php") === -1) {
        var loadingAnimation = $(
            '<div class="overlay">' +
            '   <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' +
            '</div>'
        );

        function getCsrfToken() {
            return window.UNIPANO_CSRF || $('meta[name="csrf-token"]').attr('content') || '';
        }

        function escapeHtml(string) {
            if (!string) return '';
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

        function islemlerHTML(drData) {
            var deleteData = drData.deleteData;
            var updateData = drData.updateData;
            var updateButtonString = '<a class="dropdown-item edit-btn" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#' + updateData.modalName + '"';
            
            Object.entries(updateData).forEach(function (entry) {
                var k = entry[0];
                var v = entry[1];
                if (v !== null && v !== undefined) {
                    updateButtonString += ' data-bs-' + k + '="' + escapeHtml(v) + '" ';
                }
            });
            updateButtonString += '><i class="ti-pencil me-2 text-warning"></i>Düzenle</a>\n';

            return $(
                '<div class="btn-group">\n' +
                '  <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">İşlemler</button>\n' +
                '  <div class="dropdown-menu shadow-sm">\n' +
                updateButtonString +
                '    <div class="dropdown-divider"></div>\n' +
                '    <form name="' + deleteData.formName + '" id="' + deleteData.fromId + '" method="post">\n' +
                '      <input type="hidden" name="id" value="' + deleteData.deleteId + '">\n' +
                '      <button type="submit" form="' + deleteData.fromId + '" class="dropdown-item text-danger"><i class="ti-trash me-2"></i>Sil</button>\n' +
                '    </form>\n' +
                '  </div>\n' +
                '</div>'
            ).html();
        }

        /* ----------------------------------------------------
         * FORMLARI GÖNDERME (Add & Update)
         * ---------------------------------------------------- */
        function bindAjaxForm(formName, functionName, modalId, refreshFn) {
            $('form[name="' + formName + '"]').off('submit').on('submit', function (e) {
                e.preventDefault();
                var form = this;
                var formData = new FormData(form);
                formData.append('functionName', functionName);
                formData.append('csrf_token', getCsrfToken());

                var modalEl = document.getElementById(modalId);
                var modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

                $.ajax({
                    method: "POST",
                    url: "ajax.php",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        if (modalEl) $(".modal-content", modalEl).prepend(loadingAnimation);
                    },
                    success: function (res) {
                        loadingAnimation.remove();
                        if (res.error) {
                            alert("Hata: " + res.error);
                        } else {
                            if (modal) modal.hide();
                            form.reset();
                            refreshFn();
                        }
                    },
                    error: function (xhr) {
                        loadingAnimation.remove();
                        var msg = "Bir sunucu hatası oluştu.";
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                        alert("Hata: " + msg);
                    }
                });
            });
        }

        bindAjaxForm('newSlideForm', 'saveSlide', 'newSlideModal', getSlides);
        bindAjaxForm('updateSlideForm', 'updateSlide', 'updateSlideModal', getSlides);

        bindAjaxForm('newAnnouncementForm', 'saveAnnouncement', 'newAnnouncementModal', getAnnouncements);
        bindAjaxForm('updateAnnouncementForm', 'updateAnnouncement', 'updateAnnouncementModal', getAnnouncements);

        bindAjaxForm('newUserForm', 'saveUser', 'newUserModal', getUsers);
        bindAjaxForm('updateUserForm', 'updateUser', 'updateUserModal', getUsers);

        /* ----------------------------------------------------
         * LİSTELERİ ÇEKME (AJAX)
         * ---------------------------------------------------- */

        // 1. Slaytlar
        function getSlides() {
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: { functionName: "getSlidesList" },
                dataType: "json",
                beforeSend: function () {
                    if ($(".overlay").length < 1) {
                        $("#slidesTable tbody").prepend(loadingAnimation);
                    }
                },
                success: function (slides) {
                    loadingAnimation.remove();
                    var outHTML = "";
                    if (!slides || slides.length === 0) {
                        outHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Henüz kayıtlı bir afiş / slayt bulunmamaktadır.</td></tr>';
                    } else {
                        slides.forEach(function (slide, i) {
                            var scanBadge = slide.scanCount > 0 
                                ? '<span class="badge bg-success" title="QR Kod Okutulma Sayısı">🔥 ' + slide.scanCount + ' Okutma</span>'
                                : '<span class="badge bg-light text-secondary border">0 Okutma</span>';

                            var imgHtml = slide.image 
                                ? '<img src="../' + escapeHtml(slide.image) + '" style="width:70px; height:45px; object-fit:cover; border-radius:4px;" class="border" alt="Afiş">' 
                                : '<span class="text-muted small">Resim Yok</span>';

                            var qrHtml = slide.qrCode 
                                ? '<div style="width:40px; height:40px;">' + slide.qrCode + '</div>' 
                                : '<span class="text-muted small">-</span>';

                            outHTML += '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td class="fw-bold">' + escapeHtml(slide.title) + '</td>' +
                                '<td><small class="text-muted">' + escapeHtml(slide.content || '-') + '</small></td>' +
                                '<td>' + imgHtml + '</td>' +
                                '<td>' + qrHtml + '</td>' +
                                '<td>' + scanBadge + '</td>' +
                                '<td><small>' + escapeHtml(slide.userFullName) + '</small></td>' +
                                '<td><small class="text-muted">' + escapeHtml(slide.createdDate) + '</small></td>' +
                                '<td class="text-center">' + islemlerHTML({
                                    deleteData: {
                                        formName: "deleteSlide-" + slide.id,
                                        fromId: "deleteSlide-" + slide.id,
                                        deleteId: slide.id
                                    },
                                    updateData: {
                                        modalName: 'updateSlideModal',
                                        id: slide.id,
                                        title: slide.title,
                                        content: slide.content,
                                        link: slide.link,
                                        'full-width': slide.fullWidth
                                    }
                                }) + '</td>' +
                            '</tr>';
                        });
                    }
                    $("#slidesTable tbody").html(outHTML);
                    bindDeleteForms('deleteSlide', getSlides);
                }
            });
        }

        // 2. Duyurular
        function getAnnouncements() {
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: { functionName: "getAnnouncementsList" },
                dataType: "json",
                beforeSend: function () {
                    if ($(".overlay").length < 1) {
                        $("#announcmentTable tbody").prepend(loadingAnimation);
                    }
                },
                success: function (list) {
                    loadingAnimation.remove();
                    var outHTML = "";
                    if (!list || list.length === 0) {
                        outHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Henüz kayıtlı bir duyuru bulunmamaktadır.</td></tr>';
                    } else {
                        list.forEach(function (a, i) {
                            var scanBadge = a.scanCount > 0 
                                ? '<span class="badge bg-success">🔥 ' + a.scanCount + ' Okutma</span>'
                                : '<span class="badge bg-light text-secondary border">0 Okutma</span>';

                            var qrHtml = a.qrCode 
                                ? '<div style="width:40px; height:40px;">' + a.qrCode + '</div>' 
                                : '<span class="text-muted small">-</span>';

                            outHTML += '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td class="fw-bold">' + escapeHtml(a.title || 'Duyuru') + '</td>' +
                                '<td>' + escapeHtml(a.content) + '</td>' +
                                '<td>' + qrHtml + '</td>' +
                                '<td>' + scanBadge + '</td>' +
                                '<td><small>' + escapeHtml(a.userFullName) + '</small></td>' +
                                '<td><small class="text-muted">' + escapeHtml(a.createdDate) + '</small></td>' +
                                '<td class="text-center">' + islemlerHTML({
                                    deleteData: {
                                        formName: "deleteAnnouncement-" + a.id,
                                        fromId: "deleteAnnouncement-" + a.id,
                                        deleteId: a.id
                                    },
                                    updateData: {
                                        modalName: 'updateAnnouncementModal',
                                        id: a.id,
                                        title: a.title,
                                        content: a.content,
                                        link: a.link
                                    }
                                }) + '</td>' +
                            '</tr>';
                        });
                    }
                    $("#announcmentTable tbody").html(outHTML);
                    bindDeleteForms('deleteAnnouncement', getAnnouncements);
                }
            });
        }

        // 3. Kullanıcılar
        function getUsers() {
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: { functionName: "getUsersList" },
                dataType: "json",
                beforeSend: function () {
                    if ($(".overlay").length < 1) {
                        $("#usersTable tbody").prepend(loadingAnimation);
                    }
                },
                success: function (users) {
                    loadingAnimation.remove();
                    var outHTML = "";
                    if (!users || users.length === 0) {
                        outHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Kullanıcı bulunamadı.</td></tr>';
                    } else {
                        users.forEach(function (user, i) {
                            outHTML += '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td class="fw-bold"><i class="ti-user me-1 text-primary"></i>' + escapeHtml(user.userName) + '</td>' +
                                '<td>' + escapeHtml(user.mail) + '</td>' +
                                '<td>' + escapeHtml(user.name) + '</td>' +
                                '<td>' + escapeHtml(user.lastName) + '</td>' +
                                '<td><small class="text-muted">' + escapeHtml(user.createdDate) + '</small></td>' +
                                '<td class="text-center">' + islemlerHTML({
                                    deleteData: {
                                        formName: "deleteUser-" + user.id,
                                        fromId: "deleteUser-" + user.id,
                                        deleteId: user.id
                                    },
                                    updateData: {
                                        modalName: "updateUserModal",
                                        id: user.id,
                                        'user-name': user.userName,
                                        mail: user.mail,
                                        name: user.name,
                                        'last-name': user.lastName
                                    }
                                }) + '</td>' +
                            '</tr>';
                        });
                    }
                    $("#usersTable tbody").html(outHTML);
                    bindDeleteForms('deleteUser', getUsers);
                }
            });
        }

        // Silme formu bağlayıcı
        function bindDeleteForms(prefix, refreshFn) {
            $('form[name*="' + prefix + '"]').off('submit').on('submit', function (e) {
                e.preventDefault();
                if (!confirm("Bu kaydı silmek istediğinizden emin misiniz?")) {
                    return false;
                }

                var formData = new FormData(this);
                formData.append('functionName', prefix);
                formData.append('csrf_token', getCsrfToken());

                $.ajax({
                    method: "POST",
                    url: "ajax.php",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.error) {
                            alert("Hata: " + res.error);
                        } else {
                            refreshFn();
                        }
                    },
                    error: function (xhr) {
                        var msg = "Silme işlemi sırasında hata oluştu.";
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                        alert("Hata: " + msg);
                    }
                });
            });
        }

        // Sayfa açılışında verileri yükle
        getSlides();
        getAnnouncements();
        getUsers();

        // Güncelleme Modalları Açıldığında Inputları Doldurma
        $("[id^=update]").on('show.bs.modal', function (event) {
            var modal = this;
            var button = event.relatedTarget;
            var formData = {};

            if (button && button.attributes) {
                $.each(button.attributes, function (i, attr) {
                    if (attr.name.indexOf("data-bs-") === 0) {
                        var key = attr.name.replace("data-bs-", "");
                        if (['toggle', 'target'].indexOf(key) === -1) {
                            formData[toCamelCase(key)] = attr.value;
                        }
                    }
                });
            }

            Object.entries(formData).forEach(function (entry) {
                var k = entry[0];
                var v = entry[1];
                if (k === "fullWidth") {
                    $('input[name="fullWidth"]', modal).prop('checked', v == 1);
                } else if (k !== "image") {
                    var input = $('input[name="' + k + '"], textarea[name="' + k + '"]', modal);
                    if (input.length > 0) {
                        input.val(v);
                    }
                }
            });
        });
    }
});
