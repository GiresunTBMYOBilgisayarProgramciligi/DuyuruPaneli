<?php
declare(strict_types=1);

use App\Config;

/** @var array $slides */
/** @var array $initialTickerData */
/** @var array $weather */
/** @var string $initialTime */
/** @var string $initialDate */
/** @var string $firstPrefix */
/** @var string $firstDuyuru */
/** @var string $firstQr */
/** @var bool $hasFirstQr */
/** @var string $initialContentHash */
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars(Config::INSTITUTION_NAME, ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap 5 CSS & Özel UniPano Kiosk Teması (NPM & Local Assets) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/kiosk.css">
</head>
<body>

<div class="kiosk-wrapper">
    <!-- ====================================================================
         1. MODÜLER ÜST BAŞLIK BANDI (KUSURSUZ 1fr auto 1fr ORTALAMA)
         ==================================================================== -->
    <header class="kiosk-header">
        <!-- Sol Modül: Kurum & Kampüs / Birim Bilgisi -->
        <div class="header-left">
            <img src="/<?= htmlspecialchars(ltrim(Config::LOGO_PATH, '/'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="header-logo">
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
                            $imageSrc = '/' . ltrim($slide->image, '/');
                        ?>
                        <div class="carousel-item <?= $activeClass ?>">
                            <div class="slide-ambient-bg" style="background-image: url('<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>');"></div>
                            <div class="slide-image-wrapper <?= $fullWidthClass ?>">
                                <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>" class="slide-image <?= $fullWidthClass ?>" alt="<?= htmlspecialchars($slide->title ?? '', ENT_QUOTES, 'UTF-8') ?>">
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

<!-- Kütüphaneler (NPM Dağıtımı) -->
<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>

<script>
    (function () {
        'use strict';

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }

        // 1. Canlı Tarih ve Dijital Saat Motoru
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

        // 2. Modüler Duyuru Rotasyonu ve Senkronize QR Kartı
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

        // 3. Kırpışmasız Arka Plan İzleyicisi (Flicker-Free Watchdog - Native Fetch)
        let lastContentHash = "<?= $initialContentHash ?>";

        async function checkKioskUpdates() {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 8000);
                const response = await fetch('/api/kiosk-data', { signal: controller.signal });
                clearTimeout(timeoutId);

                if (!response.ok) return;
                const res = await response.json();
                if (!res || !res.hash) return;

                localStorage.setItem('unipano_cache', JSON.stringify(res));

                if (res.hash !== lastContentHash) {
                    console.log("[UniPano] Yeni veri algılandı, arka planda güncelleniyor...");
                    lastContentHash = res.hash;

                    if (res.slides && res.slides.length > 0) {
                        let slideHtml = "";
                        res.slides.forEach(function (slide, idx) {
                            const active = idx === 0 ? "active" : "";
                            const fullW = slide.fullWidth == 1 ? "full-width" : "";
                            const imgSrc = '/' + slide.image.replace(/^\/+/, '');
                            let caption = "";
                            if (slide.title || slide.content) {
                                caption = '<div class="slide-caption-card">' +
                                    (slide.title ? '<h3 class="slide-caption-title">' + escapeHtml(slide.title) + '</h3>' : '') +
                                    (slide.content ? '<p class="slide-caption-content">' + escapeHtml(slide.content) + '</p>' : '') +
                                    '</div>';
                            }
                            slideHtml += '<div class="carousel-item ' + active + '">' +
                                '<div class="slide-ambient-bg" style="background-image: url(\'' + imgSrc + '\');"></div>' +
                                '<div class="slide-image-wrapper ' + fullW + '">' +
                                '<img src="' + imgSrc + '" class="slide-image ' + fullW + '" alt="">' +
                                '</div>' +
                                caption +
                                '</div>';
                        });
                        const carouselEl = document.getElementById('carouselContent');
                        if (carouselEl) carouselEl.innerHTML = slideHtml;
                    }

                    if (res.tickerNews) {
                        announcementsData = res.tickerNews;
                        currentAnnouncementIndex = 0;
                        startAnnouncementRotation();
                    }

                    if (res.weather && res.weather.enabled) {
                        updateWeatherUi(res.weather);
                    }
                }
            } catch (err) {
                console.warn("[UniPano] Sunucu bağlantısı yok. Yerel önbellek döngüsü devrede.");
            }
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
        setInterval(async function () {
            try {
                const response = await fetch('/api/weather');
                if (response.ok) {
                    const w = await response.json();
                    if (w && w.enabled) {
                        updateWeatherUi(w);
                    }
                }
            } catch (e) {
                console.warn("[UniPano] Hava durumu güncellenemedi.");
            }
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
