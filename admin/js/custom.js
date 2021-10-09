$(function () {
    var newAnnouncementForm = $('form[name="newAnnouncementForm"]');
    var newSlideForm = $('form[name="newSlideForm"]');
    var newUserForm = $('form[name="newUserForm"]');
    var loadingAnimation = $('' +
        '<div class="overlay">' +
        '   <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' +
        '</div>')

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
                    //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım

                    return false;
                } else {
                    console.log("Duyuru yaptım")
                    loadingAnimation.remove();
                    modal.hide()
                    getAnnouncment();
                }
            },
            beforeSend: function () {
                $(modalEl, " .modal-body").prepend(loadingAnimation)
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

                    //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                    return false;
                } else {
                    loadingAnimation.remove();
                    modal.hide()
                    getSlides();
                }
            },
            beforeSend: function () {
                $(modalEl, " .modal-body").prepend(loadingAnimation)
            },
        });
    });

    function getAnnouncment() {
        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: {
                functionName: "getAnnouncementsList",
            },
            dataType: "json",
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
                                    'qrCode': a.qrCode
                                }
                            }) + '</td>' +
                            '</tr>'

                    })
                    $("#announcmentTable tbody").html(outHTML)
                    newAnnouncementForm.trigger('reset');
                }
            },
            beforeSend: function () {
                $("#announcmentTable tbody").prepend(loadingAnimation)
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
        //todo sayfa ilk yüklendiğinde çalıştırıldığında loading animasyonu çalışmıyor. çözemedim
        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: {
                functionName: "getSlidesList",
            },
            dataType: "json",
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
                                    'qrCode': slide.qrCode,
                                    'fullWidth': slide.fullWidth
                                }
                            }) + '</td>' +
                            '</tr>'
                    })
                    $("#slidesTable tbody").html(outHTML)
                    newSlideForm.trigger('reset');
                }
            },
            beforeSend: function () {
                $("#slidesTable tbody").prepend(loadingAnimation)
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
                            '<td>' + a.userName + '</td>' +
                            '<td>' + a.mail + '</td>' +
                            '<td>' + a.name + '</td>' +
                            '<td>' + a.lastName + '</td>' +
                            '<td>' + a.profilPicture + '</td>' +
                            '<td>' + a.createdDate + '</td>' +
                            '<td>' + islemlerHTML({
                                'deleteData': {
                                    'formName': "deleteUser-" + a.id,
                                    'fromId': "deleteUser-" + a.id,
                                    'deleteId': a.id
                                },
                                'updateData':{
                                    'modalName':"updateUserModal",
                                    'id':a.id,
                                    'userName':a.userName,
                                    'mail':a.mail,
                                    'name':a.name,
                                    'lastName':a.lastName,
                                    'profilPicture':a.profilPicture
                                }
                            }) + '</td>' +
                            '</tr>'
                    })
                    $("#usersTable tbody").html(outHTML)
                    newUserForm.trigger('reset');
                }
            },
            beforeSend: function () {
                $("#usersTable tbody").prepend(loadingAnimation)
            },
        });
    }


    getAnnouncment();
    getSlides();
    getUsers();
    $('.dropify').dropify({
        messages: {
            'default': 'Dosyayı buraya sürükle bırak yada tıkla',
            'replace': 'Değiştirmek için tıkla yada dosyayı sürükle bırak ',
            'remove': 'Sil',
            'error': 'Bir hata oldu.'
        }
    });
    /**
     * Güncelleme modalı açıldığında tıklanan butondan alınan veriler mofal içerisindeki forma eklenecek
     */
    var updateModals = $("[id^=update]")
    updateModals.on('show.bs.modal', function (event) {
        var modal = this;
        // Button that triggered the modal
        var button = event.relatedTarget
        var formData = {};
        $.each(button.attributes, function (i, attr) {
            if (!([undefined, 'toggle', 'target'].includes(attr.name.split("data-bs-")[1]))) {
                formData[attr.name.split("data-bs-")[1]] = attr.value

            }

        })
        Object.entries(formData).forEach(entry => {
            const [k, v] = entry;
            var input = "";
            if (k === "fullwidth") {//veriyi jafascript ile eklediğim için html içeriğine w küçük olarak yazılıyor.
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

});
