#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
🎓 UniPano - Dijital Kampüs Bilgilendirme Panosu Kiosk Başlatıcısı
==================================================================
Harici / İkincil ekranda (TV, Projeksiyon, İkinci Monitör) UniPano'yu tam ekran
kiosk modunda açan, SIFIR EK KÜTÜPHANE GEREKSİNİMLİ (pip gerektirmeyen) başlatıcı.

Özellikler:
- Ek paket gerektirmez (Sadece standart Python kütüphaneleri kullanılır).
- Çoklu monitör algılama: İkincil ekranı otomatik tespit edip pencereyi oraya konumlandırır.
- Tarayıcı motoru: Chrome, Chromium, Edge ve Brave'i tam ekran Kiosk modunda çalıştırır.
- Ses ve video optimizasyonu: YouTube ve medya afişleri için otomatik ses bayrağını etkinleştirir.
- Açılışta otomatik başlama desteği: --autostart parametresi ile sistem başlangıcına ekler.
- Ağ / Sunucu bekleme koruması: Bilgisayar ilk açıldığında sunucu yanıt verene kadar bekler.
"""

import os
import sys
import subprocess
import shutil
import platform
import tempfile
import time
import re
import urllib.request
import urllib.error
import argparse


# ==============================================================================
# 1. MONİTÖR VE EKRAN ALGILAMA MOTORU (STANDART KÜTÜPHANE - SIFIR PIP)
# ==============================================================================

def get_monitors_linux():
    """
    Linux (X11 / XWayland) ortamında xrandr ile monitörleri tespit eder.
    """
    monitors = []
    try:
        out = subprocess.check_output(['xrandr', '--query'], stderr=subprocess.DEVNULL).decode('utf-8', errors='ignore')
        # Örnek satırlar:
        # eDP-1 connected primary 1920x1080+0+0 ...
        # HDMI-1 connected 1920x1080+1920+0 ...
        pattern = r'(\S+)\s+connected(?:\s+(primary))?.*?(\d+)x(\d+)\+(\d+)\+(\d+)'
        for match in re.finditer(pattern, out):
            name = match.group(1)
            is_primary = bool(match.group(2))
            width = int(match.group(3))
            height = int(match.group(4))
            x = int(match.group(5))
            y = int(match.group(6))
            monitors.append({
                'name': name,
                'primary': is_primary,
                'width': width,
                'height': height,
                'x': x,
                'y': y
            })
    except Exception:
        pass
    return monitors


def get_monitors_windows():
    """
    Windows ortamında harici kütüphane olmadan standart ctypes API ile
    tüm bağlı monitörleri ve koordinatlarını tespit eder.
    """
    monitors = []
    try:
        import ctypes
        from ctypes import wintypes

        CCHDEVICENAME = 32

        class MONITORINFOEXW(ctypes.Structure):
            _fields_ = [
                ('cbSize', wintypes.DWORD),
                ('rcMonitor', wintypes.RECT),
                ('rcWork', wintypes.RECT),
                ('dwFlags', wintypes.DWORD),
                ('szDevice', wintypes.WCHAR * CCHDEVICENAME)
            ]

        MONITORENUMPROC = ctypes.WINFUNCTYPE(
            wintypes.BOOL,
            wintypes.HMONITOR,
            wintypes.HDC,
            ctypes.POINTER(wintypes.RECT),
            wintypes.LPARAM
        )

        def monitor_enum_callback(hMonitor, hdcMonitor, lprcMonitor, dwData):
            info = MONITORINFOEXW()
            info.cbSize = ctypes.sizeof(MONITORINFOEXW)
            if ctypes.windll.user32.GetMonitorInfoW(hMonitor, ctypes.byref(info)):
                is_primary = bool(info.dwFlags & 1)  # MONITORINFOF_PRIMARY = 1
                rect = info.rcMonitor
                monitors.append({
                    'name': str(info.szDevice).strip('\x00') or f"Ekran {len(monitors)+1}",
                    'primary': is_primary,
                    'x': int(rect.left),
                    'y': int(rect.top),
                    'width': int(rect.right - rect.left),
                    'height': int(rect.bottom - rect.top)
                })
            return True

        callback = MONITORENUMPROC(monitor_enum_callback)
        ctypes.windll.user32.EnumDisplayMonitors(None, None, callback, 0)
    except Exception:
        pass
    return monitors


def get_all_monitors():
    """
    İşletim sistemine uygun yöntemi çağırarak bağlı ekranları döner.
    """
    system = platform.system()
    monitors = []

    if system == "Windows":
        monitors = get_monitors_windows()
    elif system == "Linux":
        monitors = get_monitors_linux()

    # İsteğe bağlı kurulu screeninfo kütüphanesi varsa ek güvence olarak dene
    if not monitors:
        try:
            import screeninfo
            for m in screeninfo.get_monitors():
                monitors.append({
                    'name': getattr(m, 'name', 'Ekran'),
                    'primary': getattr(m, 'is_primary', False),
                    'x': m.x,
                    'y': m.y,
                    'width': m.width,
                    'height': m.height
                })
        except ImportError:
            pass

    # Hiçbir yöntem sonuç vermezse güvenli varsayılan oluştur
    if not monitors:
        monitors = [{
            'name': 'Varsayılan Ekran',
            'primary': True,
            'x': 0,
            'y': 0,
            'width': 1920,
            'height': 1080
        }]

    return monitors


def select_target_monitor(monitors, force_primary=False, force_secondary=True, monitor_index=None):
    """
    Kullanıcı tercihine ve ekran durumuna göre hedef monitörü seçer.
    Varsayılan olarak ikincil harici ekranı (TV / Projeksiyon) tercih eder.
    """
    if not monitors:
        return {'name': 'Varsayılan', 'primary': True, 'x': 0, 'y': 0, 'width': 1920, 'height': 1080, 'is_secondary': False}

    # Belirli bir monitör indeksi istendiyse
    if monitor_index is not None and 0 <= monitor_index < len(monitors):
        res = dict(monitors[monitor_index])
        res['is_secondary'] = not res['primary']
        return res

    # Sadece ana ekran istendiyse
    if force_primary:
        for m in monitors:
            if m['primary']:
                res = dict(m)
                res['is_secondary'] = False
                return res
        res = dict(monitors[0])
        res['is_secondary'] = False
        return res

    # İkincil harici ekranı bul (Tercih edilen Kiosk modu)
    if force_secondary and len(monitors) > 1:
        for m in monitors:
            if not m['primary']:
                res = dict(m)
                res['is_secondary'] = True
                return res

    # Harici ekran yoksa ana ekranı kullan
    for m in monitors:
        if m['primary']:
            res = dict(m)
            res['is_secondary'] = False
            return res

    res = dict(monitors[0])
    res['is_secondary'] = False
    return res


# ==============================================================================
# 2. TARAYICI BULMA MOTORU (CHROMIUM TABANLI KIOSK)
# ==============================================================================

def find_kiosk_browser():
    """
    Sistemde yüklü Chromium tabanlı tarayıcıları tespit eder.
    (Chrome, Chromium, Edge, Brave vb. --kiosk ve --window-position destekler)
    """
    system = platform.system()
    candidates = []

    if system == "Windows":
        local_app_data = os.environ.get("LOCALAPPDATA", r"C:\Users\Default\AppData\Local")
        program_files = os.environ.get("ProgramFiles", r"C:\Program Files")
        program_files_x86 = os.environ.get("ProgramFiles(x86)", r"C:\Program Files (x86)")

        candidates = [
            # Google Chrome
            os.path.join(program_files, r"Google\Chrome\Application\chrome.exe"),
            os.path.join(program_files_x86, r"Google\Chrome\Application\chrome.exe"),
            os.path.join(local_app_data, r"Google\Chrome\Application\chrome.exe"),
            # Microsoft Edge (Windows 10/11'de %100 varsayılan olarak bulunur)
            os.path.join(program_files, r"Microsoft\Edge\Application\msedge.exe"),
            os.path.join(program_files_x86, r"Microsoft\Edge\Application\msedge.exe"),
            # Brave
            os.path.join(program_files, r"BraveSoftware\Brave-Browser\Application\brave.exe"),
            os.path.join(local_app_data, r"BraveSoftware\Brave-Browser\Application\brave.exe"),
        ]
        for name in ["chrome.exe", "msedge.exe", "brave.exe"]:
            p = shutil.which(name)
            if p:
                candidates.insert(0, p)

    elif system == "Darwin":  # macOS
        candidates = [
            "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome",
            "/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge",
            "/Applications/Brave Browser.app/Contents/MacOS/Brave Browser",
            "/Applications/Chromium.app/Contents/MacOS/Chromium",
        ]

    else:  # Linux
        for name in [
            "google-chrome",
            "google-chrome-stable",
            "chromium",
            "chromium-browser",
            "microsoft-edge",
            "microsoft-edge-stable",
            "brave-browser",
            "firefox"  # Son fallback
        ]:
            p = shutil.which(name)
            if p:
                candidates.append(p)

    for path in candidates:
        if path and os.path.isfile(path) and (system == "Windows" or os.access(path, os.X_OK)):
            return path

    return None


# ==============================================================================
# 3. BAĞLANTI & SUNUCU BEKLEME MOTORU (AÇILIŞTA DONMA KORUMASI)
# ==============================================================================

def check_url_active(url, timeout=2):
    """
    Verilen URL'nin HTTP 200/301/302 ile yanıt verip vermediğini kontrol eder.
    """
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'UniPano-Kiosk-Check/1.0'})
        with urllib.request.urlopen(req, timeout=timeout) as response:
            return response.status in (200, 301, 302, 307, 308)
    except Exception:
        return False


def wait_for_server(url, max_retries=15, delay=1.5):
    """
    Bilgisayar ilk açıldığında yerel sunucunun veya ağ bağlantısının
    hazır olması için belirli bir süre yanıt bekler.
    """
    print(f"⏳ Sunucu bağlantısı kontrol ediliyor: {url}")
    for i in range(1, max_retries + 1):
        if check_url_active(url):
            print(f"✅ Sunucu hazır! ({url})")
            return True
        print(f"   [{i}/{max_retries}] Sunucu henüz hazır değil, bekleniyor...")
        time.sleep(delay)

    print("⚠️ Sunucu yanıt zaman aşımına uğradı, yine de Kiosk açılıyor.")
    return False


def try_start_local_php_server(project_dir, port=8000):
    """
    Hedef adres localhost:8000 ise ve portta dinleyen servis yoksa,
    bilgisayardaki PHP ile arka planda yerel test sunucusunu başlatır.
    """
    php_path = shutil.which("php")
    if not php_path:
        return None

    try:
        cmd = [php_path, "-S", f"127.0.0.1:{port}", "-t", project_dir]
        proc = subprocess.Popen(
            cmd,
            cwd=project_dir,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL
        )
        time.sleep(1.0)
        return proc
    except Exception:
        return None


# ==============================================================================
# 4. SİSTEM BAŞLANGICINA EKLEME / KALDIRMA MOTORU (AUTOSTART)
# ==============================================================================

def setup_autostart(enable=True, target_url=None):
    """
    İşletim sistemine göre UniPano'yu başlangıç programlarına ekler veya kaldırır.
    Linux: ~/.config/autostart/unipano.desktop
    Windows: %APPDATA%/Microsoft/Windows/Start Menu/Programs/Startup/unipano_kiosk.bat
    """
    system = platform.system()
    script_path = os.path.abspath(__file__)
    project_dir = os.path.dirname(script_path)

    if system == "Linux":
        autostart_dir = os.path.expanduser("~/.config/autostart")
        desktop_file = os.path.join(autostart_dir, "unipano.desktop")

        if not enable:
            if os.path.exists(desktop_file):
                os.remove(desktop_file)
                print(f"🗑️ UniPano sistem başlangıcından kaldırıldı: {desktop_file}")
            else:
                print("ℹ️ UniPano zaten başlangıç listesinde bulunmuyor.")
            return True

        os.makedirs(autostart_dir, exist_ok=True)
        py_exec = sys.executable

        exec_cmd = f'{py_exec} "{script_path}"'
        if target_url:
            exec_cmd += f' "{target_url}"'

        content = f"""[Desktop Entry]
