import webview
import screeninfo

def main():
    # Bilgisayarınızdaki monitörleri alın
    
    monitors = screeninfo.get_monitors()
    selected_monitor = ""
    for monitor in monitors:
        if not monitor.is_primary:
            selected_monitor = monitor

    # Open website
    webview.create_window('TMYO Duyurular', 'https://tmyoduyuru.gencbilisim.net/',
                          x=selected_monitor.x,
                          y=selected_monitor.y,
                          fullscreen=True, # kapatma simgesi yok
                          maximized=False, # kapatma simgesi var 
                          on_top=True)
    webview.start()

if __name__ == "__main__":
    main()
