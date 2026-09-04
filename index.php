<?php
declare(strict_types=1);

namespace App;

date_default_timezone_set('Europe/Istanbul');
setlocale(LC_ALL, 'tr_TR.UTF-8');
require_once __DIR__ . '/vendor/autoload.php';

use App\Config;
use App\Repositories\SlideRepository;
use App\Repositories\AnnouncementRepository;
use App\Services\WeatherService;
use DateTime;

$slideRepo = new SlideRepository();
$slides = $slideRepo->getAll();

$announcementRepo = new AnnouncementRepository();
$announcements = $announcementRepo->getAll();

$weatherService = new WeatherService();
$weather = $weatherService->getCurrentWeather();

$initialTickerData = [];
foreach ($announcements as $announcement) {
    $dateString = '';
    if (!empty($announcement->createdDate)) {
        $dt = DateTime::createFromFormat('Y.m.d H:i:s', $announcement->createdDate);
        $dateString = $dt ? $dt->format('d.m.Y') : $announcement->createdDate;
    }

    $prefix = !empty($announcement->title) ? $announcement->title : $dateString;
    $initialTickerData[] = [
        'id' => $announcement->id,
        'prefix' => $prefix,
        'duyuru' => $announcement->content,
        'qrCode' => $announcement->qrCode ?? '',
        'link' => $announcement->link ?? ''
    ];
}

$turkishMonths = [
    1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
    7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
];
$turkishDays = [
    'Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba',
    'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar'
];
$now = new DateTime('now', new \DateTimeZone('Europe/Istanbul'));
$initialTime = $now->format('H:i:s');
$monthName = $turkishMonths[(int)$now->format('n')] ?? '';
$dayName = $turkishDays[$now->format('l')] ?? '';
$initialDate = $now->format('j') . ' ' . $monthName . ' ' . $now->format('Y') . ', ' . $dayName;

$firstAnnouncement = $initialTickerData[0] ?? null;
$firstPrefix = $firstAnnouncement['prefix'] ?? 'DUYURULAR';
$firstDuyuru = $firstAnnouncement['duyuru'] ?? 'Güncel duyuru bulunmamaktadır.';
$firstQr = $firstAnnouncement['qrCode'] ?? '';
$hasFirstQr = !empty(trim($firstQr));

