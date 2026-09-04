#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
UniPano - Dijital Kampüs Panosu Kiosk İstemcisi
Tek veya çift monitör desteği, dinamik URL yapılandırması ve hata korumalı başlatıcı.
"""

import os
import sys
import webview

try:
    import screeninfo
except ImportError:
    screeninfo = None


def get_target_monitor():
    """
    Kiosk ekranı için hedef monitörü seçer.
    İkincil harici ekran (TV/Projeksiyon) varsa onu, yoksa ana ekranı döner.
    """
    if not screeninfo:
        return 0, 0

    try:
        monitors = screeninfo.get_monitors()
        if not monitors:
            return 0, 0

        # İkincil (harici TV) monitör ara
        for monitor in monitors:
            if not monitor.is_primary:
                return monitor.x, monitor.y

        # Tek monitör varsa ana ekranı kullan
        primary = monitors[0]
        return primary.x, primary.y
    except Exception as e:
        print(f"[UniPano] Monitör algılama uyarısı: {e}. Varsayılan ekran (0, 0) kullanılacak.")
        return 0, 0


def main():
    # URL Yapılandırması: Argüman -> Ortam Değişkeni -> Varsayılan unipano.loc
    if len(sys.argv) > 1:
        target_url = sys.argv[1]
    else:
        target_url = os.getenv("UNIPANO_URL", "http://unipano.loc/")

    pos_x, pos_y = get_target_monitor()

    print("==================================================")
    print("🎓 UniPano - Dijital Kampüs Panosu Başlatılıyor")
    print(f"📌 Hedef URL: {target_url}")
    print(f"📺 Monitör Koordinatları: X={pos_x}, Y={pos_y}")
    print("==================================================")

    # Kiosk penceresini aç
    webview.create_window(
        title='UniPano - Dijital Kampüs Panosu',
        url=target_url,
        x=pos_x,
        y=pos_y,
        fullscreen=True,
        maximized=False,
        on_top=True
    )
    webview.start()


if __name__ == "__main__":
    main()
