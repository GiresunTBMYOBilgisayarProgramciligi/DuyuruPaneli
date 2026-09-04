<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\QrAnalyticsService;

class RedirectController
{
    private QrAnalyticsService $qrAnalyticsService;

    public function __construct()
    {
        $this->qrAnalyticsService = new QrAnalyticsService();
    }

    public function handle(Request $request, string $code): void
    {
        $ip = $request->getIp();
        $userAgent = $request->getUserAgent();
        $referer = $request->getReferer();

        $targetUrl = $this->qrAnalyticsService->handleRedirect($code, $ip, $userAgent, $referer);

        if ($targetUrl) {
            Response::redirect($targetUrl, 302);
        } else {
            http_response_code(404);
            echo "<h1>404 - Geçersiz veya süresi dolmuş QR Link</h1>";
            exit;
        }
    }
}
