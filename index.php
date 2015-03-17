<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Radio Deejay podcaster per i programmi in reloaded">
    <meta name="author" content="Lorenzo 'maxxer' Milesi">
    <meta name="og:image" content="http://deejayreloadedpodcast.maxxer.it/img/radiodeejay_logo.png">

    <title>Radio Deejay /reloaded/ podcaster</title>

    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link href="css/grayscale.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-37294214-4', 'auto');
      ga('send', 'pageview');

    </script>    

</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">

    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand page-scroll" href="#page-top">
                    <i class="fa fa-play-circle"></i> Radio Deejay  <span class="light">podcaster</span>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#perche">Perch&eacute;</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#podcast">Podcast</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#crediti">Crediti</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Intro Header -->
    <header class="intro">
        <div class="intro-body">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <h1 class="brand-heading">Reloaded Podcaster</h1>
                        <p class="intro-text"><strong>unofficial</strong> podcast per tutti i programmi di Radio Deejay in <em>reloaded</em>.</p>
                        <a href="http://www.deejay.it" target="_blank"><img src="img/radiodeejay_logo.png" alt="Radio Deejay" /></a> <br />
                        <a href="#perche" class="btn btn-circle page-scroll">
                            <i class="fa fa-angle-double-down animated"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section id="perche" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Perch&eacute;</h2>
                <p>Banalmente per avere in automatico tutte le puntate dei miei programmi preferiti sul telefono, e poterle ascoltare quando sono in giro senza doverle scaricare manualmente.</p>
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="podcast" class="content-section text-center">
        <div class="download-section">
            <div class="container">
                <div class="col-lg-6 col-lg-offset-3">
                    <h2>Elenco podcast</h2>
                    <ul class="list-group">
                        <?php 
                        require_once 'rdjreloaded.php';
                        $lib = new RDJReloaded();
                        foreach ($lib->generaElencoProgrammi() as $p) : ?>
                        <li class="list-group-item">
                          <span class="badge"><a href="/podcast/<?=$p['slug']?>.xml" title="Feed podcast"><?=$p['conteggio']?> <i class="fa fa-rss animated"></i></a></span>
                          <a href="<?=$p['url_archivio']?>" title="Homepage reloaded" target="_blank"><i class="fa fa-home animated"></i></a>
                          <?=$p['nome']?>
                        </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="crediti" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Crediti</h2>
                <p>Tutti i programmi, i file audio, le immagini dei programmi e i relativi loghi sono di propriet&agrave; e &copy; di <a href="http://www.deejay.it" target="_blank">Radio Deejay</a>.</p>
                <p>Gli script PHP per il reperimento delle pagine sono stati scritti da <a href="http://it.linkedin.com/in/maxxer" target="_blank" title="il mio profilo linkedin">me medesimo</a>.</p>
                <p>Per il parsing dell'HTML delle pagine del sito ho usato la libreria <a href="http://simplehtmldom.sourceforge.net/" target="_blank">simple html dom</a>.<br />
                Il bellissimo layout &egrave; invece di <a href="http://startbootstrap.com/template-overviews/grayscale/" target="_blank">David Miller</a>.<br />
                L'immagine di sfondo in alto arriva da <a href='http://www.djjeffh.com/2013/04/house-trax-72-philly-nites-radio.html' target="_blank">DJ Jeff Howell</a>.
                </p>
                <p>Per commenti o richieste scrivete pure a <a href="mailto:lorenzo@mile.si">lorenzo@mile.si</a>. <br />
                Se avete miglioramenti da proporre:</p>
                <ul class="list-inline banner-social-buttons">
                    <li>
                        <a href="https://github.com/maxxer/radiodeejaypodcaster" class="btn btn-default btn-lg"><i class="fa fa-github fa-fw"></i> <span class="network-name">Github</span></a>
                    </li>
                </ul>
                <p>Se vuoi sostenere questo sito (magari per pagare i costi di hosting) puppati un po' di annunci ed ogni tanto clicca...</p>
                <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- podcaster -->
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-0712109511578838"
                     data-ad-slot="2290262523"
                     data-ad-format="auto"></ins>
                <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>Copyright &copy; maxxer 2015</p>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <!-- Plugin JavaScript -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="js/grayscale.js"></script>

</body>

</html>
