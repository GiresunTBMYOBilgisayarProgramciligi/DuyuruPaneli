var $ = require('jquery');
window.$ = $;
window.jQuery = $
const bootstrap = require('bootstrap');
const jsConvert = require('js-convert-case');
require('dropify')
$(function () {
    console.log(window.location.pathname);
    if (window.location.pathname !== "/admin/loginView.php") {
        var newAnnouncementForm = $('form[name="newAnnouncementForm"]');
        var newSlideForm = $('form[name="newSlideForm"]');
        var newUserForm = $('form[name="newUserForm"]');
        var loadingAnimation = $('' +
            '<div class="overlay">' +
            '   <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' +
            '</div>')

        /**
         * Admin panelde oluşturulan listelerde her bir kayıt için işlemler dropdown butonunu html içeriği
         * @param drData
         * @returns {*|jQuery}
         */
        function islemlerHTML(drData) {
            var deleteData = drData.deleteData;
            var updateData = drData.updateData;
            var updateButtonString = '<a class="dropdown-item"  data-bs-toggle="modal" data-bs-target="#' + updateData.modalName + '"'
            Object.entries(updateData).forEach(entry => {
                const [k, v] = entry;
                updateButtonString += ' data-bs-' + k + '="' + v + '" '
            });

            updateButtonString += '>Güncelle</a>\n';
            return $('<div class="btn-group">\n' +
                '                            <button type="button" class="btn dropdown-toggle btn-primary" data-bs-toggle="dropdown" aria-expanded="false">İşlem Seç</button>\n' +
                '                            <div class="dropdown-menu" style="">\n' +
                `                               <form name="${deleteData.formName}" id="${deleteData.fromId}" method="post">\n` +
                `                                  <input type="hidden" name="id" value="${deleteData.deleteId}">\n` +
                `                                  <button type="submit" form="${deleteData.fromId}" class="dropdown-item ">Sil</button>\n` +
                '                               </form>                                ' +
                updateButtonString +
                '                            </div>    \n' +
                '                        </div>').html()
        }

//---------------------
        /*
         * Add new forms Start
         */
        $('form[name="newAnnouncementForm"]').submit(function (event) {
            event.preventDefault()
            var formData = new FormData(this)
            formData.append('functionName', "saveAnnouncement")

            var modalEl = document.getElementById('newAnnouncementModal')
            var modal = bootstrap.Modal.getInstance(modalEl)
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (respons) {
                    if (respons.error) {
                        alert(respons.error)
                        loadingAnimation.remove();
                        modal.hide()
                        getAnnouncment();
                        //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım

                        return false;
                    } else {
                        loadingAnimation.remove();
                        modal.hide()
                        getAnnouncment();
                    }
                },
                beforeSend: function () {
                    $(".modal-content", modalEl).prepend(loadingAnimation)
                },
            });
        });
        $('form[name="newSlideForm"]').submit(function (event) {
            event.preventDefault()
            var formData = new FormData(this)
            formData.append('functionName', "saveSlide")

            var modalEl = document.getElementById('newSlideModal')
            var modal = bootstrap.Modal.getInstance(modalEl)
            $.ajax({
                method: "POST",
                url: "ajax.php",
                processData: false,
                contentType: false,
                data: formData,
                dataType: "json",
                success: function (respons) {
                    if (respons.error) {
                        alert(respons.error);
                        loadingAnimation.remove();
                        modal.hide()
                        getSlides();
                        //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                        return false;
                    } else {
                        loadingAnimation.remove();
                        modal.hide()
                        getSlides();
                    }
                },
                beforeSend: function () {
                    $(".modal-content", modalEl).prepend(loadingAnimation)
                },
            });
        });
        $('form[name="newUserForm"]').submit(function (event) {
            event.preventDefault()
            var formData = new FormData(this)
            formData.append('functionName', "saveUser")
            if (formData.get('password') !== formData.get("password2")) {
                alert("şifreler eşleşmiyor");
                return false;
            }

            var modalEl = document.getElementById('newUserModal')
            var modal = bootstrap.Modal.getInstance(modalEl)
            $.ajax({
                method: "POST",
                url: "ajax.php",
                processData: false,
                contentType: false,
                data: formData,
                dataType: "json",
                success: function (respons) {
                    if (respons.error) {
                        alert(respons.error);

                        //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                        return false;
                    } else {
                        loadingAnimation.remove();
                        modal.hide()
                        getUsers();
                    }
                },
                beforeSend: function () {
                    $(".modal-content", modalEl).prepend(loadingAnimation)
                },
            });
        });

        /*
         * Add new forms Stop
         */
//---------------------
        /*
         * Update forms Start
         */
        $('form[name="updateSlideForm"]').submit(function (event) {
            event.preventDefault()
            var formData = new FormData(this)
            formData.append('functionName', "updateSlide")

            var modalEl = document.getElementById('updateSlideModal')
            var modal = bootstrap.Modal.getInstance(modalEl)
            $.ajax({
                method: "POST",
                url: "ajax.php",
                processData: false,
                contentType: false,
                data: formData,
                dataType: "json",
                success: function (respons) {
                    if (respons.error) {
                        alert(respons.error);

                        //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                        return false;
                    } else {
                        loadingAnimation.remove();
                        modal.hide()
                        getSlides();
                    }
                },
                beforeSend: function () {
                    $(".modal-content", modalEl).prepend(loadingAnimation)
                },
            });
        });
        $('form[name="updateAnnouncementForm"]').submit(function (event) {
            event.preventDefault()
            var formData = new FormData(this)
            formData.append('functionName', "updateAnnouncement")

            var modalEl = document.getElementById('updateAnnouncementModal')
            var modal = bootstrap.Modal.getInstance(modalEl)
            $.ajax({
                method: "POST",
                url: "ajax.php",
                processData: false,
                contentType: false,
                data: formData,
                dataType: "json",
                success: function (respons) {
                    if (respons.error) {
                        alert(respons.error);

                        //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                        return false;
                    } else {
                        loadingAnimation.remove();
                        modal.hide()
                        getAnnouncment();
                    }
                },
                beforeSend: function () {
                    $(".modal-content", modalEl).prepend(loadingAnimation)
                },
            });
        });
        $('form[name="updateUserForm"]').submit(function (event) {
            event.preventDefault()
            var formData = new FormData(this)
            formData.append('functionName', "updateUser")
            if (formData.get('password') !== formData.get("password2")) {
                alert("şifreler eşleşmiyor");
                return false;
            }
            var modalEl = document.getElementById('updateUserModal')
            var modal = bootstrap.Modal.getInstance(modalEl)
            $.ajax({
                method: "POST",
                url: "ajax.php",
                processData: false,
                contentType: false,
                data: formData,
                dataType: "json",
                success: function (respons) {
                    if (respons.error) {
                        alert(respons.error);
                        loadingAnimation.remove();
                        modal.hide()
                        getUsers();
                        //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                        return false;
                    } else {
                        loadingAnimation.remove();
                        modal.hide()
                        getUsers();
                    }
                },
                beforeSend: function () {
                    $(".modal-content", modalEl).prepend(loadingAnimation)
                },
            });
        });

        /*
         * Update forms End
         */
//---------------------
        /*
         *  Lists Start
         */

        function getAnnouncment() {
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    functionName: "getAnnouncementsList",
                },
                dataType: "json",
                beforeSend: function () {
                    if ($(".overlay").length < 1) {
                        $("#announcmentTable tbody").prepend(loadingAnimation)
                    }

                },
                success: function (respons) {
                    if (respons.error) {
                        return false;
                    } else {
                        loadingAnimation.remove();
                        outHTML = ""
                        respons.forEach(function (a, i) {
                            i++
                            var QR = a.qrCode == "" ? "" : "<img class='rounded-0' src=\"" + a.qrCode + "\">"
                            outHTML +=
                                '<tr>' +
                                '<td>' + i + '</td>' +
                                '<td>' + a.title + '</td>' +
                                '<td>' + a.content + '</td>' +
                                '<td>' + QR + '</td>' +
                                '<td>' + a.userFullName + '</td>' +
                                '<td>' + a.createdDate + '</td>' +
                                '<td>' + islemlerHTML({
                                    'deleteData': {
                                        'formName': "deleteAnnouncement-" + a.id,
                                        'fromId': "deleteAnnouncement-" + a.id,
                                        'deleteId': a.id
                                    },
                                    'updateData': {
                                        'modalName': 'updateAnnouncementModal',
                                        'id': a.id,
                                        'title': a.title,
                                        'content': a.content,
                                        'link': a.link
                                    }
                                }) + '</td>' +
                                '</tr>'

                        })
                        $("#announcmentTable tbody").html(outHTML)
                        newAnnouncementForm.trigger('reset');
                    }
                },
            }).done(function (e) {
                $('form[name*="deleteAnnouncement"]').each(function (index, value) {
                    value.addEventListener("submit", function (event) {
                        event.preventDefault();
                        var formData = new FormData(this)
                        formData.append('functionName', "deleteAnnouncement")
                        $.ajax({
                            method: "POST",
                            url: "ajax.php",
                            data: formData,
                            dataType: "json",
                            processData: false,
                            contentType: false,
                            success: function (respons) {
                                if (respons.error) {
                                    //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım

                                    return false;
                                } else {
                                    loadingAnimation.remove();
                                    getAnnouncment()
                                }
                            },
                            beforeSend: function () {
                                if (confirm("Silmek istediğinizden emin misiniz")) {
                                    $("#announcmentTable tbody").prepend(loadingAnimation)
                                } else return false;

                            },
                        });
                    })
                })
            });
        }

        function getSlides() {
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    functionName: "getSlidesList",
                },
                dataType: "json",
                beforeSend: function () {
                    if ($(".overlay").length < 1) {
                        $("#slidesTable tbody").prepend(loadingAnimation)
                    }
                },
                success: function (respons) {
                    if (respons.error) {
                        return false;
                    } else {
                        loadingAnimation.remove();
                        outHTML = ""
                        respons.forEach(function (slide, i) {
                            i++
                            var QR = slide.qrCode == "" ? "" : "<img class='rounded-0' src=\"" + slide.qrCode + "\">"
                            outHTML +=
                                '<tr>' +
                                '<td>' + i + '</td>' +
                                '<td>' + slide.title + '</td>' +
                                '<td>' + slide.content + '</td>' +
                                '<td> <img class="rounded-0" src="' + slide.image + '"></td>' +
                                '<td>' + QR + '</td>' +
                                '<td>' + slide.userFullName + '</td>' +
                                '<td>' + slide.createdDate + '</td>' +
                                '<td>' + islemlerHTML({
                                    'deleteData': {
                                        'formName': "deleteSlide-" + slide.id,
                                        'fromId': "deleteSlide-" + slide.id,
                                        'deleteId': slide.id
                                    },
                                    'updateData': {
                                        'modalName': 'updateSlideModal',
                                        'id': slide.id,
                                        'title': slide.title,
                                        'content': slide.content,
                                        'image': slide.image,
                                        'link': slide.link,
                                        'full-width': slide.fullWidth
                                    }
                                }) + '</td>' +
                                '</tr>'
                        })
                        $("#slidesTable tbody").html(outHTML)
                        newSlideForm.trigger('reset');
                    }
                },
            }).done(function (e) {
                $('form[name*="deleteSlide"]').each(function (index, value) {
                    value.addEventListener("submit", function (event) {
                        event.preventDefault();
                        var formData = new FormData(this)
                        formData.append('functionName', "deleteSlide")
                        $.ajax({
                            method: "POST",
                            url: "ajax.php",
                            data: formData,
                            dataType: "json",
                            processData: false,
                            contentType: false,
                            success: function (respons) {
                                if (respons.error) {
                                    //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım

                                    return false;
                                } else {
                                    loadingAnimation.remove();
                                    getSlides()
                                }
                            },
                            beforeSend: function () {
                                if (confirm("Silmek istediğinizden emin misiniz")) {
                                    $("#slidesTable tbody").prepend(loadingAnimation)
                                } else return false;

                            },
                        });
                    })
                })
            });
        }

        function getUsers() {
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    functionName: "getUsersList",
                },
                dataType: "json",
                beforeSend: function () {
                    if ($(".overlay").length < 1) {
                        $("#usersTable tbody").prepend(loadingAnimation)
                    }
                },
                success: function (respons) {
                    if (respons.error) {
                        return false;
                    } else {
                        loadingAnimation.remove();
                        outHTML = ""
                        respons.forEach(function (user, i) {
                            i++
                            outHTML +=
                                '<tr>' +
                                '<td>' + i + '</td>' +
                                '<td>' + user.userName + '</td>' +
                                '<td>' + user.mail + '</td>' +
                                '<td>' + user.name + '</td>' +
                                '<td>' + user.lastName + '</td>' +
                                '<td>' + user.createdDate + '</td>' +
                                '<td>' + islemlerHTML({
                                    'deleteData': {
                                        'formName': "deleteUser-" + user.id,
                                        'fromId': "deleteUser-" + user.id,
                                        'deleteId': user.id
                                    },
                                    'updateData': {// Burada input isimleri snake case olarak yazılmalı. Bu sayede sonradan camelcase e dönüştürülebilirler
                                        'modalName': "updateUserModal",
                                        'id': user.id,
                                        'user-name': user.userName,
                                        'mail': user.mail,
                                        'name': user.name,
                                        'last-name': user.lastName,
                                    }
                                }) + '</td>' +
                                '</tr>'
                        })
                        $("#usersTable tbody").html(outHTML)
                        newUserForm.trigger('reset');
                    }
                }
            }).done(function (e) {
                $('form[name*="deleteUser"]').each(function (index, value) {
                    value.addEventListener("submit", function (event) {
                        event.preventDefault();
                        var formData = new FormData(this)
                        formData.append('functionName', "deleteUser")
                        $.ajax({
                            method: "POST",
                            url: "ajax.php",
                            data: formData,
                            dataType: "json",
                            processData: false,
                            contentType: false,
                            success: function (respons) {
                                if (respons.error) {
                                    alert(respons.error)
                                    loadingAnimation.remove();
                                    getUsers()
                                    //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım

                                    return false;
                                } else {
                                    loadingAnimation.remove();
                                    getUsers()
                                }
                            },
                            beforeSend: function () {
                                if (confirm("Silmek istediğinizden emin misiniz")) {
                                    $("#usersTable tbody").prepend(loadingAnimation)
                                } else return false;

                            },
                        });
                    })
                })
            });
        }

        getSlides();
        getAnnouncment();
        getUsers();
        /*
         * Lists End
         */

        /**
         * Görsel yükleme Alanı
         */
        $('.dropify').dropify({
            messages: {
                'default': 'Dosyayı buraya sürükle bırak yada tıkla',
                'replace': 'Değiştirmek için tıkla yada dosyayı sürükle bırak ',
                'remove': 'Sil',
                'error': 'Bir hata oldu.'
            }
        });

        /**
         * Güncelleme modalı açıldığında tıklanan butondan alınan veriler modal içerisindeki forma eklenecek
         */
        var updateModals = $("[id^=update]")
        updateModals.on('show.bs.modal', function (event) {
            var modal = this;
            // Button that triggered the modal
            var button = event.relatedTarget
            var formData = {};

            /**
             * Modal açmak için kullanılan butonda bulunan data-bs-* öznitelikleri FormData nesnesine ekleniyor.
             * data-bs-* öz nitelik olarak yazılırken camelcase veriler lowercase olarak yazılıyor. Bu yüzden bu veriler snakecase olarak alınıp burada camelcase e dönüştürülüyor.
             */
            $.each(button.attributes, function (i, attr) {
                if (!([undefined, 'toggle', 'target'].includes(attr.name.split("data-bs-")[1]))) {

                    formData[jsConvert.toCamelCase(attr.name.split("data-bs-")[1])] = attr.value
                }

            })
            /**
             * FormData nesnesindeki veriler modal içerisindeki inputlara ekleniyor.
             */
            Object.entries(formData).forEach(entry => {
                const [k, v] = entry;
                var input = "";
                if (k === "fullWidth") {
                    if (v == 1) {
                        $('input[name="fullWidth"]', modal).attr('checked', 'checked');
                    }
                } else if (k !== "image") {
                    input = $('input[name="' + k + '"]', modal)[0];
                    if (input) {
                        input.value = v
                    }
                }

            });

        })

    }
});
