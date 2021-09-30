var newAnnouncementForm = $('form[name="newAnnouncementForm"]');
var newSlideForm = $('form[name="newSlideForm"]');
var newUserForm = $('form[name="newUserForm"]');
var loadingAnimation = $('' +
    '<div class="overlay">' +
    '   <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' +
    '</div>')
var islemlerDrop = $('<div class="btn-group">\n' +
    '                            <button type="button" class="btn dropdown-toggle btn-primary" data-bs-toggle="dropdown" aria-expanded="false">İşlem Seç</button>\n' +
    '                            <div class="dropdown-menu" style="">\n' +
    '                                <a class="dropdown-item ">Sil</a>\n' +
    '                                <a class="dropdown-item">Güncelle</a>\n' +
    '                            </div>    \n' +
    '                        </div>')


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
                        '<td>' + islemlerDrop.html() + '</td>' +
                        '</tr>'

                })
                $("#announcmentTable tbody").html(outHTML)
                newAnnouncementForm.trigger('reset');
            }
        },
        beforeSend: function () {
            $("#announcmentTable tbody").prepend(loadingAnimation)
        },
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
                        '<td> <img class="rounded-0" src="' + a.image + '"></td>' +
                        '<td>' + QR + '</td>' +
                        '<td>' + a.userFullName + '</td>' +
                        '<td>' + a.createdDate + '</td>' +
                        '<td>' + islemlerDrop.html() + '</td>' +
                        '</tr>'
                })
                $("#slidesTable tbody").html(outHTML)
                newSlideForm.trigger('reset');
            }
        },
        beforeSend: function () {
            $("#slidesTable tbody").prepend(loadingAnimation)
        },
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
                        '<td>' + islemlerDrop.html() + '</td>' +
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

$(function () {
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
});
