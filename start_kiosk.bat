@echo off
REM ==============================================================================
REM 🎓 UniPano - Kiosk Başlatıcı (Windows)
REM Harici TV / İkincil ekranda tek tıkla tam ekran Kiosk başlatır.
REM ==============================================================================
chcp 65001 >nul
setlocal
cd /d "%~dp0"

where python >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    start "" python "%~dp0Start.py" %*
    exit /b 0
)

where py >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    start "" py "%~dp0Start.py" %*
    exit /b 0
)

echo [HATA] Python bulunamadi! Lutfen Python 3 yukleyip PATH ortam degiskenine ekleyiniz.
pause
exit /b 1
