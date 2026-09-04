<?php
declare(strict_types=1);

namespace App\Admin;

require_once __DIR__ . "/../vendor/autoload.php";

use App\Config;
use App\Core\Session;

Session::start();

if (Session::isLoggedIn()) {
    header("Location: /admin/");
    exit;
}

$csrfToken = Session::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= Config::APP_NAME ?> | Giriş Yap</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
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
                    <div class="auth-form-light text-left py-5 px-4 px-sm-5 shadow-sm rounded">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary mb-1">🎓 <?= Config::APP_NAME ?></h2>
                            <p class="text-muted"><?= Config::APP_TAGLINE ?></p>
                        </div>
                        <h6 class="fw-light text-center mb-4">Yönetim paneline giriş yapınız.</h6>
                        <form id="loginForm" name="loginForm" class="pt-2" action="ajax.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-group mb-3">
                                <label for="userName" class="form-label small fw-bold">Kullanıcı Adı</label>
                                <input type="text" class="form-control form-control-lg" id="userName"
                                       name="userName"
                                       placeholder="Kullanıcı adınızı girin" required autocomplete="username">
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label small fw-bold">Şifre</label>
                                <input type="password" class="form-control form-control-lg" id="password"
                                       name="password"
                                       placeholder="Şifrenizi girin" required autocomplete="current-password">
                            </div>
                            <div id="loginError" class="text-danger small mb-3"></div>
                            <div class="mt-3">
                                <button type="submit" id="loginBtn" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn w-100">Giriş Yap</button>
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

<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.bundle.min.js"></script>
<script>
    $('#loginForm').submit(function(event){
        event.preventDefault();
        var errorDiv = document.getElementById("loginError");
        var btn = document.getElementById("loginBtn");
        errorDiv.textContent = "";

        var userName = document.getElementsByName("userName")[0].value.trim();
        var password = document.getElementsByName("password")[0].value;

        if (userName.length === 0 || password.length === 0) {
            errorDiv.textContent = "Lütfen kullanıcı adı ve şifrenizi giriniz.";
            return false;
        }

        var formData = new FormData(this);
        formData.append('functionName', 'login');
        
        btn.disabled = true;
        btn.textContent = "Giriş yapılıyor...";

        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (response) {
                btn.disabled = false;
                btn.textContent = "Giriş Yap";
                if (response.error) {
                    errorDiv.textContent = response.error;
                } else if (response.redirect) {
                    window.location.replace(response.redirect);
                } else {
                    window.location.replace("/admin/");
                }
            },
            error: function (xhr) {
                btn.disabled = false;
                btn.textContent = "Giriş Yap";
                var msg = "Giriş başarısız. Lütfen bilgilerinizi kontrol edin.";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                errorDiv.textContent = msg;
            }
        });
    });
</script>
</body>
</html>