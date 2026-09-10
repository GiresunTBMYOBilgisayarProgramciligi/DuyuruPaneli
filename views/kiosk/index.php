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
/** @var string $firstShortHost */
/** @var string $firstShortPath */
/** @var string $firstShortDisplay */
/** @var string $firstShortUrl */
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
         1. MODÜLER ÜST BAŞLIK BANDI (İki Kutuplu Ultra Minimalist Tasarım)
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
        <!-- Sağ Üst Rozet: Afiş Sayacı (örn: Afiş 1 / 2) -->
        <div class="slide-counter-badge" id="slideCounterBadge" style="<?= count($slides) > 0 ? '' : 'display: none;' ?>">
            <span class="counter-badge-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                    <circle cx="9" cy="9" r="2"/>
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                </svg>
            </span>
            <span class="counter-badge-label">Afiş</span>
            <span class="counter-badge-current" id="counterCurrent">1</span>
            <span class="counter-badge-divider">/</span>
            <span class="counter-badge-total" id="counterTotal"><?= count($slides) ?></span>
            <div class="counter-badge-pills" id="counterBadgePills">
                <?php foreach ($slides as $idx => $slide): ?>
                    <span class="mini-pill <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="kioskCarousel" class="kiosk-carousel carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="<?= $slideIntervalMs ?? Config::SLIDE_INTERVAL_MS ?>">
            <?php if (count($slides) > 1): ?>
            <div class="carousel-indicators" id="carouselIndicators">
                <?php foreach ($slides as $idx => $slide): ?>
                    <button type="button" data-bs-target="#kioskCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>" aria-current="<?= $idx === 0 ? 'true' : 'false' ?>" aria-label="Afiş <?= $idx + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="carousel-indicators" id="carouselIndicators" style="display: none;"></div>
            <?php endif; ?>

            <div class="carousel-inner" id="carouselContent">
                <?php if (count($slides) > 0): ?>
                    <?php foreach ($slides as $idx => $slide): ?>
                        <?php 
                            $activeClass = ($idx === 0) ? 'active' : '';
                            $fullWidthClass = (!empty($slide->fullWidth)) ? 'full-width' : '';
                            $imageSrc = !empty($slide->image) ? '/' . ltrim($slide->image, '/') : '';
                            $ytId = $slide->youtubeVideoId ?? null;
                            $isYt = !empty($ytId);
                        ?>
                        <div class="carousel-item <?= $activeClass ?>" 
                             data-slide-type="<?= $isYt ? 'youtube' : 'image' ?>"
                             <?php if ($isYt): ?>data-youtube-id="<?= htmlspecialchars($ytId, ENT_QUOTES, 'UTF-8') ?>" data-slide-id="<?= $slide->id ?>"<?php endif; ?>>
                            
                            <?php if (!empty($imageSrc)): ?>
                                <div class="slide-ambient-bg" style="background-image: url('<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>');"></div>
                            <?php else: ?>
                                <div class="slide-ambient-bg slide-ambient-video"></div>
                            <?php endif; ?>

                            <?php if ($isYt): ?>
                                <div class="slide-video-container <?= $fullWidthClass ?>">
                                    <div class="slide-video-stage <?= $fullWidthClass ?>">
                                        <div id="ytPlayer_<?= $slide->id ?>" class="youtube-player-frame" data-video-id="<?= htmlspecialchars($ytId, ENT_QUOTES, 'UTF-8') ?>"></div>
                                        
                                        <!-- Video Kontrol Şeridi -->
                                        <div class="video-overlay-bar">
                                            <div class="video-overlay-left">
                                                <?php $soundMutedInit = !Config::KIOSK_VIDEO_SOUND; ?>
                                                <button type="button" class="video-overlay-btn video-sound-btn <?= $soundMutedInit ? 'is-muted' : '' ?>" data-player-id="ytPlayer_<?= $slide->id ?>" title="Sesi Aç / Kapat">
                                                    <span class="sound-icon"><?= $soundMutedInit ? '🔇' : '🔊' ?></span>
                                                    <span class="sound-text"><?= $soundMutedInit ? 'Ses Kapalı' : 'Ses Açık' ?></span>
                                                </button>
                                            </div>
                                            <div class="video-overlay-right">
                                                <button type="button" class="video-overlay-btn video-skip-btn" title="Sonraki afişe geç">
                                                    <span>Sonraki Afiş</span>
                                                    <svg class="btn-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M5 12h14"></path>
                                                        <path d="m12 5 7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Video İlerleme Çubuğu -->
                                        <div class="video-progress-line-wrapper">
                                            <div class="video-progress-line" id="videoProgressLine_<?= $slide->id ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="slide-image-wrapper <?= $fullWidthClass ?>">
                                    <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>" class="slide-image <?= $fullWidthClass ?>" alt="<?= htmlspecialchars($slide->title ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            <?php endif; ?>
                            <?php 
                                $showCaption = !isset($slide->showCaption) || (int)$slide->showCaption === 1;
                                $hasText = !empty($slide->title) || !empty($slide->content);
                                $qrPos = $slide->qrPosition ?? 'bottom-right';
                                $hasQr = !empty($slide->qrCode) && $qrPos !== 'none';
                            ?>
                            <?php if ($showCaption && $hasText): ?>
                                <div class="slide-caption-card">
                                    <?php if (!empty($slide->title)): ?>
                                        <h3 class="slide-caption-title">
                                            <span class="slide-caption-title-dot"></span>
                                            <span><?= htmlspecialchars($slide->title, ENT_QUOTES, 'UTF-8') ?></span>
                                        </h3>
                                    <?php endif; ?>
                                    <?php if (!empty($slide->content)): ?>
                                        <p class="slide-caption-content"><?= htmlspecialchars($slide->content, ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($hasQr): ?>
                                <div class="slide-qr-card pos-<?= htmlspecialchars($qrPos, ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="slide-qr-box">
                                        <?= $slide->qrCode ?>
                                    </div>
                                    <div class="slide-qr-text">
                                        <span class="slide-qr-title">DETAYLAR İÇİN</span>
                                        <span class="slide-qr-sub">KODU OKUTUN</span>
                                    </div>
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

        <!-- Sağ QR Kod Okutma Bölümü (Duyuru Bandıyla Birebir Aynı Yükseklikte & Tam Entegre) -->
        <div class="footer-qr-card" id="footerQrCard" style="<?= $hasFirstQr ? 'display: flex;' : 'display: none;' ?>" title="<?= htmlspecialchars($firstShortUrl ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="qr-callout-text">
                <span class="qr-callout-primary">DETAYLAR İÇİN</span>
                <span class="qr-callout-secondary">TELEFONLA OKUTUN</span>
                <span class="qr-callout-url" id="footerQrUrl" style="<?= !empty($firstShortDisplay) ? 'display: inline-flex;' : 'display: none;' ?>" title="<?= htmlspecialchars($firstShortUrl ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <span class="url-host-truncate" id="footerQrHost"><?= htmlspecialchars($firstShortHost ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="url-code-badge" id="footerQrPath"><?= htmlspecialchars($firstShortPath ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            </div>
            <div class="qr-canvas-box" id="footerQrCode">
                <?= $firstQr ?>
            </div>
        </div>
    </footer>
    <?php endif; ?>
</div>

<!-- YouTube IFrame API küresel hazır olma dinleyicisi (Script yüklenmeden önce tanımlanır) -->
<script>
    window._isYtApiReady = false;
    window.onYouTubeIframeAPIReady = function () {
        window._isYtApiReady = true;
        if (typeof window.initUniPanoYouTubePlayers === 'function') {
            window.initUniPanoYouTubePlayers();
        }
    };
</script>
<script src="https://www.youtube.com/iframe_api"></script>
<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>

<script>
    (function () {
        'use strict';

        const configVideoSound = <?= Config::KIOSK_VIDEO_SOUND ? 'true' : 'false' ?>;

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

        // 2. Kiosk Carousel & YouTube Player Senkronizasyon Yönetimi
        const kioskCarouselEl = document.getElementById('kioskCarousel');
        const counterCurrentEl = document.getElementById('counterCurrent');
        const counterTotalEl = document.getElementById('counterTotal');
        const counterBadgePillsEl = document.getElementById('counterBadgePills');
        const slideCounterBadgeEl = document.getElementById('slideCounterBadge');
        const carouselIndicatorsEl = document.getElementById('carouselIndicators');

        let kioskCarouselInstance = null;
        const ytPlayers = new Map(); // key: frameId, value: { player, isReady, videoId, hasEnded }
        let videoProgressInterval = null;

        function isAudioMuted() {
            const stored = localStorage.getItem('unipano_audio_muted');
            if (stored !== null) {
                return stored === '1';
            }
            return !configVideoSound;
        }

        function setAudioMuted(muted) {
            localStorage.setItem('unipano_audio_muted', muted ? '1' : '0');
            updateSoundButtonUi(muted);
        }

        function updateSoundButtonUi(muted, isBlocked = false) {
            const btns = document.querySelectorAll('.video-sound-btn');
            btns.forEach(function (btn) {
                const icon = btn.querySelector('.sound-icon');
                const text = btn.querySelector('.sound-text');
                if (muted) {
                    btn.classList.add('is-muted');
                    if (icon) icon.textContent = '🔇';
                    if (text) text.textContent = isBlocked ? 'Sesi Aç (Tıklayın)' : 'Ses Kapalı';
                } else {
                    btn.classList.remove('is-muted');
                    if (icon) icon.textContent = '🔊';
                    if (text) text.textContent = 'Ses Açık';
                }
            });
        }

        function updateVideoProgressUi(cur, dur) {
            const activeSlide = kioskCarouselEl ? kioskCarouselEl.querySelector('.carousel-inner .carousel-item.active') : null;
            if (!activeSlide) return;

            const progressLine = activeSlide.querySelector('.video-progress-line');
            if (progressLine && dur > 0) {
                const pct = Math.min(100, Math.max(0, (cur / dur) * 100));
                progressLine.style.width = pct + '%';
            }
        }

        function updateSlideCounter(activeIndex, totalCount) {
            if (counterCurrentEl) {
                counterCurrentEl.textContent = String(activeIndex + 1);
            }
            if (counterTotalEl && typeof totalCount === 'number') {
                counterTotalEl.textContent = String(totalCount);
            }
            if (counterBadgePillsEl) {
                const pills = counterBadgePillsEl.querySelectorAll('.mini-pill');
                pills.forEach((pill, idx) => {
                    if (idx === activeIndex) {
                        pill.classList.add('active');
                    } else {
                        pill.classList.remove('active');
                    }
                });
            }
        }

        function initYouTubePlayers() {
            if (!window._isYtApiReady && (typeof YT === 'undefined' || !YT.Player)) return;
            if (!kioskCarouselEl) return;

            const frameEls = kioskCarouselEl.querySelectorAll('.youtube-player-frame');
            frameEls.forEach(function (el) {
                const frameId = el.id;
                const videoId = el.getAttribute('data-video-id');
                if (!frameId || !videoId || ytPlayers.has(frameId)) return;

                const entry = {
                    player: null,
                    isReady: false,
                    videoId: videoId,
                    hasEnded: false
                };
                ytPlayers.set(frameId, entry);

                try {
                    entry.player = new YT.Player(frameId, {
                        videoId: videoId,
                        playerVars: {
                            autoplay: 1,
                            controls: 1,
                            rel: 0,
                            modestbranding: 1,
                            playsinline: 1,
                            enablejsapi: 1,
                            iv_load_policy: 3,
                            fs: 0,
                            loop: 0,
                            origin: window.location.origin
                        },
                        events: {
                            onReady: function () {
                                entry.isReady = true;
                                const activeItem = kioskCarouselEl.querySelector('.carousel-inner .carousel-item.active');
                                if (activeItem && activeItem.contains(document.getElementById(frameId))) {
                                    handleSlideActivation(activeItem);
                                }
                            },
                            onStateChange: function (event) {
                                // YT.PlayerState: ENDED = 0, PLAYING = 1, PAUSED = 2, BUFFERING = 3, CUED = 5
                                if (event.data === 0) {
                                    console.log('[UniPano] YouTube onStateChange(ENDED) tetiklendi. Sonraki afişe geçiliyor...');
                                    triggerVideoFinished(entry);
                                } else if (event.data === 1) {
                                    entry.hasEnded = false;
                                    clearVideoSafetyWatchdog();

                                    // Oynama başladıktan sonra video süresi + 15 saniyelik nihai güvenlik sınırı koy
                                    try {
                                        const dur = entry.player.getDuration();
                                        if (dur && dur > 0) {
                                            setVideoSafetyWatchdog(entry, (dur + 15) * 1000);
                                        }
                                    } catch (e) {}

                                    startVideoProgressTimer(entry.player, entry);
                                }
                            },
                            onError: function (event) {
                                console.warn('[UniPano] YouTube oynatma hatası (kod: ' + event.data + '). 4 sn sonra geçiliyor.');
                                clearVideoSafetyWatchdog();
                                setTimeout(function () {
                                    triggerVideoFinished(entry);
                                }, 4000);
                            }
                        }
                    });
                } catch (err) {
                    console.error('[UniPano] YT.Player oluşturulamadı:', err);
                }
            });

            updateSoundButtonUi(isAudioMuted());
        }

        window.initUniPanoYouTubePlayers = initYouTubePlayers;

        let videoSafetyWatchdogTimer = null;

        function clearVideoSafetyWatchdog() {
            if (videoSafetyWatchdogTimer) {
                clearTimeout(videoSafetyWatchdogTimer);
                videoSafetyWatchdogTimer = null;
            }
        }

        function setVideoSafetyWatchdog(entry, timeoutMs = 15000) {
            clearVideoSafetyWatchdog();
            videoSafetyWatchdogTimer = setTimeout(function () {
                console.warn('[UniPano] Video güvenlik zaman aşımı (' + (timeoutMs / 1000) + ' sn). Kiosk kilitlenmesini önlemek için sonraki afişe geçiliyor.');
                triggerVideoFinished(entry);
            }, timeoutMs);
        }

        function playActiveYouTubeVideo(player) {
            if (!player || typeof player.playVideo !== 'function') return;

            const muted = isAudioMuted();

            try {
                if (muted) {
                    if (typeof player.mute === 'function') player.mute();
                    updateSoundButtonUi(true);
                } else {
                    if (typeof player.unMute === 'function') player.unMute();
                    if (typeof player.setVolume === 'function') player.setVolume(100);
                    updateSoundButtonUi(false);
                }

                player.playVideo();

                // Tarayıcı otomatik sesli oynatmayı engellediyse (Autoplay policy fallback)
                setTimeout(function () {
                    try {
                        if (!muted && typeof player.getPlayerState === 'function') {
                            const state = player.getPlayerState();
                            // State 1 = playing, 3 = buffering. Başlamadıysa sessiz başlatıp butonu yak
                            if (state !== 1 && state !== 3) {
                                console.warn('[UniPano] Sesli otomatik oynatma tarayıcı tarafından engellendi. Sessiz başlatılıyor.');
                                if (typeof player.mute === 'function') player.mute();
                                player.playVideo();
                                updateSoundButtonUi(true, true);
                            }
                        }
                    } catch (e) {}
                }, 1000);
            } catch (e) {
                console.warn('[UniPano] playVideo çağrısı başarısız:', e);
            }
        }

        function pauseAllYouTubeVideos() {
            ytPlayers.forEach(function (entry) {
                if (entry.isReady && entry.player && typeof entry.player.pauseVideo === 'function') {
                    try {
                        entry.player.pauseVideo();
                    } catch (e) {}
                }
            });
        }

        function startVideoProgressTimer(player, entry) {
            stopVideoProgressTimer();
            let prevCurTime = 0;

            videoProgressInterval = setInterval(function () {
                if (!player || typeof player.getCurrentTime !== 'function' || typeof player.getDuration !== 'function') {
                    return;
                }

                try {
                    const cur = player.getCurrentTime();
                    const dur = player.getDuration();

                    if (dur && dur > 0) {
                        updateVideoProgressUi(cur, dur);

                        // 1. Video süresinin sonuna (son 0.6 saniye) gelindiğinde
                        // 2. VEYA YouTube otomatik olarak radyo listesindeki bir sonraki şarkıya atladıysa:
                        if (cur >= dur - 0.6 || (prevCurTime > dur - 2.5 && cur < 1.0)) {
                            console.log('[UniPano] Video süresi tamamlandı (' + cur.toFixed(1) + 's / ' + dur.toFixed(1) + 's).');
                            triggerVideoFinished(entry);
                            return;
                        }

                        prevCurTime = cur;
                    }
                } catch (e) {}
            }, 250);
        }

        function stopVideoProgressTimer() {
            if (videoProgressInterval) {
                clearInterval(videoProgressInterval);
                videoProgressInterval = null;
            }
        }

        function triggerVideoFinished(entry) {
            if (entry && entry.hasEnded) return;
            if (entry) entry.hasEnded = true;

            clearVideoSafetyWatchdog();
            stopVideoProgressTimer();
            if (entry && entry.player && typeof entry.player.pauseVideo === 'function') {
                try { entry.player.pauseVideo(); } catch (e) {}
            }

            const activeSlide = kioskCarouselEl ? kioskCarouselEl.querySelector('.carousel-inner .carousel-item.active') : null;
            if (activeSlide) {
                const progressLine = activeSlide.querySelector('.video-progress-line');
                if (progressLine) progressLine.style.width = '100%';
            }

            advanceToNextSlide();
        }

        function advanceToNextSlide() {
            if (!kioskCarouselEl) return;

            clearVideoSafetyWatchdog();
            stopVideoProgressTimer();
            pauseAllYouTubeVideos();

            const items = kioskCarouselEl.querySelectorAll('.carousel-inner .carousel-item');
            if (items.length > 1) {
                if (kioskCarouselInstance) {
                    kioskCarouselInstance.next();
                }
            } else if (items.length === 1) {
                // Tek afiş varsa ve video ise başa sarıp tekrar başlat
                const frameEl = items[0].querySelector('.youtube-player-frame');
                if (frameEl && frameEl.id && ytPlayers.has(frameEl.id)) {
                    const entry = ytPlayers.get(frameEl.id);
                    if (entry && entry.isReady && entry.player) {
                        try {
                            entry.hasEnded = false;
                            entry.player.seekTo(0, true);
                            playActiveYouTubeVideo(entry.player);
                            startVideoProgressTimer(entry.player, entry);
                        } catch (e) {}
                    }
                }
            }
        }

        function handleSlideActivation(activeSlide) {
            if (!activeSlide) return;
            clearVideoSafetyWatchdog();

            const isYt = activeSlide.getAttribute('data-slide-type') === 'youtube';

            if (isYt) {
                console.log('[UniPano] YouTube afişi aktif. Carousel zamanlayıcısı duraklatıldı.');
                // 1. Carousel otomatik döngüsünü durdur (video bitene kadar beklesin)
                if (kioskCarouselInstance) {
                    kioskCarouselInstance.pause();
                }

                // 2. İlgili videoyu oynat ve süre izleyicisini başlat
                const frameEl = activeSlide.querySelector('.youtube-player-frame');
                if (frameEl && frameEl.id && ytPlayers.has(frameEl.id)) {
                    const entry = ytPlayers.get(frameEl.id);

                    // Başlangıç güvenlik zamanlayıcısı: 15 sn içinde oynama başlamazsa kilitlenmeyi önle
                    setVideoSafetyWatchdog(entry, 15000);

                    if (entry.isReady && entry.player) {
                        entry.hasEnded = false;
                        try {
                            if (typeof entry.player.seekTo === 'function') {
                                entry.player.seekTo(0, true);
                            }
                        } catch (e) {}
                        playActiveYouTubeVideo(entry.player);
                        startVideoProgressTimer(entry.player, entry);
                    }
                } else {
                    // Oynatıcı henüz hazır değilse 15 sn güvenlik sınırı
                    setVideoSafetyWatchdog(null, 15000);
                }
            } else {
                // Normal görsel afiş: Video zamanlayıcısını durdur ve Carousel döngüsünü devam ettir
                stopVideoProgressTimer();
                const items = kioskCarouselEl ? kioskCarouselEl.querySelectorAll('.carousel-inner .carousel-item') : [];
                if (items.length > 1 && kioskCarouselInstance) {
                    kioskCarouselInstance.cycle();
                }
            }
        }

        function initKioskCarousel() {
            if (!kioskCarouselEl || typeof bootstrap === 'undefined') return;

            if (kioskCarouselInstance) {
                try {
                    kioskCarouselInstance.dispose();
                } catch (e) {}
            }

            const items = kioskCarouselEl.querySelectorAll('.carousel-inner .carousel-item');
            if (items.length > 1) {
                kioskCarouselInstance = new bootstrap.Carousel(kioskCarouselEl, {
                    interval: <?= $slideIntervalMs ?? Config::SLIDE_INTERVAL_MS ?>,
                    ride: false,
                    pause: false,
                    wrap: true,
                    touch: false
                });
            }

            const activeItem = kioskCarouselEl.querySelector('.carousel-inner .carousel-item.active') || items[0];
            if (activeItem) {
                handleSlideActivation(activeItem);
            }
        }

        if (kioskCarouselEl) {
            kioskCarouselEl.addEventListener('slide.bs.carousel', function () {
                clearVideoSafetyWatchdog();
                stopVideoProgressTimer();
                pauseAllYouTubeVideos();
            });

            kioskCarouselEl.addEventListener('slid.bs.carousel', function () {
                const items = kioskCarouselEl.querySelectorAll('.carousel-inner .carousel-item');
                const activeItem = kioskCarouselEl.querySelector('.carousel-inner .carousel-item.active');
                const activeIndex = activeItem ? Array.from(items).indexOf(activeItem) : 0;

                updateSlideCounter(activeIndex, items.length);
                handleSlideActivation(activeItem);
            });

            initKioskCarousel();
            if (window._isYtApiReady || (typeof YT !== 'undefined' && YT.Player)) {
                window._isYtApiReady = true;
                initYouTubePlayers();
            }
        }

        // Kullanıcı Tıklama ve Ses / Atlama Etkileşim Dinleyicisi
        document.addEventListener('click', function (e) {
            // Sesi Aç / Kapat Butonu
            const soundBtn = e.target.closest('.video-sound-btn');
            if (soundBtn) {
                e.preventDefault();
                e.stopPropagation();
                // Buton o anda sessiz durumdaysa aç, sesliyse kapat
                const isCurrentlyMuted = soundBtn.classList.contains('is-muted');
                const newMuted = !isCurrentlyMuted;
                setAudioMuted(newMuted);

                ytPlayers.forEach(function (entry) {
                    if (entry.isReady && entry.player) {
                        try {
                            if (newMuted) {
                                entry.player.mute();
                            } else {
                                entry.player.unMute();
                                entry.player.setVolume(100);
                            }
                        } catch (err) {}
                    }
                });
                return;
            }

            // Sonraki Afişe Geç (Videoyu Atla) Butonu
            const skipBtn = e.target.closest('.video-skip-btn');
            if (skipBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('[UniPano] Kullanıcı videoyu atladı (Sonraki afişe geçiliyor).');
                advanceToNextSlide();
                return;
            }

            // Sayfada herhangi bir yere tıklandığında ses tercihi açık ise tarayıcı ses kilidini aç
            const storedMuted = localStorage.getItem('unipano_audio_muted');
            const userExplicitlyMuted = (storedMuted === '1');
            if (!userExplicitlyMuted && configVideoSound) {
                ytPlayers.forEach(function (entry) {
                    if (entry.isReady && entry.player && typeof entry.player.unMute === 'function') {
                        try {
                            entry.player.unMute();
                            entry.player.setVolume(100);
                        } catch (err) {}
                    }
                });
                updateSoundButtonUi(false);
            }
        });

        // 3. Modüler Duyuru Rotasyonu ve Senkronize QR Kartı
        let announcementsData = <?= json_encode($initialTickerData, JSON_UNESCAPED_UNICODE) ?>;
        let currentAnnouncementIndex = 0;
        let announcementTimer = null;

        const badgeEl = document.getElementById('announcementBadge');
        const textEl = document.getElementById('announcementText');
        const qrCardEl = document.getElementById('footerQrCard');
        const qrCodeBoxEl = document.getElementById('footerQrCode');
        const qrUrlEl = document.getElementById('footerQrUrl');
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

            // QR Kod ve Kısa Link Senkronizasyonu
            if (qrCardEl && qrCodeBoxEl) {
                if (item.qrCode && item.qrCode.trim() !== '') {
                    qrCodeBoxEl.innerHTML = item.qrCode;
                    if (qrUrlEl) {
                        if (item.shortDisplay && item.shortDisplay.trim() !== '') {
                            let hostVal = item.shortHost || '';
                            let pathVal = item.shortPath || '';
                            if (!hostVal && item.shortDisplay) {
                                const slashIdx = item.shortDisplay.indexOf('/');
                                if (slashIdx !== -1) {
                                    hostVal = item.shortDisplay.substring(0, slashIdx);
                                    pathVal = item.shortDisplay.substring(slashIdx);
                                } else {
                                    hostVal = item.shortDisplay;
                                    pathVal = '';
                                }
                            }
                            const hostEl = document.getElementById('footerQrHost');
                            const pathEl = document.getElementById('footerQrPath');
                            if (hostEl && pathEl) {
                                hostEl.textContent = hostVal;
                                pathEl.textContent = pathVal;
                            } else {
                                qrUrlEl.textContent = item.shortDisplay;
                            }
                            qrUrlEl.title = item.shortUrl || item.link || item.shortDisplay;
                            qrUrlEl.style.display = "inline-flex";
                        } else {
                            qrUrlEl.style.display = "none";
                        }
                    }
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

        // 4. Kırpışmasız Arka Plan İzleyicisi (Flicker-Free Watchdog - Native Fetch)
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
                        // Önceki video oynatıcıları temizle
                        ytPlayers.forEach(function (entry) {
                            try {
                                if (entry.player && typeof entry.player.destroy === 'function') {
                                    entry.player.destroy();
                                }
                            } catch (e) {}
                        });
                        ytPlayers.clear();

                        let slideHtml = "";
                        let indicatorsHtml = "";
                        let pillsHtml = "";
                        const total = res.slides.length;

                        res.slides.forEach(function (slide, idx) {
                            const active = idx === 0 ? "active" : "";
                            const fullW = slide.fullWidth == 1 ? "full-width" : "";
                            const imgSrc = slide.image ? '/' + slide.image.replace(/^\/+/, '') : '';
                            const ytId = slide.youtubeVideoId || '';
                            const isYt = Boolean(ytId);

                            let mediaHtml = '';
                            if (isYt) {
                                const isMuted = isAudioMuted();
                                const soundIcon = isMuted ? '🔇' : '🔊';
                                const soundText = isMuted ? 'Ses Kapalı' : 'Ses Açık';
                                const soundClass = isMuted ? 'is-muted' : '';

                                mediaHtml = '<div class="slide-video-container ' + fullW + '">' +
                                    '<div class="slide-video-stage ' + fullW + '">' +
                                    '<div id="ytPlayer_' + slide.id + '" class="youtube-player-frame" data-video-id="' + escapeHtml(ytId) + '"></div>' +
                                    '<div class="video-overlay-bar">' +
                                    '<div class="video-overlay-left">' +
                                    '<button type="button" class="video-overlay-btn video-sound-btn ' + soundClass + '" data-player-id="ytPlayer_' + slide.id + '" title="Sesi Aç / Kapat">' +
                                    '<span class="sound-icon">' + soundIcon + '</span>' +
                                    '<span class="sound-text">' + soundText + '</span>' +
                                    '</button>' +
                                    '</div>' +
                                    '<div class="video-overlay-right">' +
                                    '<button type="button" class="video-overlay-btn video-skip-btn" title="Sonraki afişe geç">' +
                                    '<span>Sonraki Afiş</span>' +
                                    '<svg class="btn-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
                                    '<path d="M5 12h14"></path>' +
                                    '<path d="m12 5 7 7-7 7"></path>' +
                                    '</svg>' +
                                    '</button>' +
                                    '</div>' +
                                    '</div>' +
                                    '<div class="video-progress-line-wrapper">' +
                                    '<div class="video-progress-line" id="videoProgressLine_' + slide.id + '" style="width: 0%;"></div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>';
                            } else {
                                mediaHtml = '<div class="slide-image-wrapper ' + fullW + '">' +
                                    '<img src="' + imgSrc + '" class="slide-image ' + fullW + '" alt="">' +
                                    '</div>';
                            }

                            const ambientBg = imgSrc
                                ? '<div class="slide-ambient-bg" style="background-image: url(\'' + imgSrc + '\');"></div>'
                                : '<div class="slide-ambient-bg slide-ambient-video"></div>';

                            let caption = "";
                            const showCaption = slide.showCaption === undefined || slide.showCaption == 1;
                            if (showCaption && (slide.title || slide.content)) {
                                caption = '<div class="slide-caption-card">' +
                                    (slide.title ? '<h3 class="slide-caption-title"><span class="slide-caption-title-dot"></span><span>' + escapeHtml(slide.title) + '</span></h3>' : '') +
                                    (slide.content ? '<p class="slide-caption-content">' + escapeHtml(slide.content) + '</p>' : '') +
                                    '</div>';
                            }
                            let qrHtml = "";
                            const qrPos = slide.qrPosition || 'bottom-right';
                            if (slide.qrCode && qrPos !== 'none') {
                                qrHtml = '<div class="slide-qr-card pos-' + escapeHtml(qrPos) + '">' +
                                    '<div class="slide-qr-box">' + slide.qrCode + '</div>' +
                                    '<div class="slide-qr-text">' +
                                    '<span class="slide-qr-title">DETAYLAR İÇİN</span>' +
                                    '<span class="slide-qr-sub">KODU OKUTUN</span>' +
                                    '</div>' +
                                    '</div>';
                            }
                            slideHtml += '<div class="carousel-item ' + active + '" data-slide-type="' + (isYt ? 'youtube' : 'image') + '"' +
                                (isYt ? ' data-youtube-id="' + escapeHtml(ytId) + '" data-slide-id="' + slide.id + '"' : '') + '>' +
                                ambientBg +
                                mediaHtml +
                                caption +
                                qrHtml +
                                '</div>';

                            if (total > 1) {
                                indicatorsHtml += '<button type="button" data-bs-target="#kioskCarousel" data-bs-slide-to="' + idx + '" class="' + active + '" aria-current="' + (idx === 0 ? 'true' : 'false') + '" aria-label="Afiş ' + (idx + 1) + '"></button>';
                            }
                            pillsHtml += '<span class="mini-pill ' + active + '" data-index="' + idx + '"></span>';
                        });

                        const carouselEl = document.getElementById('carouselContent');
                        if (carouselEl) carouselEl.innerHTML = slideHtml;

                        if (carouselIndicatorsEl) {
                            carouselIndicatorsEl.innerHTML = indicatorsHtml;
                            carouselIndicatorsEl.style.display = total > 1 ? 'flex' : 'none';
                        }

                        if (counterTotalEl) counterTotalEl.textContent = String(total);
                        if (counterCurrentEl) counterCurrentEl.textContent = "1";
                        if (counterBadgePillsEl) counterBadgePillsEl.innerHTML = pillsHtml;
                        if (slideCounterBadgeEl) slideCounterBadgeEl.style.display = total > 0 ? 'inline-flex' : 'none';

                        initKioskCarousel();
                        if (window._isYtApiReady || (typeof YT !== 'undefined' && YT.Player)) {
                            window._isYtApiReady = true;
                            initYouTubePlayers();
                        }
                    } else {
                        ytPlayers.forEach(function (entry) {
                            try {
                                if (entry.player && typeof entry.player.destroy === 'function') {
                                    entry.player.destroy();
                                }
                            } catch (e) {}
                        });
                        ytPlayers.clear();

                        const carouselEl = document.getElementById('carouselContent');
                        if (carouselEl) {
                            carouselEl.innerHTML = '<div class="carousel-item active">' +
                                '<div class="empty-stage-card">' +
                                '<div class="empty-icon">🎓</div>' +
                                '<h2 class="empty-title"><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></h2>' +
                                '<p class="empty-desc">Şu anda yayında aktif bir görsel veya afiş bulunmamaktadır.</p>' +
                                '</div>' +
                                '</div>';
                        }
                        if (carouselIndicatorsEl) carouselIndicatorsEl.style.display = 'none';
                        if (slideCounterBadgeEl) slideCounterBadgeEl.style.display = 'none';
                        if (kioskCarouselInstance) {
                            try { kioskCarouselInstance.dispose(); } catch (e) {}
                            kioskCarouselInstance = null;
                        }
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
