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
    public const LOGO_PATH = "assets/images/logo_230x230.png";

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
    public const TIMEZONE = "Europe/Istanbul";
    public const SLIDE_INTERVAL_MS = 20000;  // Slayt geçiş süresi (20 saniye)
    public const KIOSK_POLL_INTERVAL_MS = 25000; // Arka plan güncelleme kontrolü
    public const KIOSK_VIDEO_SOUND = true;   // Video afişlerinde ses varsayılan olarak açık olsun mu

    public const ROOT_PATH = __DIR__ . "/../";
    public const PATH_TO_SQLITE_FILE = __DIR__ . '/../db/phpsqlite.db';

    public const SESSION_AUTH_KEY = "unipano_user_id";
    public const SESSION_CSRF_KEY = "unipano_csrf_token";
    public const LOGIN_COOKIE_NAME = "unipano_session";

    public const UPLOAD_DIR = __DIR__ . "/../uploads/";
    public const UPLOAD_URL_PREFIX = "uploads/";
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    public const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const MAX_IMAGE_SIZE_BYTES = 10 * 1024 * 1024; // 10MB

    // Loglama Yapılandırması (Monolog)
    public const LOG_DIR = __DIR__ . "/../storage/logs/";
    public const LOG_FILE_PREFIX = "unipano";
    public const LOG_ROTATION = "monthly"; // 30 günlük / aylık rotasyon
    public const LOG_MAX_FILES = 24;       // 2 yıllık saklama (24 ay x 30 gün = 2 yıl)
    public const LOG_RETENTION_DAYS = 730; // 2 yıldan (730 gün) eski log dosyaları otomatik temizlenir
    public const LOG_LEVEL = "DEBUG";      // DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL
}