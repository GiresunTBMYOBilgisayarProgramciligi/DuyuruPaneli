<?php
declare(strict_types=1);
?>
<div class="tab-pane fade" id="settingsTabContent" role="tabpanel" aria-labelledby="settings-tab">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                <span>⚙️</span> Sistem ve Görsel Optimizasyon Ayarları
            </h4>
            <p class="text-muted small mb-0">
                Pano vitrini, görsel sıkıştırma kuralları, API anahtarları ve modül yapılandırmalarını buradan yönetebilirsiniz.
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm" id="saveSettingsBtn">
                <span>💾</span> Ayarları Kaydet
            </button>
        </div>
    </div>

    <form id="settingsForm" autocomplete="off">
        <div class="row g-4">
            <!-- 1. GÖRSEL OPTİMİZASYONU VE TINYPNG (ISSUE #11) -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <span>🖼️</span> Görsel Optimizasyonu & TinyPNG
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small" id="optDriverBadge">
                            Hibrit Mod
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <!-- Optimizasyon Sürücüsü -->
                        <div class="mb-3">
                            <label for="setting_image_driver" class="form-label fw-semibold small text-muted">
                                Optimizasyon Modu (Sürücü)
                            </label>
                            <select class="form-select" id="setting_image_driver" name="image_driver">
                                <option value="hybrid">Hibrit (TinyPNG API + Yerel GD Yedekli) — Önerilen</option>
                                <option value="gd">Yalnızca Yerel GD Motoru (Limitsiz, API Gerektirmez)</option>
                                <option value="tinypng">Yalnızca TinyPNG API (Kota/Ağ Kesintisinde Hata Verir)</option>
                                <option value="off">Devre Dışı (Görselleri Sıkıştırmadan Ham Kaydet)</option>
                            </select>
                            <div class="form-text small text-muted">
                                <strong>Hibrit Mod:</strong> API anahtarı tanımlıysa TinyPNG ile ultra sıkıştırma yapar; kota dolduğunda veya ağ koptuğunda kesintisiz yerel GD motoruna geçer.
                            </div>
                        </div>

                        <!-- Yeniden Boyutlandırma (Resize) -->
                        <div class="mb-3">
                            <label for="setting_image_resize_dimension" class="form-label fw-semibold small text-muted">
                                Maksimum Boyutlandırma Sınırı (Resize Sınırı)
                            </label>
                            <select class="form-select" id="setting_image_resize_dimension" name="image_resize_dimension">
                                <option value="1920">1920 px (Full HD Kiosk Standardı — Tavsiye Edilen)</option>
                                <option value="1600">1600 px (Geniş Ekranlar)</option>
                                <option value="1280">1280 px (Standart HD)</option>
                                <option value="1024">1024 px (Düşük Bant Genişliği)</option>
                                <option value="0">Boyutlandırma Yapma (Orijinal Çözünürlüğü Koru)</option>
                            </select>
                            <div class="form-text small text-muted">
                                1920px üzerindeki 4K/6K görseller otomatik olarak en-boy oranı korunarak Full HD standardına çekilir.
                            </div>
                        </div>

                        <!-- Görsel Kalitesi -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="setting_image_quality" class="form-label fw-semibold small text-muted mb-0">
                                    Sıkıştırma Kalitesi (Quality)
                                </label>
                                <span class="badge bg-light text-dark border" id="imageQualityVal">85%</span>
                            </div>
                            <input type="range" class="form-range" id="setting_image_quality" name="image_quality" min="50" max="100" step="5" value="85">
                            <div class="d-flex justify-content-between small text-muted">
                                <span>%50 (Yüksek Sıkıştırma)</span>
                                <span>%85 (Optimum)</span>
                                <span>%100 (Kayıpsıza Yakın)</span>
                            </div>
                        </div>

                        <!-- TinyPNG API Anahtarı -->
                        <div class="mb-3 pt-2 border-top">
                            <label for="setting_tinypng_api_key" class="form-label fw-semibold small text-muted d-flex justify-content-between align-items-center">
                                <span>TinyPNG API Anahtarı</span>
                                <a href="https://tinypng.com/developers" target="_blank" class="small text-decoration-none" title="Ücretsiz API anahtarı edinin">
                                    Ücretsiz Anahtar Al ↗
                                </a>
                            </label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light">🔑</span>
                                <input type="password" class="form-control" id="setting_tinypng_api_key" name="tinypng_api_key" placeholder="Örn: aBC123xyz..." autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleApiKeyVisibility" title="Anahtarı Göster/Gizle">
                                    👁️
                                </button>
                                <button class="btn btn-outline-primary" type="button" id="testTinyPngBtn" title="TinyPNG API bağlantısını ve aylık kotayı sına">
                                    Sına & Kota Kontrolü
                                </button>
                            </div>
                            <div id="tinyPngTestResult" class="d-none alert py-2 px-3 small mb-2"></div>
                            <div class="alert alert-light border py-2 px-3 small mb-0 text-muted d-flex align-items-center gap-2">
                                <span>🔒</span>
                                <div>
                                    <strong>Gizlilik Güvencesi:</strong> API anahtarınız doğrudan yerel veritabanında ve <code>.env</code> dosyasında saklanır, asla Git sürüm kontrolüne dahil edilmez.
                                </div>
                            </div>
                            <div class="form-text small text-primary mt-2">
                                <span>💡</span> <strong>Akıllı Otomasyon:</strong> <em>"Sına & Kota Kontrolü"</em> butonuna tıkladığınızda doğrulanan anahtar doğrudan <code>.env</code> dosyasına ve sisteme otomatik olarak anında kaydedilir.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. KİOSK VE EKRAN YAPILANDIRMASI -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <span>📺</span> Kiosk Pano & Ekran Yönetimi
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Slayt Geçiş Süresi -->
                        <div class="mb-3">
                            <label for="setting_slide_interval" class="form-label fw-semibold small text-muted">
                                Slayt Geçiş Süresi (Saniye)
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="setting_slide_interval" name="slide_interval" min="5" max="300" value="20">
                                <span class="input-group-text bg-light">saniye</span>
                            </div>
                            <div class="form-text small text-muted">
                                Her afişin Kiosk ekranında kaç saniye görüntüleneceğini belirler (Varsayılan: 20 sn).
                            </div>
                        </div>

                        <!-- Video Afişlerinde Ses Durumu -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted d-block">
                                Video Afişlerinde Ses Durumu
                            </label>
                            <div class="form-check form-switch fs-6">
                                <input class="form-check-input" type="checkbox" id="setting_kiosk_video_sound" name="kiosk_video_sound" value="1">
                                <label class="form-check-label small" for="setting_kiosk_video_sound">
                                    YouTube video afişleri başladığında ses varsayılan olarak açık olsun
                                </label>
                            </div>
                        </div>

                        <!-- Kiosk Modülleri -->
                        <div class="pt-3 border-top">
                            <label class="form-label fw-semibold small text-muted d-block mb-2">
                                Kiosk Ekranında Gösterilecek Modüller
                            </label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="setting_module_weather" name="module_weather" value="1">
                                        <label class="form-check-label small" for="setting_module_weather">
                                            🌦️ Hava Durumu Widget'ı
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="setting_module_clock" name="module_clock" value="1">
                                        <label class="form-check-label small" for="setting_module_clock">
                                            🕒 Canlı Saat & Tarih
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="setting_module_ticker" name="module_ticker" value="1">
                                        <label class="form-check-label small" for="setting_module_ticker">
                                            📢 Kayan Duyuru Alt Bandı
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="setting_module_qr_analytics" name="module_qr_analytics" value="1">
                                        <label class="form-check-label small" for="setting_module_qr_analytics">
                                            📊 QR Okutma Analitiği
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. KURUMSAL BİLGİLER VE HAVA DURUMU KONUMU -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <span>🏛️</span> Kurumsal Bilgiler & Hava Durumu Konumu
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="setting_institution_name" class="form-label fw-semibold small text-muted">
                                Kurum / Üniversite Adı
                            </label>
                            <input type="text" class="form-control" id="setting_institution_name" name="institution_name">
                        </div>

                        <div class="mb-3">
                            <label for="setting_campus_name" class="form-label fw-semibold small text-muted">
                                Kampüs / Fakülte / MYO Adı
                            </label>
                            <input type="text" class="form-control" id="setting_campus_name" name="campus_name">
                        </div>

                        <div class="mb-3">
                            <label for="setting_app_tagline" class="form-label fw-semibold small text-muted">
                                Pano Sloganı / Alt Başlık
                            </label>
                            <input type="text" class="form-control" id="setting_app_tagline" name="app_tagline">
                        </div>

                        <div class="pt-2 border-top">
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                    <label for="setting_weather_city" class="form-label fw-semibold small text-muted">
                                        Hava Durumu Şehri
                                    </label>
                                    <input type="text" class="form-control" id="setting_weather_city" name="weather_city">
                                </div>
                                <div class="col-6 col-sm-4">
                                    <label for="setting_weather_latitude" class="form-label fw-semibold small text-muted">
                                        Enlem (Lat)
                                    </label>
                                    <input type="text" class="form-control" id="setting_weather_latitude" name="weather_latitude">
                                </div>
                                <div class="col-6 col-sm-4">
                                    <label for="setting_weather_longitude" class="form-label fw-semibold small text-muted">
                                        Boylam (Lon)
                                    </label>
                                    <input type="text" class="form-control" id="setting_weather_longitude" name="weather_longitude">
                                </div>
                            </div>
                            <div class="form-text small text-muted mt-1">
                                Open-Meteo REST API koordinatlarıdır.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. SİSTEM & MOTOR DURUMU (SYSTEM HEALTH) -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <span>⚡</span> Sistem Sağlığı & Optimizasyon Motorları
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="fw-semibold">PHP Versiyonu:</span>
                                <span class="badge bg-secondary font-monospace" id="sysPhpVer">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="fw-semibold">PHP GD Kütüphanesi:</span>
                                <span class="badge bg-success" id="sysGdStatus">Kontrol ediliyor...</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="fw-semibold">WebP Desteği:</span>
                                <span class="badge bg-success" id="sysWebpStatus">Kontrol ediliyor...</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="fw-semibold">cURL Desteği (API İstekleri):</span>
                                <span class="badge bg-success" id="sysCurlStatus">Kontrol ediliyor...</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="fw-semibold">TinyPNG API Durumu:</span>
                                <span class="badge bg-secondary" id="sysTinyPngStatus">Tanımlanmamış</span>
                            </li>
                        </ul>

                        <div class="alert alert-info border-0 rounded-3 mt-3 p-3 small mb-0">
                            <strong>💡 Bilgi:</strong>
                            Yüklenen afişler otomatik olarak belirlenen çözünürlüğe ölçeklenir ve metaverileri temizlenir. YouTube bağlantılı video afişlerinde ise orijinal YouTube kapak görseli kullanılır ve optimizasyon yapılmaz.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">
            <span class="text-muted small">Tüm ayarların kalıcı olarak geçerli olması için:</span>
            <button type="button" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm" id="saveSettingsBtnBottom">
                <span>💾</span> Ayarları Kaydet
            </button>
        </div>
    </form>
</div>
