<?php
declare(strict_types=1);

namespace App\Admin;

require_once __DIR__ . "/../vendor/autoload.php";

use App\Config;
use App\Core\Session;
use App\Services\AuthService;

Session::start();
$authService = new AuthService();
$currentUser = $authService->getCurrentUser();

if (!$currentUser) {
    header("Location: /admin/loginView.php");
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
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= Config::APP_NAME ?> | Yönetim Paneli</title>
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
    <!-- Navbar -->
    <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
            <div>
                <a class="navbar-brand brand-logo fw-bold text-primary" href="/">
                    🎓 <?= Config::APP_NAME ?>
                </a>
                <a class="navbar-brand brand-logo-mini fw-bold text-primary" href="/">
                    UP
                </a>
            </div>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-top">
            <ul class="navbar-nav">
                <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
                    <h1 class="welcome-text">Merhaba, <span
                                class="text-black fw-bold"><?= htmlspecialchars($currentUser->name . ' ' . $currentUser->lastName, ENT_QUOTES, 'UTF-8') ?></span></h1>
                    <h3 class="welcome-sub-text"><?= Config::APP_NAME ?> - <?= Config::APP_TAGLINE ?></h3>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                    <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="img-xs rounded-circle" src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($currentUser->mail))) ?>?d=mp&s=80" alt="Profile image"> </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                        <div class="dropdown-header text-center">
                            <img class="img-md rounded-circle" src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($currentUser->mail))) ?>?d=mp&s=80" alt="Profile image">
                            <p class="mb-1 mt-3 font-weight-semibold"><?= htmlspecialchars($currentUser->name . ' ' . $currentUser->lastName, ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="fw-light text-muted mb-0"><?= htmlspecialchars($currentUser->mail, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <a class="dropdown-item" id="logoutBtn" style="cursor: pointer;">
                            <i class="dropdown-item-icon mdi mdi-power text-danger me-2"></i>Çıkış Yap
                        </a>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas">
                <span class="mdi mdi-menu"></span>
            </button>
        </div>
    </nav>
    <div class="container-fluid page-body-wrapper justify-content-center">
        <div class="main-panel pb-5">
            <div class="content-wrapper duyuru_tabs bg-white">
                <div class="row">
                    <ul class="nav nav-tabs" id="duyuruTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="slide-tab" data-bs-toggle="tab" data-bs-target="#slideTabContent" type="button" role="tab" aria-controls="home" aria-selected="true">Slayt Yönetimi</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="duyuru-tab" data-bs-toggle="tab" data-bs-target="#duyuruTabContent" type="button" role="tab" aria-controls="profile" aria-selected="false">Duyuru Yönetimi</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="kullanıcılar-tab" data-bs-toggle="tab" data-bs-target="#kullanıcılarTabContent" type="button" role="tab" aria-controls="contact" aria-selected="false">Kullanıcı Yönetimi</button>
                        </li>
                    </ul>
                    <div class="tab-content p-0 border-0" id="myTabContent">
                        <?php include_once __DIR__ . "/pages/SlidesView.php"; ?>
                        <?php include_once __DIR__ . "/pages/AnnouncementsView.php"; ?>
                        <?php include_once __DIR__ . "/pages/UsersView.php"; ?>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
            <footer class="footer fixed-bottom">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
                        <strong><?= Config::APP_NAME ?></strong> v<?= Config::APP_VERSION ?> — <?= Config::APP_TAGLINE ?>
                    </span>
                    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Tüm hakları saklıdır.</span>
                </div>
            </footer>
        </div>
    </div>
</div>

<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.bundle.min.js"></script>
<script src="js/custom.js"></script>
<script>
    // Global CSRF Token Injection
    window.UNIPANO_CSRF = "<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>";
    
    // Çıkış yapma tetikleyicisi
    document.getElementById('logoutBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'ajax.php';
        
        var fn = document.createElement('input');
        fn.type = 'hidden';
        fn.name = 'functionName';
        fn.value = 'logout';
        form.appendChild(fn);
        
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = window.UNIPANO_CSRF;
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    });
</script>
</body>
</html>