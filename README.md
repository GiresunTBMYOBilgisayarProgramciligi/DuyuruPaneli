# 🎓 UniPano - Dijital Kampüs Panosu & Duyuru Yönetim Sistemi

Üniversiteler, fakülteler ve kampüsler için geliştirilmiş modern, güvenli, katmanlı mimariye sahip dijital bilgilendirme panosu ve TV kiosk yönetim platformu.

---

## 🚀 Temel Özellikler

- **Katmanlı MVC Mimarisi:**
  `Router -> Middlewares -> Controller -> Validator -> DTO -> Policies -> Services -> Repository -> Model` akışıyla tam kurumsal mimari.
- **Kırpışmasız (Flicker-Free) TV Kiosk Deneyimi:**
  Tam sayfa yenileme (`<meta http-equiv="refresh">`) kaldırıldı. Arka planda periyodik veri senkronizasyonu ve içerik özet kontrolü (hash comparison) ile ekranda beyaz parlama ve titreme olmadan akıcı afiş ve duyuru döngüsü.
- **Çevrimdışı (Offline Fallback) Desteği:**
  Ağ kesintilerinde `localStorage` önbelleğindeki son güncel afiş ve duyurular kesintisiz gösterilmeye devam eder.
- **Dahili QR Kod & Okutulma Analitiği:**
  Dış Bitly bağımlılıkları tamamen kaldırıldı! Dahili SVG/PNG QR motoru ve `/r/{kisaKod}` yönlendiricisi ile her afiş/duyurunun öğrenci ve ziyaretçiler tarafından kaç kez okutulduğu, SHA-256 ile anonimleştirilmiş IP ve cihaz istatistikleriyle takip edilir.
- **Yüksek Güvenlik Standartları:**
  - PDO Parametreli Sorgular (Prepared Statements) ile SQL Injection'a karşı tam koruma.
  - CSRF Token koruması ve Whitelist Action Dispatcher.
  - `HttpOnly` ve `SameSite=Strict` korumalı güvenli oturum yönetimi.
  - MIME type doğrulama ve rastgele benzersiz isimlendirmeli güvenli dosya yükleme servisi (`FileUploadService`).
  - `.htaccess` ile veritabanı, ortam ve gizli dosyalara doğrudan erişim engeli (403 Forbidden).
- **Akıllı Python Kiosk İstemcisi (`Start.py`):**
  İkincil ekranı (TV / Projeksiyon / Kiosk Monitörü) otomatik tespit eder, tek ekranda çökme yaşamadan tam ekran kiosk modunda çalışır.

---

## 🛠️ Gereksinimler

- **PHP:** 8.1 veya üzeri (`pdo_sqlite`, `gd`, `fileinfo` eklentileri aktif olmalı)
- **Web Sunucu:** Apache 2.4+ (mod_rewrite aktif) veya Nginx
- **İstemci Kiosk (İsteğe Bağlı):** Python 3.8+ (`pywebview`, `screeninfo`)

---

## 📦 Kurulum

1. **Projeyi Klonlayın veya İndirin:**
   ```bash
   git clone https://github.com/GiresunTBMYOBilgisayarProgramciligi/DuyuruPaneli.git
   cd DuyuruPaneli
   ```

2. **Dizin İzinlerini Ayarlayın:**
   ```bash
   chmod -R 775 db images
   ```

3. **Apache Sanal Sunucu (VirtualHost) Yapılandırması:**
   `/etc/apache2/sites-available/unipano.loc.conf` dosyasını oluşturun:
   ```apache
   <VirtualHost *:80>
       ServerName unipano.loc
       DocumentRoot /home/kullanici/DuyuruPaneli
       <Directory /home/kullanici/DuyuruPaneli>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   Siteyi aktif edin ve `/etc/hosts` dosyasına ekleyin:
   ```bash
   sudo a2ensite unipano.loc.conf
   sudo systemctl reload apache2
   echo "127.0.0.1 unipano.loc" | sudo tee -a /etc/hosts
   ```

---

## 🔑 Kullanım & Yönetim Paneli

- **Canlı Kampüs Panosu:** `http://unipano.loc/`
- **Yönetim Paneli:** `http://unipano.loc/admin/`

### Varsayılan Giriş Bilgileri:
- **Kullanıcı Adı:** `sametatabasch`
- **Şifre:** `123456` *(İlk girişten sonra güvenlik için değiştirilmelidir)*

---

## 📺 Kiosk Ekranını Başlatma

Kampüs girişindeki ekranda veya Raspberry Pi üzerinde panoyu kiosk modunda açmak için:
```bash
python3 Start.py
```
Veya özel bir URL ile başlatmak için:
```bash
python3 Start.py http://unipano.loc/
```
