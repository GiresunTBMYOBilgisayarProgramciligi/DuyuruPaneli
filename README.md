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
- **Akıllı Kiosk İstemcisi (`Start.py`):**
  İkincil ekranı (Harici TV / Projeksiyon) otomatik tespit eder. **Sıfır ek paket (pip) gerektirir**, Chrome, Edge veya Chromium ile harici ekranda tam ekran kiosk modunda çalışır.

---

## 🛠️ Gereksinimler

- **PHP:** 8.1 veya üzeri (`pdo_sqlite`, `gd`, `fileinfo` eklentileri aktif olmalı)
- **Web Sunucu:** Apache 2.4+ (mod_rewrite aktif) veya Nginx
- **İstemci Kiosk:** Python 3.6+ (Ek hiçbir pip paketi gerektirmez!) ve standart bir tarayıcı (Chrome, Edge, Chromium veya Brave)

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

## 📺 Kiosk Ekranını Başlatma (Harici TV / İkincil Ekran)

Harici bir TV, projeksiyon cihazı veya kiosk ekranında panoyu tam ekran başlatmak için **hiçbir ek kütüphane (pip) kurmanız gerekmez**.

### 1. Doğrudan Çalıştırma
Bağlı bir ikincil ekran (TV/HDMI) varsa sistem onu otomatik algılar ve Kiosk penceresini o ekranda açar:
```bash
# Linux / macOS:
python3 Start.py
# veya tek tıkla: ./start_kiosk.sh

# Windows:
python Start.py
# veya çift tıkla: start_kiosk.bat
```

### 2. Bilgisayar Açıldığında Otomatik Başlatma (Autostart)
Bilgisayar her açıldığında harici ekranda UniPano'nun otomatik başlaması için:
```bash
python3 Start.py --autostart
```
*Bu komut Linux'ta XDG Autostart kaydı, Windows'ta ise Başlangıç klasörü kaydı oluşturur.*

Açılıştan kaldırmak için:
```bash
python3 Start.py --disable-autostart
```

### 3. Monitör ve Sistem Durumunu Görme
Bağlı ekranları, koordinatları ve aktif tarayıcıyı listelemek için:
```bash
python3 Start.py --status
```

### 4. Özel Parametreler
```bash
python3 Start.py https://panonuzun-adresi.edu.tr  # Farklı bir URL açmak için
python3 Start.py --primary                       # Zorunlu olarak ana ekranda açmak için
python3 Start.py --pos 1920,0                    # Belirli koordinatlara yönlendirmek için
```
