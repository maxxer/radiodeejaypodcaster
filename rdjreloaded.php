<?php
/**
 * Radio Deejay RELOADED podcaster
 * @author Lorenzo Milesi <lorenzo@mile.si>
 * @copyright 2016 Lorenzo Milesi
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
    private $baseUrl = "http://www.deejay.it/reloaded/radio/";
    private $baseUrlArchivio = "http://www.deejay.it/audio/?reloaded=";
    private $nextPageClass = "nextpostslink";

    const CACHE_PREFIX = "rdjreloaded";

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
        self::log("INIZIO SCANSIONE ELENCO PROGRAMMI\n");
        if (is_null($url))
            $url = $this->baseUrl;
        $reloaded_home = file_get_html($url);

        $db = $this->getDbConnection();
        $insProgr = $db->prepare("REPLACE INTO `programma` (slug, nome, url_immagine) "
                . "VALUES (:slug, :nome, :urlImg)");
        // Per ogni link della home apro la pagina e prelevo i dati del programma
        foreach ($reloaded_home->find('ul[class="block-grid"]',0)->find("li") as $programma) {
            $url_puntata = $programma->find("a",0)->href;
            $dom_programma = file_get_html($url_puntata);
            // Immagine programma dall'elenco, perché quello della pagina della puntata si riferisce all'episodio stesso
            $img = $programma->find("img",0)->src;
            // Titolo da hgroup > h2.xlarge-title > a
            $nome = $dom_programma->find('hgroup h2[class="xlarge-title"] a',0)->title;
            // Slug dal link ad "Archivio +"
            $slug_url = $dom_programma->find('hgroup span[class="small-title"] a',0)->href;
            list(, $slug) = explode("=", $slug_url);

            // Verifico se ho già il programma
            $cntProgr = current($db->query("SELECT COUNT(*) FROM `programma` WHERE `slug` = '$slug' ")->fetch());
            if ($cntProgr > 0) { // Programma già presente
                self::log("Programma '$slug' già presente\n");
                continue;
            }
            // Altrimenti inserisco
            $insProgr->execute([
                ':slug' => $slug,
                ':nome' => $nome,
                ':urlImg' => $img,
            ]);
            self::log("Programma '$slug' inserito\n");
        }

        // Cerco se c'è una pagina successiva
        $next = $reloaded_home->find('a[class="'.$this->nextPageClass.'"]', 0);
        if (!is_null($next)) {
            return $this->scanList($next->href);
        }
        self::log("FINE SCANSIONE ELENCO PROGRAMMI\n");
        return;
    }

    /**
     * Aggiorna gli episodi di uno o più programmi
     * @param string $programma SLUG del programma, % per tutti.
     */
    public function aggiornaPodcast ($programma = '%') {
        self::log("INIZIO AGGIORNAMENTO PROGRAMMA\n");
        $db = $this->getDbConnection();
        $qrPodcast = $db->query("SELECT * FROM `programma` WHERE `slug` LIKE '$programma'")->fetchAll();

        foreach ($qrPodcast as $riga) {
            self::log("Elaborazione programma '{$riga['slug']}' \n");
            $urlArchivio = $this->baseUrlArchivio.$riga['slug'];
            self::log("Apertura pagina archivio '$urlArchivio' \n");
            $pagArchivio = file_get_html($urlArchivio);

            $elencoEpisodi = $pagArchivio->find('ul[class="lista"]',0);
            if (empty($elencoEpisodi)) {
                self::log("ATTENZIONE: nessun link trovato!\n");
                continue;
            }
            foreach ($elencoEpisodi->find("li a[1]") as $link) {
                self::log("Rilevato episodio '{$link->title}' con url '{$link->href}' \n");
                $titolo = $link->title;
                $qrFind = current($db->query("SELECT COUNT(*) FROM `episodio` WHERE "
                    . "`id_programma` = '{$riga['id']}' AND `href` = '{$link->href}' ")->fetch());
                if ($qrFind > 0) {
                    // Se ho già questo titolo tutto il mio programma è aggiornato
                    self::log("Episodio già presente, programma aggiornato\n");
                    continue 2;
                }
                $this->leggiProgramma($titolo, $riga['id'], $link->href);
            }
        }
        self::log("FINE AGGIORNAMENTO PROGRAMMA\n");
    }

    /**
     * Scansione pagina del programma alla ricerca degli episodi, e popolazione della tabella `episodi`
     * @param string $titolo Titolo dell'episodio
     * @param integer $id_programma id del programma nel db
     * @param string $url URL dell'episodio
     */
    private function leggiProgramma ($titolo, $id_programma, $url) {
        self::log("Lettura url '$url' per programma $id_programma \n");
        $db = $this->getDbConnection();
        $qAddPodcast = $db->prepare("INSERT INTO `episodio` "
                . "(`id_programma`, `titolo`, `url_file`, `href`, `data_inserimento`) "
                . "VALUES ('$id_programma', '$titolo', :file, '$url', :pubdate)");
        $doc = file_get_html($url);
        $iframe = $doc->find("iframe",0)->src;
        $iframe_query = parse_url($iframe, PHP_URL_QUERY);
        // rimuovere la prima parte
        foreach (explode("&", $iframe_query) as $p) {
            $kv = explode("=", $p);
            $val [$kv[0]] = $kv[1];
        }
        // Data inserimento da og:published
        $dt = DateTime::createFromFormat("Y-m-d\TH:i:s", $doc->find('meta[property="og:published_time"]',0)->content);
        if ($dt === FALSE) // Se la data non è valida
            $dt = new DateTime();

        // A questo punto ho le chiavi 'file' e 'image'
        $qAddPodcast->execute([':file' => $val['file'], ':pubdate' => $dt->getTimestamp()]);
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
        $chan->appendChild($xml->createElement('link', $this->baseUrlArchivio.$programma['slug']));
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
        $this->cache_add($cache_key, $outXml);
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
                . "COUNT(*) AS conteggio, "
                . "'{$this->baseUrlArchivio}' || `slug` AS url_archivio "
                . "FROM `programma` "
                . "JOIN `episodio` ON `id_programma` = `programma`.`id` "
                . "GROUP BY `programma`.`slug` "
                . "ORDER BY `programma`.`slug` ";
        $programmi = $db->query($q_programmi)->fetchAll();
        if (empty($programmi))
            return;

        $this->cache_add($cache_key, $programmi);
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
