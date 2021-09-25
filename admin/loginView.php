<?php
namespace App\Admin;

require_once "../vendor/autoload.php";

use App\UsersControler;

$users = new UsersControler();

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
    <title>Star Admin2 </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/typicons/typicons.css">
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png"/>
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
                        <form class="pt-3" action="login.php" method="post">
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
                                <a class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn"
                                   href="#" onclick="ajaxLogin()">Giriş Yap</a>
                            </div>
                            <div class="my-2 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <label class="form-check-label text-muted">
                                        <input type="checkbox" class="form-check-input">
                                        Beni hatırla
                                    </label>
                                </div>
                                <a href="#" class="auth-link text-black">Forgot password?</a>
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
<script>
    function ajaxLogin(str) {
        let userName = document.getElementsByName("userName")[0].value;
        let password = document.getElementsByName("password")[0].value;
        if (userName.length === 0 || password.length === 0) {
            document.getElementById("loginError").innerHTML = "Lütfen bilgileri girin.<br>";
        } else {
            const xmlhttp = new XMLHttpRequest();
            xmlhttp.onload = function () {
                let respons = JSON.parse(this.responseText)
                if (respons.error){
                    document.getElementById("loginError").innerHTML = respons.error+"<br>";
                }else window.location.replace("/admin")
            }
            xmlhttp.open("POST", "login.php");
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("userName=" + userName + "&password=" + password);
        }
    }
</script>
<!-- plugins:js -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<script src="vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<script src="js/settings.js"></script>
<script src="js/todolist.js"></script>
<!-- endinject -->
</body>

</html>