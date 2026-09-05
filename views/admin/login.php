<?php
declare(strict_types=1);

use App\Config;

/** @var string $csrfToken */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?> | Yönetici Girişi</title>
    
    <!-- Bootstrap 5 CSS & Modern Admin Stilleri (NPM & Local Assets) -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="shortcut icon" href="/<?= htmlspecialchars(ltrim(Config::LOGO_PATH, '/'), ENT_QUOTES, 'UTF-8') ?>"/>

    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%);
        }
        .login-brand-logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            margin-bottom: 12px;
        }
        .input-group-text {
            background: #f8fafc;
            border-color: var(--border-color);
            color: var(--text-muted);
        }
        .password-toggle-btn {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="/<?= htmlspecialchars(ltrim(Config::LOGO_PATH, '/'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="login-brand-logo">
        <h2 class="fw-bold mb-1" style="color: var(--text-main); font-size: 1.6rem;">🎓 <?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="text-muted small mb-0"><?= htmlspecialchars(Config::INSTITUTION_NAME, ENT_QUOTES, 'UTF-8') ?></p>
        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold mt-2 px-3 py-1 rounded-pill">Yönetim Paneli Girişi</span>
    </div>

    <!-- Hata Bildirimi -->
    <div id="loginError" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>

    <form id="loginForm" name="loginForm" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="mb-3">
            <label for="userName" class="form-label">Kullanıcı Adı</label>
            <div class="input-group">
                <span class="input-group-text">👤</span>
                <input type="text" class="form-control" id="userName" name="userName" placeholder="Kullanıcı adınızı girin" required autofocus autocomplete="username">
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Şifre</label>
            <div class="input-group">
                <span class="input-group-text">🔒</span>
                <input type="password" class="form-control" id="password" name="password" placeholder="Şifrenizi girin" required autocomplete="current-password">
                <button type="button" class="input-group-text password-toggle-btn" id="togglePasswordBtn" title="Şifreyi Göster/Gizle">👁️</button>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" id="loginBtn" class="btn btn-primary py-2 fw-bold" style="border-radius: var(--radius-md);">
                Giriş Yap
            </button>
        </div>
    </form>

    <div class="text-center mt-4 pt-2 border-top">
        <a href="/" class="small text-decoration-none text-muted" target="_blank">
            📺 Canlı Kampüs Panosunu Aç ↗
        </a>
    </div>
</div>

<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script>
    // Şifre Göster / Gizle
    document.getElementById('togglePasswordBtn').addEventListener('click', function () {
        var pwdInput = document.getElementById('password');
        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            this.textContent = '🙈';
        } else {
            pwdInput.type = 'password';
            this.textContent = '👁️';
        }
    });

    // Form Gönderimi (Modern Native Fetch)
    document.getElementById('loginForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        var errorDiv = document.getElementById("loginError");
        var btn = document.getElementById("loginBtn");
        
        errorDiv.classList.add("d-none");
        errorDiv.textContent = "";

        var userName = document.getElementById("userName").value.trim();
        var password = document.getElementById("password").value;

        if (userName.length === 0 || password.length === 0) {
            errorDiv.textContent = "Lütfen kullanıcı adı ve şifrenizi giriniz.";
            errorDiv.classList.remove("d-none");
            return;
        }

        var formData = new FormData(this);
        formData.append('functionName', 'login');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Giriş yapılıyor...';

        try {
            var response = await fetch("/admin/login", {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            var data = await response.json();
            btn.disabled = false;
            btn.textContent = "Giriş Yap";

            if (data.error) {
                errorDiv.textContent = data.error;
                errorDiv.classList.remove("d-none");
            } else if (data.redirect) {
                window.location.replace(data.redirect);
            } else {
                window.location.replace("/admin");
            }
        } catch (err) {
            btn.disabled = false;
            btn.textContent = "Giriş Yap";
            errorDiv.textContent = "Giriş başarısız. Lütfen bilgilerinizi kontrol edin.";
            errorDiv.classList.remove("d-none");
        }
    });
</script>
</body>
</html>
