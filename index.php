<?php
declare(strict_types=1);

namespace App;

setlocale(LC_ALL, 'tr_TR.UTF-8');
require_once __DIR__ . '/vendor/autoload.php';

use App\Config;
use App\Repositories\SlideRepository;

$slideRepo = new SlideRepository();
$slides = $slideRepo->getAll();
?>
<!doctype html>
<html lang="tr">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS & Tema -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/breaking-news-ticker.css">

    <title><?= Config::APP_NAME ?> | <?= Config::APP_TAGLINE ?></title>

    <style>
        body {
            background-color: #f8fafc;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .header-container {
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .logo-img {
            max-height: 70px;
            width: auto;
        }
        .campus-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .campus-sub {
            font-size: 0.95rem;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }
        .carousel-item img {
            max-height: 72vh !important;
            object-fit: contain;
            margin: 0 auto;
        }
        .carousel-caption {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            border-radius: 8px;
            padding: 12px 24px;
            bottom: 20px;
        }
        .weather-col {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .badge-live {
            background: #ef4444;
            color: #ffffff;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 700;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.4; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
<main class="d-flex flex-column vh-100 justify-content-between">
    <!-- Üst Başlık ve Kurumsal Bilgi Bandı -->
    <header class="header-container">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="images/logo_230x230.png" alt="Logo" class="logo-img">
                </div>
                <div class="col text-center">
                    <h1 class="campus-title">🎓 <?= Config::APP_NAME ?></h1>
                    <p class="campus-sub"><?= Config::APP_TAGLINE ?></p>
                </div>
                <div class="col-auto weather-col">
                    <span class="badge-live me-3">CANLI YAYIN</span>
                    <!-- Hava Durumu Widget -->
                    <div style="width: 220px; height: 75px;">
                        <a class="weatherwidget-io" href="https://forecast7.com/tr/41d0138d81/tirebolu/"
                           data-icons="Climacons Animated" data-mode="Current" data-days="3" data-theme="pure">Hava Durumu</a>
                        <script>
                            !function (d, s, id) {
                                var js, fjs = d.getElementsByTagName(s)[0];
                                if (!d.getElementById(id)) {
                                    js = d.createElement(s);
                                    js.id = id;
                                    js.src = 'https://weatherwidget.io/js/widget.min.js';
                                    fjs.parentNode.insertBefore(js, fjs);
                                }
                            }(document, 'script', 'weatherwidget-io-js');
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Ana Afiş & Slayt Alanı (Flicker-Free Carousel) -->
    <div class="container-fluid flex-grow-1 d-flex align-items-center justify-content-center p-0 my-2">
        <div class="w-100">
            <div id="afisler" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="25000">
                <div class="carousel-inner" id="carouselContent">
                    <?php if (count($slides) > 0): ?>
                        <?php foreach ($slides as $idx => $slide): ?>
                            <?php 
                                $activeClass = ($idx === 0) ? 'active' : '';
                                $fullWidthClass = (!empty($slide->fullWidth)) ? 'w-100' : '';
                            ?>
                            <div class="carousel-item <?= $activeClass ?>">
                                <img src="<?= htmlspecialchars($slide->image, ENT_QUOTES, 'UTF-8') ?>" class="d-block <?= $fullWidthClass ?>" alt="<?= htmlspecialchars($slide->title, ENT_QUOTES, 'UTF-8') ?>">
                                <?php if (!empty($slide->title) || !empty($slide->content)): ?>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($slide->title, ENT_QUOTES, 'UTF-8') ?></h4>
                                        <p class="mb-0"><?= htmlspecialchars($slide->content, ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="carousel-item active">
                            <div class="d-flex align-items-center justify-content-center" style="height: 60vh;">
                                <div class="text-center">
                                    <h3 class="text-muted fw-bold">🎓 <?= Config::APP_NAME ?></h3>
                                    <p class="text-secondary">Şu anda gösterilecek aktif bir afiş bulunmamaktadır.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <a class="carousel-control-prev" href="#afisler" role="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Önceki</span>
                </a>
                <a class="carousel-control-next" href="#afisler" role="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sonraki</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alt Kayan Duyuru Bandı -->
    <div class="breaking-news-ticker" id="lastAnnouncements">
        <div class="bn-label">Son Duyurular</div>
        <div class="bn-news">
            <ul>
                <li><span class="bn-loader-text">Duyurular yükleniyor...</span></li>
            </ul>
        </div>
    </div>
</main>

<!-- Scriptler -->
<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/breaking-news-ticker.min.js"></script>

<script>
    // Kayan Haber Ticker'ı Başlat
    function initTicker() {
        $('#lastAnnouncements').breakingNews({
            borderWidth: 0,
            height: 55,
            themeColor: '#1e3a8a',
            scrollSpeed: 0.5,
            effect: 'slide-down',
            delayTimer: 8000,
            source: {
                type: 'json',
                url: 'admin/ajax.php?action=getAnnouncementJSON',
                limit: 15,
                showingField: 'duyuru',
                linkEnabled: false,
                target: '_blank',
                seperator: '<span class="bn-seperator" style="background-image:url(images/logo_230x230.png);"></span>',
                withPrefix: true,
                errorMsg: 'Duyurular yüklenemedi.'
            }
        });
    }

    $(document).ready(function () {
        initTicker();

        // -------------------------------------------------------------
        // Kırpışmasız Arka Plan Güncelleme İzleyicisi (Flicker-Free Watchdog)
        // -------------------------------------------------------------
        var lastContentHash = "";

        function checkUpdates() {
            $.ajax({
                url: 'admin/ajax.php',
                data: { action: 'getKioskData' },
                dataType: 'json',
                success: function (res) {
                    if (!res || !res.hash) return;

                    // Eğer veriler aynıysa DOM'a hiç dokunma (sıfır titreme)
                    if (lastContentHash === "") {
                        lastContentHash = res.hash;
                        localStorage.setItem('unipano_cache', JSON.stringify(res));
                        return;
                    }

                    if (res.hash !== lastContentHash) {
                        console.log("UniPano: Yeni afiş veya duyuru algılandı, arka planda güncelleniyor...");
                        lastContentHash = res.hash;
                        localStorage.setItem('unipano_cache', JSON.stringify(res));

                        // Slaytları yumuşakça yeniden çiz
                        if (res.slides && res.slides.length > 0) {
                            var html = "";
                            res.slides.forEach(function (slide, idx) {
                                var active = idx === 0 ? "active" : "";
                                var w = slide.fullWidth == 1 ? "w-100" : "";
                                var caption = "";
                                if (slide.title || slide.content) {
                                    caption = '<div class="carousel-caption d-none d-md-block">' +
                                        '<h4 class="fw-bold mb-1">' + (slide.title || '') + '</h4>' +
                                        '<p class="mb-0">' + (slide.content || '') + '</p>' +
                                    '</div>';
                                }
                                html += '<div class="carousel-item ' + active + '">' +
                                    '<img src="' + slide.image + '" class="d-block ' + w + '" alt="">' +
                                    caption +
                                '</div>';
                            });
                            $("#carouselContent").html(html);
                        }

                        // Kayan duyuruyu yeniden başlat
                        initTicker();
                    }
                },
                error: function () {
                    // Çevrimdışı durumu: LocalStorage önbelleği devrede kalır
                    console.warn("UniPano: Sunucu bağlantısı kurulamadı. Çevrimdışı modda devam ediliyor.");
                }
            });
        }

        // Her 20 saniyede bir arka planda kontrol et
        setInterval(checkUpdates, 20000);
    });
</script>
</body>
</html>
