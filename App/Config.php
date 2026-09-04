<?php
declare(strict_types=1);

namespace App;

class Config
{
    // Kurumsal Kimlik & Başlık Yapılandırması
    public const INSTITUTION_NAME = "GİRESUN ÜNİVERSİTESİ";
    public const CAMPUS_NAME = "Tirebolu Mehmet Bayrak MYO";
    public const APP_NAME = "UniPano";
    public const APP_TAGLINE = "Dijital Kampüs Bilgilendirme Panosu";
    public const APP_VERSION = "2.1.0";
    public const LOGO_PATH = "images/logo_230x230.png";

    // Modül Açma / Kapama Anahtarları (Farklı Kurumlar İçin Modüler Seçim)
    public const MODULE_WEATHER = true;      // Hava durumu modülü
    public const MODULE_CLOCK = true;        // Canlı tarih & dijital saat modülü
    public const MODULE_TICKER = true;       // Alt kayan duyuru bandı
    public const MODULE_QR_ANALYTICS = true; // QR kod okutulma analitiği

    // Hava Durumu Yapılandırması (Open-Meteo API - Ücretsiz & Limitsiz)
    public const WEATHER_CITY = "Tirebolu";
    public const WEATHER_LATITUDE = 41.0064;
    public const WEATHER_LONGITUDE = 38.8142;
    public const WEATHER_CACHE_TTL = 900;    // 15 dakika (saniye)

    // Kiosk Döngü Yapılandırması
    public const SLIDE_INTERVAL_MS = 20000;  // Slayt geçiş süresi (20 saniye)
    public const KIOSK_POLL_INTERVAL_MS = 25000; // Arka plan güncelleme kontrolü

    public const ROOT_PATH = __DIR__ . "/../";
    public const PATH_TO_SQLITE_FILE = __DIR__ . '/../db/phpsqlite.db';

    public const SESSION_AUTH_KEY = "unipano_user_id";
    public const SESSION_CSRF_KEY = "unipano_csrf_token";
    public const LOGIN_COOKIE_NAME = "unipano_session";

    public const UPLOAD_DIR = __DIR__ . "/../images/";
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    public const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const MAX_IMAGE_SIZE_BYTES = 10 * 1024 * 1024; // 10MB
}