Type=Application
Version=1.0
Name=UniPano Kiosk
GenericName=Dijital Bilgilendirme Panosu
Comment=UniPano Dijital Kampüs Panosunu İkincil Ekranda Kiosk Modunda Başlatır
Exec={exec_cmd}
Path={project_dir}
Terminal=false
StartupNotify=false
Hidden=false
X-GNOME-Autostart-enabled=true
"""
        with open(desktop_file, "w", encoding="utf-8") as f:
            f.write(content)

        print(f"✅ UniPano Linux başlangıç programlarına başarıyla eklendi:")
        print(f"   📁 Dosya: {desktop_file}")
        print("   Bilgisayar her açıldığında ikincil harici ekranda UniPano otomatik başlayacaktır.")
        return True

    elif system == "Windows":
        appdata = os.environ.get("APPDATA")
        if not appdata:
            print("❌ Windows APPDATA dizini bulunamadı.")
            return False

        startup_dir = os.path.join(appdata, r"Microsoft\Windows\Start Menu\Programs\Startup")
        bat_file = os.path.join(startup_dir, "unipano_kiosk.bat")

        if not enable:
            if os.path.exists(bat_file):
                os.remove(bat_file)
                print(f"🗑️ UniPano başlangıçtan kaldırıldı: {bat_file}")
            else:
                print("ℹ️ UniPano zaten Windows başlangıcında bulunmuyor.")
            return True

        os.makedirs(startup_dir, exist_ok=True)
        py_exec = sys.executable

        run_cmd = f'start "" "{py_exec}" "{script_path}"'
        if target_url:
            run_cmd += f' "{target_url}"'

        content = f"""@echo off