$initialContentHash = md5(json_encode($slides) . json_encode($initialTickerData));
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars(Config::INSTITUTION_NAME, ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap 5 CSS & Özel UniPano Kiosk Teması -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/kiosk.css">
</head>
<body>

<div class="kiosk-wrapper">
    <!-- ====================================================================
         1. MODÜLER ÜST BAŞLIK BANDI (KUSURSUZ 1fr auto 1fr ORTALAMA)
         ==================================================================== -->
    <header class="kiosk-header">
        <!-- Sol Modül: Kurum & Kampüs / Birim Bilgisi -->
        <div class="header-left">
            <img src="<?= htmlspecialchars(Config::LOGO_PATH, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="header-logo">
            <div class="institution-meta">
                <span class="institution-name"><?= htmlspecialchars(Config::INSTITUTION_NAME, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="campus-name"><?= htmlspecialchars(Config::CAMPUS_NAME, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <!-- Orta Modül: Matematiksel Olarak %50 Ekran Merkezinde UniPano Başlığı -->
        <div class="header-center">
            <div class="panel-brand">
                <span class="brand-icon">🎓</span>
                <span class="brand-title"><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <span class="brand-tagline"><?= htmlspecialchars(Config::APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <!-- Sağ Modül: Canlı Tarih & Saat + Özel Yerel Hava Durumu -->
        <div class="header-right">
            <?php if (Config::MODULE_CLOCK): ?>
            <!-- Dijital Saat & Tarih -->
            <div class="clock-module">
                <div class="clock-time" id="digitalClock"><?= htmlspecialchars($initialTime, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="clock-date" id="digitalDate"><?= htmlspecialchars($initialDate, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endif; ?>

            <?php if (Config::MODULE_WEATHER && !empty($weather['enabled'])): ?>
            <!-- Özel Yerel Hava Durumu Widget'ı (Open-Meteo & SVG) -->
            <div class="weather-module" id="weatherWidget">
                <div class="weather-icon-wrapper" id="weatherIcon">
                    <?= $weather['iconSvg'] ?? '' ?>
                </div>
                <div class="weather-info">
                    <div class="weather-temp-row">
                        <span class="weather-temp" id="weatherTemp"><?= htmlspecialchars($weather['temperatureFormatted'] ?? '--°C', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="weather-city"><?= htmlspecialchars(Config::WEATHER_CITY, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="weather-condition" id="weatherCondition"><?= htmlspecialchars($weather['condition'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- ====================================================================
         2. ORTA SAHNE & KIRPIŞMASIZ CAROUSEL (1024x768 & HD/4K DİNAMİK ORAN)
         ==================================================================== -->
    <main class="kiosk-stage">
        <div id="kioskCarousel" class="kiosk-carousel carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="<?= Config::SLIDE_INTERVAL_MS ?>">
            <div class="carousel-inner" id="carouselContent">
                <?php if (count($slides) > 0): ?>
                    <?php foreach ($slides as $idx => $slide): ?>
                        <?php 
                            $activeClass = ($idx === 0) ? 'active' : '';
                            $fullWidthClass = (!empty($slide->fullWidth)) ? 'full-width' : '';
                        ?>
                        <div class="carousel-item <?= $activeClass ?>">
                            <div class="slide-image-wrapper">
                                <img src="<?= htmlspecialchars($slide->image, ENT_QUOTES, 'UTF-8') ?>" class="slide-image <?= $fullWidthClass ?>" alt="<?= htmlspecialchars($slide->title ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <?php if (!empty($slide->title) || !empty($slide->content)): ?>
                                <div class="slide-caption-card">
                                    <?php if (!empty($slide->title)): ?>
                                        <h3 class="slide-caption-title"><?= htmlspecialchars($slide->title, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <?php endif; ?>
                                    <?php if (!empty($slide->content)): ?>
                                        <p class="slide-caption-content"><?= htmlspecialchars($slide->content, ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="carousel-item active">
                        <div class="empty-stage-card">
                            <div class="empty-icon">🎓</div>
                            <h2 class="empty-title"><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="empty-desc">Şu anda yayında aktif bir görsel veya afiş bulunmamaktadır.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- ====================================================================
         3. MODÜLER ALT DUYURU BANDI (BROADCAST LOWER-THIRD TICKER - 100vw)
         ==================================================================== -->
    <?php if (Config::MODULE_TICKER): ?>
    <footer class="kiosk-footer" id="kioskFooter">
        <!-- Sol Etiket -->
        <div class="footer-badge">
            <span class="badge-pulse-dot"></span>
            <span>DUYURULAR</span>
        </div>

        <!-- Orta Duyuru Metin Alanı -->
        <div class="footer-announcement-area">
            <div class="announcement-item-view" id="announcementContainer">
                <span class="announcement-prefix-badge" id="announcementBadge"><?= htmlspecialchars($firstPrefix, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="announcement-body-text" id="announcementText"><?= htmlspecialchars($firstDuyuru, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <!-- Sağ QR Kod Okutma Kartı -->
        <div class="footer-qr-card" id="footerQrCard" style="<?= $hasFirstQr ? 'display: flex;' : 'display: none;' ?>">
            <div class="qr-callout-text">
                <span class="qr-callout-primary">DETAYLAR İÇİN</span>
                <span class="qr-callout-secondary">TELEFONLA OKUTUN</span>
            </div>
            <div class="qr-canvas-box" id="footerQrCode">
                <?= $firstQr ?>
            </div>
        </div>
    </footer>
    <?php endif; ?>
</div>

<!-- Kütüphaneler -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>

<script>
    (function () {
        'use strict';

        // -------------------------------------------------------------
        // 1. Canlı Tarih ve Dijital Saat Motoru
        // -------------------------------------------------------------
        const clockEl = document.getElementById('digitalClock');
        const dateEl = document.getElementById('digitalDate');

        function updateClock() {
            const now = new Date();
            if (clockEl) {
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds}`;
            }

            if (dateEl) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateEl.textContent = now.toLocaleDateString('tr-TR', options);
            }
        }

        if (clockEl || dateEl) {
            updateClock();
            setInterval(updateClock, 1000);
        }

        // -------------------------------------------------------------
        // 2. Modüler Duyuru Rotasyonu ve Senkronize QR Kartı
        // -------------------------------------------------------------
        let announcementsData = <?= json_encode($initialTickerData, JSON_UNESCAPED_UNICODE) ?>;
        let currentAnnouncementIndex = 0;
        let announcementTimer = null;

        const badgeEl = document.getElementById('announcementBadge');
        const textEl = document.getElementById('announcementText');
        const qrCardEl = document.getElementById('footerQrCard');
        const qrCodeBoxEl = document.getElementById('footerQrCode');
        const containerEl = document.getElementById('announcementContainer');

        function displayAnnouncement(index) {
            if (!announcementsData || announcementsData.length === 0) {
                if (badgeEl) badgeEl.textContent = "BİLGİ";
                if (textEl) textEl.textContent = "Şu anda yayında aktif bir duyuru bulunmamaktadır.";
                if (qrCardEl) qrCardEl.style.display = "none";
                return;
            }

            const item = announcementsData[index % announcementsData.length];

            // Akıcı geçiş efekti
            if (containerEl) {
                containerEl.style.animation = 'none';
                containerEl.offsetHeight; /* reflow */
                containerEl.style.animation = 'slideUpFade 0.4s ease-out';
            }

            if (badgeEl) badgeEl.textContent = item.prefix || "DUYURU";
            if (textEl) textEl.textContent = item.duyuru || "";

            // QR Kod Senkronizasyonu
            if (qrCardEl && qrCodeBoxEl) {
                if (item.qrCode && item.qrCode.trim() !== '') {
                    qrCodeBoxEl.innerHTML = item.qrCode;
                    qrCardEl.style.display = "flex";
                } else {
                    qrCardEl.style.display = "none";
                }
            }
        }

        function startAnnouncementRotation() {
            if (announcementTimer) clearInterval(announcementTimer);
            if (!announcementsData || announcementsData.length === 0) {
                displayAnnouncement(0);
                return;
            }

            displayAnnouncement(currentAnnouncementIndex);

            // Her 8 saniyede bir sonraki duyuruya geç
            announcementTimer = setInterval(function () {
                currentAnnouncementIndex = (currentAnnouncementIndex + 1) % announcementsData.length;
                displayAnnouncement(currentAnnouncementIndex);
            }, 8000);
        }

        startAnnouncementRotation();

        // -------------------------------------------------------------
        // 3. Kırpışmasız Arka Plan İzleyicisi (Flicker-Free Watchdog)
        // -------------------------------------------------------------
        let lastContentHash = "<?= $initialContentHash ?>";

        function checkKioskUpdates() {
            $.ajax({
                url: 'admin/ajax.php',
                data: { action: 'getKioskData' },
                dataType: 'json',
                timeout: 8000,
                success: function (res) {
                    if (!res || !res.hash) return;

                    // İlk çalıştırmada önbelleğe kaydet
                    localStorage.setItem('unipano_cache', JSON.stringify(res));

                    // Eğer içerik değiştiyse DOM'u kırpışmasız güncelle
                    if (res.hash !== lastContentHash) {
                        console.log("[UniPano] Yeni veri algılandı, arka planda güncelleniyor...");
                        lastContentHash = res.hash;

                        // Slaytları güncelle
                        if (res.slides && res.slides.length > 0) {
                            let slideHtml = "";
                            res.slides.forEach(function (slide, idx) {
                                const active = idx === 0 ? "active" : "";
                                const fullW = slide.fullWidth == 1 ? "full-width" : "";
                                let caption = "";
                                if (slide.title || slide.content) {
                                    caption = '<div class="slide-caption-card">' +
                                        (slide.title ? '<h3 class="slide-caption-title">' + $('<div>').text(slide.title).html() + '</h3>' : '') +
                                        (slide.content ? '<p class="slide-caption-content">' + $('<div>').text(slide.content).html() + '</p>' : '') +
                                        '</div>';
                                }
                                slideHtml += '<div class="carousel-item ' + active + '">' +
                                    '<div class="slide-image-wrapper">' +
                                    '<img src="' + slide.image + '" class="slide-image ' + fullW + '" alt="">' +
                                    '</div>' +
                                    caption +
                                    '</div>';
                            });
                            $("#carouselContent").html(slideHtml);
                        }

                        // Duyuruları güncelle
                        if (res.tickerNews) {
                            announcementsData = res.tickerNews;
                            currentAnnouncementIndex = 0;
                            startAnnouncementRotation();
                        }

                        // Hava durumunu güncelle
                        if (res.weather && res.weather.enabled) {
                            updateWeatherUi(res.weather);
                        }
                    }
                },
                error: function () {
                    console.warn("[UniPano] Sunucu bağlantısı yok. Yerel önbellek döngüsü devrede.");
                }
            });
        }

        function updateWeatherUi(w) {
            if (!w) return;
            const tempEl = document.getElementById('weatherTemp');
            const condEl = document.getElementById('weatherCondition');
            const iconEl = document.getElementById('weatherIcon');

            if (tempEl && w.temperatureFormatted) tempEl.textContent = w.temperatureFormatted;
            if (condEl && w.condition) condEl.textContent = w.condition;
            if (iconEl && w.iconSvg) iconEl.innerHTML = w.iconSvg;
        }

        // Kiosk periyodik kontrolü (25 sn)
        setInterval(checkKioskUpdates, <?= Config::KIOSK_POLL_INTERVAL_MS ?>);

        // Hava durumu bağımsız güncellemesi (10 dk)
        setInterval(function () {
            $.ajax({
                url: 'admin/ajax.php',
                data: { action: 'getWeatherData' },
                dataType: 'json',
                timeout: 5000,
                success: function (w) {
                    if (w && w.enabled) {
                        updateWeatherUi(w);
                    }
                }
            });
        }, 600000);

        // Çevrimdışı başlangıç koruması
        window.addEventListener('load', function () {
            if (!navigator.onLine) {
                const cached = localStorage.getItem('unipano_cache');
                if (cached) {
                    try {
                        const parsed = JSON.parse(cached);
                        if (parsed.tickerNews) {
                            announcementsData = parsed.tickerNews;
                            startAnnouncementRotation();
                        }
                    } catch (e) {
                        console.error("[UniPano] Önbellek yükleme hatası:", e);
                    }
                }
            }
        });
    })();
</script>
</body>
</html>
