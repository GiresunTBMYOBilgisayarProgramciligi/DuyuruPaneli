#!/usr/bin/env bash
# ==============================================================================
# 🎓 UniPano - Kiosk Başlatıcı (Linux / macOS)
# Harici TV / İkincil ekranda tek tıkla tam ekran Kiosk başlatır.
# ==============================================================================

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$DIR"

if command -v python3 >/dev/null 2>&1; then
    exec python3 "$DIR/Start.py" "$@"
elif command -v python >/dev/null 2>&1; then
    exec python "$DIR/Start.py" "$@"
else
    echo "❌ HATA: Sistemde Python bulunamadı! Lütfen Python 3 yükleyiniz."
    exit 1
fi
