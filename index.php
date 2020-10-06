<?php
/* Yereli Türkçe yapalım */
setlocale(LC_ALL, 'tr_TR.UTF-8');
?>
<!doctype html>
<html lang="tr">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="300">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/breaking-news-ticker.css">

    <title>Tirebolu MYO Duyurular</title>
</head>
<body>

<header>
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col text-center">
                <img src="images/logo_230x230.png" alt="logo" class="logo img-fluid">
            </div>
            <div class="col-6 text-center">
                <h2 class="baslik">Tirebolu Mehmet Bayrak Meslek Yüksekokulu</h2>
            </div>
            <div class="col" style="height: 100px">
                <!-- Hava Durumu -->
                <a class="weatherwidget-io" href="https://forecast7.com/tr/41d0138d81/tirebolu/" data-label_1="Tirebolu"
                   data-label_2="Mehmet Bayrak MYO" data-icons="Climacons Animated" data-mode="Current" data-days="3"
                   data-theme="pure">Tirebolu Mehmet Bayrak MYO</a>
                <script>
                    !function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (!d.getElementById(id)) {
                            js = d.createElement(s);
                            js.id = id;
                            js.src = 'https://weatherwidget.io/js/widget.min.js';
                            fjs.parentNode.insertBefore(js, fjs);
                        }
                    }(document, 'script', 'weatherwidget-io-js');
                </script>
                <!--/ Hava Durumu -->
            </div>
        </div>
    </div>
</header>
<main class="mt-2">
    <div class="container-fluid">
        <div class="row">
            <div class="col-9">
                <div class="card">
                    <!--div class="card-header text-center">Duyurular Panosu</div-->
                    <div class="card-body p-0">
                        <div id="afisler" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
                                <li data-target="#carouselExampleCaptions" data-slide-to="1" class=""></li>
                                <li data-target="#carouselExampleCaptions" data-slide-to="2" class=""></li>
                                <li data-target="#carouselExampleCaptions" data-slide-to="3" class=""></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="images/açılış ile ilgili pup-up.jpg" class="d-block w-100" alt="" style="" >
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5></h5>
                                        <p></p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img src="images/rektor_hosgeldin_mesajı.jpeg" class="d-block" alt="" style="height: 78vh!important;margin:0 auto;" >
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5></h5>
                                        <p></p>
                                    </div>
                                </div>
                                <div class="carousel-item ">
                                    <img src="images/uzembilgi.jpg" class="d-block" alt="" style="height: 78vh!important;margin:0 auto;" >
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5></h5>
                                        <p></p>
                                    </div>
                                </div>
                                <div class="carousel-item ">
                                    <img src="images/stajBildiri.jpg" class="d-block" alt="" style="height: 78vh!important;margin:0 auto;" >
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5></h5>
                                        <p></p>
                                    </div>
                                </div>
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
            <!-- Etkinlikler -->
            <div class="col p-0">
                <div class="card border-0">
                    <div class="card-header">
                        Haberler
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"></h5>
                                    <small></small>
                                </div>
                                <p class="mb-1"></p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
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


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="js/jquery.min.js"></script>
<!--<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>-->
<script src="js/jquery-migrate-git.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/breaking-news-ticker.min.js"></script>
<script type="text/javascript">
    $('#lastAnnouncements').breakingNews({
        position: 'fixed-bottom',
        borderWidth: 3,
        height: 50,
        themeColor: '#000B98',
        scrollSpeed: 0.5,
        source: {
            type: 'json',
            url: 'sonDuyurular.json',
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
