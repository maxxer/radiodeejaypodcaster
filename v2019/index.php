<!-- Stage- Bootstrap one page Event ticket booking theme 
Created by pixpalette.com - online design magazine -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Radio Deejay /reloaded/ podcaster - v2019</title>
    <meta name="description" content="Radio Deejay podcast per i programmi completi in reloaded - v2019.06">
    <meta name="author" content="Lorenzo 'maxxer' Milesi">
    <meta name="og:image" content="https://deejayreloadedpodcast.maxxer.it/img/radiodeejay_logo.png">

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    
    <!-- fonts -->
    <link href='//fonts.googleapis.com/css?family=Nixie+One' rel='stylesheet' type='text/css'>
    <link href="//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,900" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Matomo -->
    <script type="text/javascript">
      var _paq = _paq || [];
      /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
      _paq.push(['trackPageView']);
      _paq.push(['enableLinkTracking']);
      (function() {
        var u="//s.maxxer.it/t/";
        _paq.push(['setTrackerUrl', u+'piwik.php']);
        _paq.push(['setSiteId', '3']);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <!-- End Matomo Code -->
  </head>
  <body>
  	
    <div class="loader">
       <div>
        <img src="images/icons/preloader.gif" />
       </div>
    </div>
    
    <div class="container-fluid">
		<div class="row">
        	<div class="col-sm-5 left-wrapper">
            	<div class="event-banner-wrapper">
                	<div class="logo">
                        <h1>Stage</h1>
                    </div>
                
                	<h2>
                    Radio Deejay <br>reloaded podcaster
                    <span>v2019.06</span>
                    </h2>
                    <p>by <a href="https://lorenzo.mile.si" target="_blank">maxxer</a></p>
                </div>
            </div>
            <div class="col-sm-7 right-wrapper">
            	<div class="event-ticket-wrapper">
                    
                    <div class="event-tab">
                
                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#buyTicket" aria-controls="buyTicket" role="tab" data-toggle="tab">Podcast</a></li>
                    <li role="presentation"><a href="#credits" aria-controls="credits" role="tab" data-toggle="tab">Crediti</a></li>
                    <li role="presentation"><a href="https://lorenzo.mile.si/category/deejay-reloaded-podcaster/" target="_blank">News</a></li>
                  </ul>
                
                  <!-- Tab panes -->
                  <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active" id="buyTicket">
                        <h2>Elenco podcast reloaded Radio Deejay</h2>
                    	<div class="row">

                        <?php
                        require_once 'rdjreloaded.php';
                        $lib = new RDJReloaded();
                        foreach ($lib->generaElencoProgrammi() as $p) : ?>

                            <div class="col-md-6">
                            	<div class="ticketBox" data-ticket-price="25000">
                                	<div class="inactiveStatus"></div>
                                    
                                    <div class="row">
 			                       	<div class="col-xs-6">
            							<div class="ticket-name">
                                        <a href="<?=$p['url_archivio']?>" title="Homepage programma <?=$p['nome']?>" target="_blank">
                                            <?=$p['nome']?> 
                                        </a>
                                        <span>
                                        Ultima puntata: 
                                        <?=date("d/m/Y", $p['ultima_puntata'])?></span></div>
            						</div>
                                    
                                    <div class="col-xs-6">
            							<div class="ticket-price-count-box">
                                            <div class="ticket-control">
                                                <div class="input-group">
                                                  <span class="input-group-btn">
                                                    <a class="btn btn-default btn-number" href="podcast/<?=$p['slug']?>.xml" title="Feed podcast <?=$p['nome']?>" target="_blank">
                                                        <span class="glyphicon glyphicon-headphones"></span>
                                                    </a>
                                                  </span>
                                                </div>
                                            </div>
                                            <p class="price"><!-- --></p>
                                        </div>
            						</div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>


                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="credits">
                        <h4>Tutti i programmi, i file audio, le immagini dei programmi e i relativi loghi sono di proprietà e © di Radio Deejay.</h4>
                        <h4>Ho fatto questo sito perché sono da moltissimi anni appassionato di Radio Deejay, e di informatica.</h4>
                        <h4>Non partecipo ai programmi, raramente agli eventi e scrivo di rado. È il mio modo per partecipare alla <em>vita</em> della radio.</em> </h4>
                        <h4>Foto di <a style="background-color:black;color:white;text-decoration:none;padding:4px 6px;font-family:-apple-system, BlinkMacSystemFont, &quot;San Francisco&quot;, &quot;Helvetica Neue&quot;, Helvetica, Ubuntu, Roboto, Noto, &quot;Segoe UI&quot;, Arial, sans-serif;font-size:12px;font-weight:bold;line-height:1.2;display:inline-block;border-radius:3px" href="https://unsplash.com/@tompavlakos?utm_medium=referral&amp;utm_campaign=photographer-credit&amp;utm_content=creditBadge" target="_blank" rel="noopener noreferrer" title="Download free do whatever you want high-resolution photos from Tom Pavlakos"><span style="display:inline-block;padding:2px 3px"><svg xmlns="http://www.w3.org/2000/svg" style="height:12px;width:auto;position:relative;vertical-align:middle;top:-2px;fill:white" viewBox="0 0 32 32"><title>unsplash-logo</title><path d="M10 9V0h12v9H10zm12 5h10v18H0V14h10v9h12v-9z"></path></svg></span><span style="display:inline-block;padding:2px 3px">Tom Pavlakos</span></a> da <a href="https://unsplash.com" target="_blank">Unsplash</a>.</h4>
                        <h4>Layout grafico <a href="https://onepagelove.com/stage" target="_blank">Stage</a> di <a href="http://www.pixpalette.com/" target="_blank">pixlpalette</a>.</h4>
                        <h4>Per commenti o richieste scrivete pure <a href="mailto:lorenzo@mile.si">qui</a>.</h4>
                        <h4>Se avete miglioramenti da proporre:</h4>
                        <a href="https://github.com/maxxer/radiodeejaypodcaster" class="btn btn-default btn-lg"><i class="fa fa-github fa-fw"></i> <span class="network-name">Github</span></a>
                    </div>
                  </div>
                
                </div>
                
                	<div class="cart">
                <div class="row">
                    <div class="col-xs-6">
                        <p> 
                            <!-- -->
                        </p>
                    </div>
                    <div class="col-xs-6">
                    	<div class="text-right">
                        	<a class="btn" data-toggle="modal" data-target="#ticket-details">Dona</a>
                        </div>
                    </div>
                </div>
                </div>
                       
                </div>
            </div>
        </div>
    </div>

<!-- Modal -->
<div class="modal right fade" id="ticket-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <!--<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Share your contact Details</h4>
      </div>-->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        	<img src="images/icons/cancel.png">
        </button>
        <h4 class="modal-title">Aiuta il progetto</h4>
      </div>
      <div class="modal-body">
        
        <div class="cart-information">
                <div class="ticket-type"></div>
                Gestisco questo progetto in totale autonomia.<br>
                Mi pago il server, il dominio su cui gira ed ovviamente il tempo per 
                gestirlo ed aggiornarlo.<br>
                Se lo usi e ti fa piacere puoi pensare di fare una piccola donazione per supportarne
                lo sviluppo.

          		<ul>
	                <li>Costo server: <span class="ticket-count">30€/anno</span></li>
                    <li>Costo dominio: <span class="ticket-amount">8.42€/anno</span></li>
                    <li>Tempo di sviluppo: <span class="ticket-amount">XX</span></li>
    			</ul>
            </div>
            
            <div class="contactForm">	
                  Se ti fa piacere,
        		  <a href="https://paypal.me/maxx3r" target="_blank" class="btn">fai una donazione</a>
                </form>
            </div>
        
        
        
      </div>
    </div>
  </div>
</div>
    
    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/allscript.js"></script>
  </body>
</html>
