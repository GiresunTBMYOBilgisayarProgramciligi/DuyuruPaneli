<?php
namespace App\Admin;

use App\SlideController;
use App\UsersControler;

require_once "../vendor/autoload.php";

if (!($user = (new UsersControler())->isLoggedIn())) {
    header("Location: /admin/loginView.php");
}
$slides = new SlideController();
//$user=new User(1);
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
    <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">

    <link rel="stylesheet" href="js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png"/>
    <link type="text/css" rel="stylesheet" href="vendors/pwstabs/jquery.pwstabs.min.css">

</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
            <div>
                <a class="navbar-brand brand-logo" href="index.php">
                    DuyuruPaneli
                </a>
                <a class="navbar-brand brand-logo-mini" href="index.php">
                    DP
                </a>
            </div>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-top">
            <ul class="navbar-nav">
                <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
                    <h1 class="welcome-text">Merhaba, <span
                                class="text-black fw-bold"><?= $user->getFullName() ?></span></h1>
                    <h3 class="welcome-sub-text">Tirebolu Mehmet Bayrak MYO Duyuru Paneli </h3>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                    <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="img-xs rounded-circle" src="images/faces/face8.jpg" alt="Profile image"> </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                        <div class="dropdown-header text-center">
                            <img class="img-md rounded-circle" src="images/faces/face8.jpg" alt="Profile image">
                            <p class="mb-1 mt-3 font-weight-semibold"><?= $user->getFullName() ?></p>
                            <p class="fw-light text-muted mb-0"><?= $user->mail ?></p>
                        </div>
                        <a class="dropdown-item"><i
                                    class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i>Profilim
                        </a>
                        <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Çıkış
                            Yap</a>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas">
                <span class="mdi mdi-menu"></span>
            </button>
        </div>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper justify-content-center">
        <!-- partial:partials/_sidebar.html -->

        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper duyuru_tabs bg-white">
                <div class="row">
                    <ul class="nav nav-tabs" id="duyuruTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="slide-tab" data-bs-toggle="tab" data-bs-target="#slideTabContent" type="button" role="tab" aria-controls="home" aria-selected="true">Slide</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="duyuru-tab" data-bs-toggle="tab" data-bs-target="#duyuruTabContent" type="button" role="tab" aria-controls="profile" aria-selected="false">Duyuru</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="kullanıcılar-tab" data-bs-toggle="tab" data-bs-target="#kullanıcılarTabContent" type="button" role="tab" aria-controls="contact" aria-selected="false">Kullanıcı</button>
                        </li>
                    </ul>
                    <div class="tab-content p-0 border-0" id="myTabContent">
                        <?php include_once "pages/SlideView.php"?>
                        <?php include_once "pages/DuyuruView.php"?>
                        <?php include_once "pages/KullanıcıView.php"?>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
            <!-- partial:partials/_footer.html -->
            <footer class="footer fixed-bottom">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Premium <a
                                href="https://www.bootstrapdash.com/" target="_blank">Bootstrap admin template</a> from BootstrapDash.</span>
                    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Copyright © 2021. All rights reserved.</span>
                </div>
            </footer>
            <!-- partial -->
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

<!-- plugins:js -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page -->


<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<script src="js/settings.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->
<script src="js/dashboard.js"></script>
<!-- End custom js for this page-->
</body>

</html>

