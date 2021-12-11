<?php
/* Yereli Türkçe yapalım */
setlocale(LC_ALL, 'tr_TR.UTF-8');
require 'vendor/autoload.php';

use App\SlideController;

$slides= (new SlideController())->getSlides();
?>
<!doctype html>
<html lang="tr">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="<?= count($slides)*60?>">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/breaking-news-ticker.css">

    <title>Tirebolu MYO Duyurular</title>
</head>
<body>
<main class="mt-2">
    <header>
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col text-center">
                    <img src="images/logo_230x230.png" alt="logo" class="logo img-fluid">
                </div>
                <div class="col-5 text-center">
                    <h2 class="baslik">Tirebolu Mehmet Bayrak Meslek Yüksekokulu</h2>
                </div>
                <div class="col" style="height: 100px">
                    <!-- Hava Durumu -->
                    <a class="weatherwidget-io" href="https://forecast7.com/tr/41d0138d81/tirebolu/" data-icons="Climacons Animated" data-mode="Current" data-days="3" data-theme="pure" >Tirebolu Mehmet Bayrak MYO</a>
                    <script>
                        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
                    </script>
                    <!--/ Hava Durumu -->
                </div>
            </div>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!--div class="card-header text-center">Duyurular Panosu</div-->
                    <div class="card-body p-0">
                        <div id="afisler" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                               <?php
                                $active=true;
                                foreach ($slides as $slide){
                                    $c=$active ? "active":"";
                                    $w=$slide->fullWidth ? "w-100": "";
                                    echo '
                                    <div class="carousel-item '.$c.'">
                                    <img src="'.$slide->image.'" class="d-block '.$w.'" alt="" >
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5>'.$slide->title.'</h5>
                                        <p>'.$slide->content.'</p>
                                    </div>
                                </div>
                                    ';
                                    $active=false;
                                }
                                ?>
                            </div>
                            <a class="carousel-control-prev" href="#afisler" role="button"
                               data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#afisler" role="button"
                               data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Last Announcements HTML STARTS HERE *-->
    <!-- *********************** -->
    <div class="breaking-news-ticker" id="lastAnnouncements">
        <div class="bn-label">Son Duyurular</div>
        <div class="bn-news">
            <ul>
                <li><span class="bn-loader-text">Loading post from JSON file...</span></li>
            </ul>
        </div>

    </div>
    <!-- *********************** -->
    <!-- Last Announcements HTML END HERE *** -->
</main>



<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="js/jquery.min.js"></script>
<!--<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>-->
<script src="js/jquery-migrate-git.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/breaking-news-ticker.min.js"></script>
<script type="text/javascript">
    /**
     * @link http://tevratgundogdu.com/works/ideabox-breaking-news-ticker/
     */
    $('#lastAnnouncements').breakingNews({
        /*position: 'fixed-bottom',*/
        borderWidth: 3,
        height: 60,
        themeColor: '#000B98',
        scrollSpeed: 0.5,
        effect: 'slide-down',
        delayTimer:10000,
        source: {
            type: 'json',
            url: 'admin/ajax.php',
            limit: 10,
            showingField: 'duyuru',
            linkEnabled: false,
            target: '_blank',
            seperator: '<span class="bn-seperator" style="background-image:url(images/logo_230x230.png);"></span>',
            withPrefix: true,
            errorMsg: 'Duyurular yüklenemedi. Lütfen ayarları kontrol ediniz.'
        }
    });

    $('#afisler').carousel({
        interval: 30000
    })
</script>
</body>
</html>
