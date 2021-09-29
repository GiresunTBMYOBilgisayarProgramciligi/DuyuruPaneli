var form= $('form[name="newAnnouncementForm"]');
console.log(form)
$('form[name="newAnnouncementForm"]').submit(function (event){
    event.preventDefault()
    var loadingAnimation=$('' +
        '<div class="overlay">' +
        '   <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>' +
        '</div>')
    var modalEl = document.getElementById('newAnnouncementModal')
    var modal = bootstrap.Modal.getInstance(modalEl)
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: {
            ajaxData:{
                functionName:"saveAnnouncement",
                data:$('form[name="newAnnouncementForm"]').serializeArray()
            }},
        dataType:"json",
        success: function (respons) {
            if (respons.error){
                document.getElementById("loginError").innerHTML = respons.error+"<br>";
                return false;
            }
            loadingAnimation.remove();
            modal.hide()
        },
        beforeSend: function (){
            console.log("gönderim öncesi")
            $(modalEl," .modal-body").prepend(loadingAnimation)
        },
    });
});