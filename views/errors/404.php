<?php
declare(strict_types=1);

use App\Config;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>404 Sayfa Bulunamadı | <?= htmlspecialchars(Config::APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <style>
        body {
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            text-align: center;
            padding: 2rem;
        }
        .error-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 3rem 2rem;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .error-code {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #38bdf8 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">404</div>
        <h2 class="fw-bold mb-2">Sayfa Bulunamadı</h2>
        <p class="text-secondary small mb-4">Aradığınız bağlantı silinmiş, taşınmış veya hiç var olmamış olabilir.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="/" class="btn btn-primary px-4 fw-semibold">📺 Canlı Panoya Dön</a>
            <a href="/admin" class="btn btn-outline-light px-4 fw-semibold">⚙️ Yönetim Paneli</a>
        </div>
    </div>
</body>
</html>
