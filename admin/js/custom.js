var form = $('form[name="newAnnouncementForm"]');
var loadingAnimation = $('' +
    '<div class="overlay">' +
    '   <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' +
    '</div>')
$('form[name="newAnnouncementForm"]').submit(function (event) {
    event.preventDefault()

    var modalEl = document.getElementById('newAnnouncementModal')
    var modal = bootstrap.Modal.getInstance(modalEl)
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: {
            ajaxData: {
                functionName: "saveAnnouncement",
                data: $('form[name="newAnnouncementForm"]').serializeArray()
            }
        },
        dataType: "json",
        success: function (respons) {
            if (respons.error) {
                //todo hatlar yazılacak form denetimi olarak sadece boş bırakılamaz işlemini html5 ile yaptım
                return false;
            } else {
                loadingAnimation.remove();
                modal.hide()
                getAnnouncment();
            }
        },
        beforeSend: function () {
            console.log("gönderim öncesi")
            $(modalEl, " .modal-body").prepend(loadingAnimation)
        },
    });
});

function getAnnouncment() {
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: {
            ajaxData: {
                functionName: "getAnnouncementList",
            }
        },
        dataType: "json",
        success: function (respons) {
            if (respons.error) {
                document.getElementById("loginError").innerHTML = respons.error + "<br>";
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
                            '<td>' + a.content + '</td>'+
                            '<td>' + QR+ '</td>' +
                            '<td>' + a.userFullName + '</td>' +
                            '<td>' + a.createdDate + '</td>' +
                        '</tr>'
                })
                $("#announcmentTable tbody").html(outHTML)
            }
        },
        beforeSend: function () {
            $("#announcmentTable tbody").prepend(loadingAnimation)
        },
    });
}

$(function () {
    getAnnouncment();
});