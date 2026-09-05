<?php
declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use App\Controllers\RedirectController;
use App\Core\Request;

$request = new Request();
$code = (string)$request->input('c', '');

if (empty($code)) {
    http_response_code(400);
    echo "<h1>Geçersiz QR Kod Bağlantısı</h1>";
    exit;
}

$controller = new RedirectController();
$controller->handle($request, $code);
