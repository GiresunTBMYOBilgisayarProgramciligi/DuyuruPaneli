<?php
declare(strict_types=1);

use App\Config;

/** @var object $currentUser */
/** @var string $csrfToken */
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Yönetim Paneli</title>

    <!-- Bootstrap 5 CSS & Özel Admin Teması (NPM & Local Assets) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- 1. ÜST NAVİGASYON BARI -->
<nav class="admin-topbar">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
        <!-- Sol: Logo & Başlık -->
        <div class="d-flex align-items-center gap-3">
            <img src="/<?= htmlspecialchars(ltrim(Config::LOGO_PATH, '/'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="topbar-logo">
            <div class="topbar-brand">
                <span class="brand-title"><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="brand-badge">YÖNETİM</span>
            </div>
        </div>

        <!-- Sağ: Kiosk Önizleme & Profil Dropdown -->
        <div class="d-flex align-items-center gap-3">
            <a href="/" target="_blank" class="kiosk-preview-link d-none d-md-inline-flex" title="Yayındaki dijital panoyu yeni sekmede aç">
                <span>📺</span> Canlı Yayını İzle
            </a>

            <!-- Kullanıcı Menüsü -->
            <div class="dropdown">
                <div class="user-profile-badge dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar-circle">
                        <?= mb_strtoupper(mb_substr($currentUser->name ?? 'Y', 0, 1, 'UTF-8'), 'UTF-8') ?>
                    </div>
                    <div class="d-none d-sm-block text-start">
                        <div class="user-meta-name"><?= htmlspecialchars($currentUser->userName ?? 'Yönetici', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="user-meta-role">Yönetici</div>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" aria-labelledby="userDropdown">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold small"><?= htmlspecialchars(($currentUser->name ?? '') . ' ' . ($currentUser->lastName ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($currentUser->mail ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 text-danger d-flex align-items-center gap-2" id="logoutBtn" style="cursor: pointer;">
                            <span>🚪</span> Çıkış Yap
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- 2. ANA İÇERİK ALANI -->
<div class="container-fluid px-4 flex-grow-1">
    <!-- Dashboard KPI Kartları -->
    <div class="kpi-container">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Yayındaki Afişler</div>
                        <div class="kpi-value" id="statSlidesCount">0</div>
                        <div class="kpi-sub">Görsel slaytlar</div>
                    </div>
                    <div class="kpi-icon-wrap kpi-icon-blue">🖼️</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Kayan Duyurular</div>
                        <div class="kpi-value" id="statAnnouncementsCount">0</div>
                        <div class="kpi-sub">Alt bant metinleri</div>
                    </div>
                    <div class="kpi-icon-wrap kpi-icon-green">📢</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Toplam Okutma</div>
                        <div class="kpi-value" id="statScansCount">0</div>
                        <div class="kpi-sub">Öğrenci & ziyaretçi QR</div>
                    </div>
                    <div class="kpi-icon-wrap kpi-icon-amber">🔥</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Yöneticiler</div>
                        <div class="kpi-value" id="statUsersCount">0</div>
                        <div class="kpi-sub">Aktif hesaplar</div>
                    </div>
                    <div class="kpi-icon-wrap kpi-icon-purple">👥</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yönetim Sekmeleri -->
    <ul class="nav admin-tabs-nav" id="duyuruTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="slide-tab" data-bs-toggle="tab" data-bs-target="#slideTabContent" type="button" role="tab" aria-controls="slideTabContent" aria-selected="true">
                <span>🖼️</span> Afiş ve Slaytlar
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="duyuru-tab" data-bs-toggle="tab" data-bs-target="#duyuruTabContent" type="button" role="tab" aria-controls="duyuruTabContent" aria-selected="false">
                <span>📢</span> Kayan Duyurular
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#usersTabContent" type="button" role="tab" aria-controls="usersTabContent" aria-selected="false">
                <span>👥</span> Kullanıcı Hesapları
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logsTabContent" type="button" role="tab" aria-controls="logsTabContent" aria-selected="false">
                <span>📜</span> Sistem Günlükleri
            </button>
        </li>
    </ul>

    <!-- Sekme İçerikleri -->
    <div class="tab-content" id="adminTabContent">
        <?php require __DIR__ . "/partials/slides.php"; ?>
        <?php require __DIR__ . "/partials/announcements.php"; ?>
        <?php require __DIR__ . "/partials/users.php"; ?>
        <?php require __DIR__ . "/partials/logs.php"; ?>
    </div>
</div>

<!-- 3. ALT BİLGİ (FOOTER) -->
<footer class="admin-footer">
    <div class="container-fluid px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <div>
            <strong><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></strong> v<?= Config::APP_VERSION ?> — <?= htmlspecialchars(Config::APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div>
            <?= htmlspecialchars(Config::INSTITUTION_NAME, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
</footer>

<!-- 4. PAYLAŞILAN İNTERAKTİF MODALLAR -->
<?php require __DIR__ . "/partials/modals.php"; ?>

<!-- 5. KÜTÜPHANELER VE SCRİPTLER (NPM & Local Assets) -->
<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/assets/js/admin.js"></script>

<script>
    // Global CSRF Token Injection
    window.UNIPANO_CSRF = "<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>";
    
    // Çıkış yapma tetikleyicisi
    document.getElementById('logoutBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/logout';
        
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
