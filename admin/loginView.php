<?php
namespace App\Admin;

require_once "../vendor/autoload.php";

use App\UsersController;

$users = new UsersController();

if ($users->isLoggedIn()) {
    header("Location:/admin/");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tirebolu MBMYO Duyuru Paneli </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="node_modules/dropify/dist/css/dropify.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png"/>

    <link rel="stylesheet" href="css/custom.css">
</head>

<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
            <div class="row w-100 mx-0">
                <div class="col-lg-4 mx-auto">
                    <div class="auth-form-light text-left py-5 px-4 px-sm-5">

                        <h4 class="text-center">Tirebolu Mehmet Bayrak MYO</h4>
                        <h6 class="fw-light text-center">Giriş yapın.</h6>
                        <form id="loginForm" name="loginForm" class="pt-3" action="ajax.php" method="post">
                            <div class="form-group">
                                <input type="userName" class="form-control form-control-lg" id="userName"
                                       name="userName"
                                       placeholder="Kullanıcı Adınız">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-lg" id="password"
                                       name="password"
                                       placeholder="Şifre">
                            </div>
                            <div id="loginError" class="text-danger">

                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">Giriş Yap</button>
                            </div>
                            <div class="my-2 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <label class="form-check-label text-muted">
                                        <input type="checkbox" class="form-check-input">
                                        Beni hatırla
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->
<script src="js/bundle.js"></script>
<script>
    $('#loginForm').submit(function(event){
        event.preventDefault();
        let userName = document.getElementsByName("userName")[0].value;
        let password = document.getElementsByName("password")[0].value;
        if (userName.length === 0 || password.length === 0) {
            document.getElementById("loginError").innerHTML = "Lütfen bilgileri girin.<br>";
        } else {
            var formData =new FormData(this)
            formData.append('functionName','login')
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: formData,
                dataType:"json",
                processData: false,
                contentType: false,
                success: function (respons) {
                    if (respons.error){
                        document.getElementById("loginError").innerHTML = respons.error+"<br>";
                        return false;
                    }else window.location.replace("/admin")
                }
            });
        }
    })

</script>



</body>

</html>