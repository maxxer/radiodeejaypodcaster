<?php
/**
 * Radio Deejay RELOADED podcaster
 * @author Lorenzo Milesi <lorenzo@mile.si>
 * @copyright 2019 Lorenzo Milesi
 * @license GNU GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once("simple_html_dom.php");
date_default_timezone_set("Europe/Rome");

class RDJReloaded {
    private $baseUrl = "https://www.deejay.it/programmi/";

    const CACHE_PREFIX = "201906-rdjreloaded";

    /* @var PDO */
    private $conn;

    /**
     * Scansiona la homepage di Deejay Reloaded e crea la lista dei programmi disponibili, raccogliendo
     * titolo, nome, URL immagine e lo slug.
     * Inserisce i risultati nella tabella `programma`.
     * Si chiama ricorsivamente per la paginazione.
     * @param string $url Path della prima pagina
     * @return null
     */
    public function scanList ($url = NULL)
    {
        if (is_null($url)) {
            self::log("INIZIO SCANSIONE ELENCO PROGRAMMI\n");
            $url = $this->baseUrl;
        }
        $reloaded_home = file_get_html($url);

        $db = $this->getDbConnection();
        $insProgr = $db->prepare("INSERT INTO `programma` (`slug`, `nome`, `url_immagine`, `url_thumb`) "
            . "VALUES (:slug, :nome, :urlImg, :urlThumb)");
        $updProgr = $db->prepare("UPDATE `programma` SET `nome` = :nome, `url_immagine` = :urlImg, `url_thumb` = :urlThumb "
            . "WHERE `slug` = :slug");
        // Per ogni li della pagina apro il link e prelevo i dati del programma
        foreach ($reloaded_home->find('section.program-list',0)->find("article") as $programma) {
            // Prendo il link dell'immagine
            $link_programma = $programma->find("figure a", 0);
            if (is_null($link_programma)) {
                // Capita (2018.01.29 - Il Rosario della sera) che pubblichino un "placeholder" al programma senza link
                continue;
            }
            // Apro la pagina di dettaglio del programma 
            $dom_programma = file_get_html($link_programma->href);
            // Immagine programma
            $img = $dom_programma->find('meta[property="og:image"]', 0)->content;
            $thumb = $programma->find('figure img', 0)->src;
            // Titolo da hgroup > h2.title 
            $nome = $dom_programma->find('article hgroup h2[class="title"]', 0)->plaintext;
            // Slug dal link corrente
            $url_parse = parse_url($link_programma->href, PHP_URL_PATH);
            $slug = str_replace(["programmi", "/"], "", $url_parse);

            unset($dom_programma);
            $queryParams = [
                ':slug' => $slug,
                ':nome' => $nome,
                ':urlImg' => $img,
                ':urlThumb' => $thumb,
            ];
            // Verifico se ho già il programma
            $cntProgr = current($db->query("SELECT COUNT(*) FROM `programma` WHERE `slug` = '$slug' ")->fetch());
            if ($cntProgr > 0) { // Programma già presente
                // Aggiorno in caso sia cambiata l'immagine o il titolo (?)
                $updProgr->execute($queryParams);
                self::log("Programma '$slug' aggiornato\n");
            } else {
                // Altrimenti inserisco
                $insProgr->execute($queryParams);
                self::log("Programma '$slug' inserito\n");
            }
        }

        // Cerco se c'è una pagina successiva
        /** 2019.06.19 per ora la paginazione non c'è
        $next = $reloaded_home->find('a[class="'.$this->nextPageClass.'"]', 0);
        unset($reloaded_home);
        if (!is_null($next)) {
            return $this->scanList($next->href);
        }
        */
        self::log("FINE SCANSIONE ELENCO PROGRAMMI\n");
        return;
    }

    /**
     * Aggiorna gli episodi di uno o più programmi
     * @param string $programma SLUG del programma, % per tutti.
     * @param boolean $noStop Se TRUE non si ferma quando incontra il primo episodio già presente. Predefinito FALSE
     */
    public function aggiornaPodcast ($programma = '%', $noStop = FALSE) {
        self::log("INIZIO AGGIORNAMENTO PROGRAMMA\n");
        $db = $this->getDbConnection();
        $qrPodcast = $db->query("SELECT * FROM `programma` WHERE `slug` LIKE '$programma'")->fetchAll();

        foreach ($qrPodcast as $riga) {
            self::log("Elaborazione programma '{$riga['slug']}' \n");
            $urlPuntate = $this->baseUrl.$riga['slug']."/puntate/";
            $this->leggiPaginaProgramma($urlPuntate, $riga['id'], $noStop);
        }
        self::log("FINE AGGIORNAMENTO PROGRAMMA\n");
    }

    /**
     * Legge una pagina dell'archvio programma
     * @param string $url Pagina da leggere
     * @param integer $id_programma Id del programma
     * @param boolean $tutto Se TRUE non si ferma quando trova un episodio già presente ma va avanti
     *  fino a che ci sono pagine. Predefinito FALSE
     */
    private function leggiPaginaProgramma($url, $id_programma, $tutto = false)
    {
        self::log("Apertura pagina puntate '$url' \n");
        $pagArchivio = file_get_html($url);

        $elencoEpisodi = $pagArchivio->find('section.puntate-list ul',0);
        if (empty($elencoEpisodi)) {
            self::log("ATTENZIONE: nessun link trovato!\n");
            return;
        }
        $db = $this->getDbConnection();
        foreach ($elencoEpisodi->find("li h1 a") as $link) {
            self::log("Rilevato episodio '{$link->plaintext}' con url '{$link->href}' \n");
            $qrFind = current($db->query("SELECT COUNT(*) FROM `episodio` WHERE "
                . "`id_programma` = '$id_programma' AND `href` = '{$link->href}' ")->fetch());
            if ($qrFind > 0 && $tutto === false) {
                // Se ho già questo link il programma è aggiornato
                self::log("Episodio già presente, programma aggiornato\n");
                return;
            }
            $this->leggiProgramma($id_programma, $link->href);
        }
        // Vediamo se c'è una seconda pagina
        $nuovaPagina = $pagArchivio->find('a[class="next"]', 0);
        unset($pagArchivio);
        if (!empty($nuovaPagina)) {
            $this->leggiPaginaProgramma($nuovaPagina->href, $id_programma, $tutto);
        }
    }

    /**
     * Scansiona la pagina del programma con il player popolando la tabella `episodi`
     * @param integer $id_programma id del programma nel db
     * @param string $url URL dell'episodio
     */
    private function leggiProgramma ($id_programma, $url) {
        self::log("Lettura url '$url' per programma $id_programma \n");
        $db = $this->getDbConnection();
        $qAddPodcast = $db->prepare("INSERT OR IGNORE INTO `episodio` "
                . "(`id_programma`, `titolo`, `url_file`, `href`, `data_inserimento`) "
                . "VALUES ('$id_programma', :titolo, :file, '$url', :pubdate)");
        $doc = file_get_html($url);
        $iframe = $doc->find("iframe",0)->src;
        $iframe_query = parse_url($iframe, PHP_URL_QUERY);
        // Dei parametri del link iframe estraggo "file"
        parse_str($iframe_query, $iframe_params);
        $titolo = $doc->find("h1.title a", 0)->plaintext;
        // Data inserimento da og:published
        $dt = DateTime::createFromFormat("Y-m-d\TH:i:s", $doc->find('meta[property="article:published_time"]',0)->content);
        if ($dt === FALSE) // Se la data non è valida
            $dt = new DateTime();

        // A questo punto ho i dati per l'inserimento della puntata
        $qAddPodcast->execute([
            ':file' => $iframe_params['file'], 
            ':pubdate' => $dt->getTimestamp(), 
            ':titolo' => $titolo,
        ]);
        unset($doc);
    }

    /**
     * Genera l'XML del podcast e lo emette su stdout
     * @param string $prog SLUG programma
     */
    public function generaXmlProgramma($prog) {
        $cache_key = self::CACHE_PREFIX."-xml-".$prog;
        $cached = $this->cache_get($cache_key);
        if ($cached !== FALSE) {
            print $cached;
            return;
        }

        $db = $this->getDbConnection();
        $programma = $db->query("SELECT * FROM `programma` WHERE `slug` LIKE '$prog'")->fetch();
        if (empty($programma))
            return;

        // Contatore visite
        $updCnt = $db->exec("UPDATE `programma_visite` SET `visite` = `visite` + 1  "
                . "WHERE `id_programma` = '{$programma['id']}' ");
        if ($updCnt == 0) {
            $db->exec("INSERT INTO `programma_visite` (`id_programma`, `visite`) "
                . "VALUES ('{$programma['id']}', 1) ");
        }

        $xml = new DOMDocument();
        $root = $xml->appendChild($xml->createElement('rss'));
        $root->setAttribute('xmlns:itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $root->setAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
        $root->setAttribute('xmlns:feedburner', 'http://rssnamespace.org/feedburner/ext/1.0');
        $root->setAttribute('version', '2.0');

        $chan = $root->appendChild($xml->createElement('channel'));
        $chan->appendChild($xml->createElement('title', $programma['nome']));
        $chan->appendChild($xml->createElement('link', $this->baseUrl.$programma['slug']."/puntate/"));
        $chan->appendChild($xml->createElement('generator', 'deejayreloadedpodcast.maxxer.it'));
        $chan->appendChild($xml->createElement('language', 'it'));
        $chan_img = $chan->appendChild($xml->createElement('itunes:image'));
        $chan_img->setAttribute('href', $programma['url_immagine']);

        // Query elenco episodi
        $q_episodi = "SELECT * FROM `episodio` WHERE `id_programma` = '{$programma['id']}' ORDER BY `data_inserimento` DESC ";

        foreach ($db->query($q_episodi)->fetchAll() as $episodio) {
            $item = $chan->appendChild($xml->createElement('item'));
            $item->appendChild($xml->createElement('title', $episodio['titolo']));
            $item->appendChild($xml->createElement('link', $episodio['href']));
            $item->appendChild($xml->createElement('itunes:author', $programma['nome']));
            $item->appendChild($xml->createElement('itunes:summary', $episodio['titolo']));
            $item->appendChild($xml->createElement('guid', $episodio['url_file']));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $enclosure = $item->appendChild($xml->createElement('enclosure'));
            $enclosure->setAttribute('url', $episodio['url_file']);
//            $enclosure->setAttribute('length', filesize($episode['audio_file']));
//            $enclosure->setAttribute('type', finfo_file($finfo, $episode['audio_file']));

            $item->appendChild($xml->createElement('pubDate', date('D, d M Y H:i:s O', $episodio['data_inserimento'])));

        //    $getID3 = new getID3();
        //    $fileinfo = $getID3->analyze($episode['audio_file']);
        //    $item->appendChild($xml->createElement('itunes:duration', $fileinfo['playtime_string']));
        }

        $xml->formatOutput = true;
        $outXml = $xml->saveXML();
        // Cache dell'XML per un'oretta
        $this->cache_add($cache_key, $outXml, 1800);
        print $outXml;
    }

    /**
     * Torna l'elenco dei programmi, da usare nel frontend
     * @return array
     */
    public function generaElencoProgrammi () {
        $cache_key = self::CACHE_PREFIX."-elencoprogrammi";
        $cached = $this->cache_get($cache_key);
        if ($cached !== FALSE) {
            return $cached;
        }

        $db = $this->getDbConnection();
        $q_programmi = "SELECT *, "
                . "MAX(`data_inserimento`) AS ultima_puntata, "
                . "'{$this->baseUrl}' || `slug` || '/puntate/' AS url_archivio "
                . "FROM `programma` "
                . "JOIN `episodio` ON `id_programma` = `programma`.`id` "
                . "GROUP BY `programma`.`slug` "
                . "ORDER BY `programma`.`slug` ";
        $programmi = $db->query($q_programmi)->fetchAll();
        if (empty($programmi))
            return;

        $this->cache_add($cache_key, $programmi, 7200);
        return $programmi;
    }

    /**
     * Genera l'oggetto della connessione SQLite
     * @return PDO
     */
    public function getDbConnection () {
        if (empty($this->conn)) {
            try {
                $this->conn = new PDO("sqlite:".__DIR__."/radiodeejayreloaded.sqldb","","",array(PDO::ATTR_PERSISTENT => true));
            } catch (PDOException $e) {
                die ("Errore apertura DB: ".$e->getMessage());
            }
        }
        return $this->conn;
    }

    /**
     * Reperisce il contenuto di una chiave dalla cache
     * @param string $key Chiave
     * @return boolean|mixed FALSE se non c'è niente nella cache, altrimenti il contenuto della chiave
     */
    private function cache_get($key)
    {
        if (!extension_loaded('apc') || ini_get('apc.enabled') == 0)
            return FALSE;
        return apcu_fetch($key);
    }

    /**
     * Aggiunge una variabile alla cache
     * @param string $key Chiave
     * @param mixed $what Variabile da aggiungere
     * @param integer $ttl Tempo di durata della cache
     */
    private function cache_add($key, $what, $ttl = 3600)
    {
        if (!extension_loaded('apc') || ini_get('apc.enabled') == 0)
            return;
        apcu_add($key, $what, $ttl);
    }

    /**
     * Emette un messaggio su stdout
     */
    private static function log($message)
    {
        echo date("Y-m-d H:i:s")." ".$message;
    }
}