rem ========================================================
rem UniPano Kiosk Otomatik Baslatici
rem ========================================================
cd /d "{project_dir}"
timeout /t 5 /nobreak >nul
{run_cmd}
exit
"""
        with open(bat_file, "w", encoding="utf-8") as f:
            f.write(content)

        print(f"✅ UniPano Windows başlangıç klasörüne başarıyla eklendi:")
        print(f"   📁 Dosya: {bat_file}")
        print("   Bilgisayar her açıldığında harici ekranda UniPano otomatik başlayacaktır.")
        return True

    else:
        print(f"⚠️ {system} işletim sistemi için otomatik başlangıç manuel ayarlanmalıdır.")
        return False


def is_autostart_enabled():
    """
    Sistem başlangıcının aktif olup olmadığını kontrol eder.
    """
    system = platform.system()
    if system == "Linux":
        return os.path.exists(os.path.expanduser("~/.config/autostart/unipano.desktop"))
    elif system == "Windows":
        appdata = os.environ.get("APPDATA")
        if appdata:
            return os.path.exists(os.path.join(appdata, r"Microsoft\Windows\Start Menu\Programs\Startup\unipano_kiosk.bat"))
    return False


# ==============================================================================
# 5. KIOSK ÇALIŞTIRMA VE SÜREÇ YÖNETİMİ
# ==============================================================================

def launch_kiosk(browser_path, url, monitor):
    """
    Chromium tabanlı tarayıcıyı izole profil ve ikincil ekran koordinatlarıyla
    Kiosk modunda başlatır.
    """
    x = monitor.get("x", 0)
    y = monitor.get("y", 0)
    w = monitor.get("width", 1920)
    h = monitor.get("height", 1080)

    # Ayrı profil dizini: Kullanıcının normal Chrome oturumuyla çakışmaz!
    profile_dir = os.path.join(tempfile.gettempdir(), "unipano_kiosk_profile")
    os.makedirs(profile_dir, exist_ok=True)

    is_firefox = "firefox" in os.path.basename(browser_path).lower()

    if is_firefox:
        cmd = [browser_path, "--kiosk", url]
    else:
        cmd = [
            browser_path,
            f"--user-data-dir={profile_dir}",
            "--kiosk",
            f"--window-position={x},{y}",
            f"--window-size={w},{h}",
            "--autoplay-policy=no-user-gesture-required",  # Video afişlerinde tık gerekmeden ses çalma
            "--noerrdialogs",                              # Hata diyaloglarını gizle
            "--disable-infobars",                          # Bilgi çubuklarını gizle
            "--disable-session-crashed-bubble",            # Çökme/kurtarma balonunu gizle
            "--check-for-update-interval=31536000",        # Güncelleme uyarısı çıkarma
            "--disable-pinch",                             # Dokunmatik ekran yakınlaştırmasını kilitle
            "--overscroll-history-navigation=0",           # Geri/ileri kaydırma jestini engelle
            url
        ]

    try:
        proc = subprocess.Popen(cmd)
        return proc
    except Exception as e:
        print(f"❌ Kiosk tarayıcısı başlatılamadı: {e}")
        return None


def show_status():
    """
    Mevcut monitörleri, tarayıcıyı ve başlangıç durumunu ekrana yazdırır.
    """
    monitors = get_all_monitors()
    target = select_target_monitor(monitors, force_secondary=True)
    browser = find_kiosk_browser()
    autostart = is_autostart_enabled()

    print("\n" + "=" * 60)
    print("🎓 UniPano - Kiosk Donanım ve Sistem Durumu Raporu")
    print("=" * 60)
    print(f"💻 İşletim Sistemi : {platform.system()} ({platform.release()})")
    print(f"🚀 Kiosk Tarayıcısı : {browser or 'Bulunamadı! (Chrome veya Edge yükleyiniz)'}")
    print(f"🔄 Otomatik Başlama : {'✅ AKTİF (Açılışta otomatik çalışır)' if autostart else '⚪ Pasif (Açmak için: python Start.py --autostart)'}")
    print("\n📺 Algılanan Monitörler:")
    for idx, m in enumerate(monitors, 1):
        pri_label = "[Ana Ekran]" if m['primary'] else "[Harici TV / İkincil Ekran]"
        is_sel = "👉 (HEDEF OLARAK SEÇİLDİ)" if m['x'] == target['x'] and m['y'] == target['y'] else ""
        print(f"  [{idx}] {m['name']:<12}: {m['width']}x{m['height']} (Konum: X={m['x']}, Y={m['y']}) {pri_label} {is_sel}")

    print("\n🎯 Kiosk Yayın Hedefi:")
    print(f"  -> Ekran     : {target['name']} ({'İkincil TV' if target.get('is_secondary') else 'Ana Ekran'})")
    print(f"  -> Konum     : X={target['x']}, Y={target['y']}")
    print(f"  -> Çözünürlük: {target['width']}x{target['height']}")
    print("=" * 60 + "\n")


# ==============================================================================
# 6. ANA PROGRAM GİRİŞ NOKTASI
# ==============================================================================

def main():
    parser = argparse.ArgumentParser(
        description="UniPano - Dijital Kampüs Panosu Kiosk Başlatıcısı",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""Örnek Kullanım Şekilleri:
  python Start.py                        # İkincil ekranda UniPano'yu başlatır
  python Start.py https://pano.okul.edu.tr # Belirtilen adresi başlatır
  python Start.py --autostart            # Bilgisayar açılışına otomatik ekler
  python Start.py --disable-autostart    # Açılıştan kaldırır
  python Start.py --status               # Monitörleri ve ayarları listeler
  python Start.py --primary              # Zorunlu olarak ana ekranda açar
"""
    )

    parser.add_argument("url", nargs="?", default=None, help="Kiosk hedef web adresi (Varsayılan: unipano.loc veya localhost)")
    parser.add_argument("--autostart", action="store_true", help="Bilgisayar açıldığında otomatik başlamasını sağlar")
    parser.add_argument("--disable-autostart", action="store_true", help="Otomatik başlangıç kaydını siler")
    parser.add_argument("--status", action="store_true", help="Bağlı monitör ve sistem bilgilerini gösterip çıkar")
    parser.add_argument("--primary", action="store_true", help="Harici ekran yerine zorunlu olarak ana ekranda başlat")
    parser.add_argument("--secondary", action="store_true", default=True, help="Varsayılan: Varsa ikincil ekranda başlat")
    parser.add_argument("--monitor", type=int, default=None, help="Belirli bir monitör numarası (1, 2, ...)")
    parser.add_argument("--pos", type=str, default=None, help="Manuel pencere koordinatı (örn: 1920,0)")
    parser.add_argument("--no-wait", action="store_true", help="Sunucu bağlantı testini beklemeden doğrudan aç")

    args = parser.parse_args()

    # Durum bilgisi sorgulama
    if args.status:
        show_status()
        return

    # Başlangıca ekleme / çıkarma işlemleri
    if args.disable_autostart:
        setup_autostart(enable=False)
        return

    if args.autostart:
        setup_autostart(enable=True, target_url=args.url)
        return

    # 1. Hedef URL Belirleme
    if args.url:
        target_url = args.url
    else:
        env_url = os.getenv("UNIPANO_URL")
        if env_url:
            target_url = env_url
        else:
            # Akıllı yerel URL seçimi: unipano.loc -> localhost:8000 -> localhost
            if check_url_active("http://unipano.loc/"):
                target_url = "http://unipano.loc/"
            elif check_url_active("http://localhost:8000/"):
                target_url = "http://localhost:8000/"
            elif check_url_active("http://localhost/"):
                target_url = "http://localhost/"
            else:
                target_url = "http://unipano.loc/"

    # 2. Monitör Tespiti ve Hedef Seçimi
    monitors = get_all_monitors()
    target_monitor = select_target_monitor(
        monitors,
        force_primary=args.primary,
        force_secondary=not args.primary,
        monitor_index=(args.monitor - 1) if args.monitor else None
    )

    # Manuel koordinat geçildiyse uygula
    if args.pos:
        try:
            px, py = map(int, args.pos.split(','))
            target_monitor['x'] = px
            target_monitor['y'] = py
        except Exception:
            pass

    # 3. Kiosk Tarayıcısı Tespiti
    browser_path = find_kiosk_browser()

    # Ekran Bilgi Özeti
    print("\n" + "=" * 60)
    print("🎓 UniPano - Dijital Kampüs Panosu Kiosk Başlatıcı")
    print("=" * 60)
    print(f"📌 Hedef URL         : {target_url}")
    print(f"📺 Yayın Ekranı      : {target_monitor['name']} ({'İkincil TV' if target_monitor.get('is_secondary') else 'Ana Ekran'})")
    print(f"📍 Koordinatlar      : X={target_monitor['x']}, Y={target_monitor['y']} ({target_monitor['width']}x{target_monitor['height']})")
    print(f"🌐 Tarayıcı          : {browser_path or 'Standart Web Görünümü'}")
    if not is_autostart_enabled():
        print("💡 İpucu             : Bilgisayar açılışında otomatik başlama için: python Start.py --autostart")
    print("=" * 60 + "\n")

    # 4. Sunucu Hazır Olma Kontrolü (Açılışta Donma Koruması)
    project_dir = os.path.dirname(os.path.abspath(__file__))
    php_proc = None

    if not args.no_wait:
        is_ready = wait_for_server(target_url, max_retries=10, delay=1.0)
        # Yerel 8000 portu kapalıysa ve localhost istenmişse otomatik PHP sunucusu kaldır
        if not is_ready and ("localhost:8000" in target_url or "127.0.0.1:8000" in target_url):
            print("🚀 Yerel PHP sunucusu otomatik başlatılıyor...")
            php_proc = try_start_local_php_server(project_dir, port=8000)
            if php_proc:
                wait_for_server(target_url, max_retries=5, delay=0.8)

    # 5. Kiosk Penceresini Başlat
    kiosk_proc = None
    if browser_path:
        kiosk_proc = launch_kiosk(browser_path, target_url, target_monitor)

    # Tarayıcı bulunamadıysa veya açılamadıysa pywebview fallback'i dene
    if not kiosk_proc:
        try:
            import webview
            print("ℹ️ Standart tarayıcı yerine pywebview motoru devreye alınıyor...")
            webview.create_window(
                title='UniPano - Dijital Kampüs Panosu',
                url=target_url,
                x=target_monitor['x'],
                y=target_monitor['y'],
                fullscreen=True,
                maximized=False,
                on_top=True
            )
            webview.start()
        except ImportError:
            # En temel sistem varsayılan tarayıcısını aç
            import webbrowser
            print("ℹ️ Sistem varsayılan tarayıcısında açılıyor...")
            webbrowser.open(target_url)

    # Süreci takip et ve kapanışta temizle
    if kiosk_proc:
        try:
            kiosk_proc.wait()
        except KeyboardInterrupt:
            print("\n🛑 Kiosk kullanıcı tarafından sonlandırıldı.")
            try:
                kiosk_proc.terminate()
            except Exception:
                pass
        finally:
            if php_proc:
                try:
                    php_proc.terminate()
                except Exception:
                    pass


if __name__ == "__main__":
    main()

