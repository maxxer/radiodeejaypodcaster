<?php
/**
 * Radio Deejay RELOADED podcaster
 * @author Lorenzo Milesi <lorenzo@mile.si>
 * @copyright 2015 Lorenzo Milesi 
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
        if (is_null($url))
            $url = $this->baseUrl;
        $reloaded_home = file_get_html($url);
        
        $db = $this->getDbConnection();
        $insProgr = $db->prepare("REPLACE INTO `programma` (slug, nome, url_immagine) "
                . "VALUES (:slug, :nome, :urlImg)");
        foreach ($reloaded_home->find('ul[class="block-grid"]',0)->find("li") as $programma) {
            $imgEl = $programma->find("img",0);
            $img = $imgEl->src;
            $nome = $imgEl->alt;
            $slug = self::createSlug($nome);
            $cntProgr = current($db->query("SELECT COUNT(*) FROM `programma` WHERE `slug` = '$slug' ")->fetch());
            if ($cntProgr > 0) { // Programma già presente
                echo "Programma '$slug' già presente\n";
                continue;
            }
            $insProgr->execute([
                ':slug' => $slug, 
                ':nome' => $nome, 
                ':urlImg' => $img, 
            ]);
        }
        
        // Cerco se c'è una pagina successiva
        $next = $reloaded_home->find('a[class="'.$this->nextPageClass.'"]', 0);
        if (!is_null($next)) {
            return $this->scanList($next->href);
        } 
        return;
    }
    
    /**
     * Aggiorna gli episodi di uno o più programmi 
     * @param string $programma SLUG del programma, % per tutti.
     */
    public function aggiornaPodcast ($programma = '%') {
        $db = $this->getDbConnection();
        $qrPodcast = $db->query("SELECT * FROM `programma` WHERE `slug` LIKE '$programma'")->fetchAll();
        
        foreach ($qrPodcast as $riga) {
            echo "Elaborazione programma '{$riga['slug']}' \n";
            $urlArchivio = $this->baseUrlArchivio.$riga['slug'];
            echo "Apertura pagina archivio '$urlArchivio' \n";
            $pagArchivio = file_get_html($urlArchivio);
            
            $elencoEpisodi = $pagArchivio->find('ul[class="lista"]',0)->find("li a[1]");
            if (empty($elencoEpisodi)) {
                echo "ATTENZIONE: nessun link trovato!\n";
                return;
            }
            foreach ($elencoEpisodi as $link) {
                echo "Rilevato episodio '{$link->title}' con url '{$link->href}' \n";
                $titolo = $link->title;
                $qrFind = current($db->query("SELECT COUNT(*) FROM `episodio` WHERE "
                    . "`id_programma` = '{$riga['id']}' AND `href` = '{$link->href}' ")->fetch());
                if ($qrFind > 0) {
                    // Se ho già questo titolo tutto il mio programma è aggiornato
                    echo "Episodio già presente, programma aggiornato\n";
                    continue 2;
                }
                $this->leggiProgramma($titolo, $riga['id'], $link->href);
            }
        }
    }
    
    /**
     * Scansione pagina del programma alla ricerca degli episodi, e popolazione della tabella `episodi`
     * @param string $titolo Titolo dell'episodio
     * @param integer $id_programma id del programma nella tabella 
     * @param string $url URL dell'episodio
     */
    private function leggiProgramma ($titolo, $id_programma, $url) {
        echo "Lettura url '$url' per programma $id_programma \n";
        $db = $this->getDbConnection();
        $qAddPodcast = $db->prepare("INSERT INTO `episodio` "
                . "(`id_programma`, `titolo`, `url_file`, `href`, `data_inserimento`) "
                . "VALUES ('$id_programma', '$titolo', :file, '$url', datetime())");
        $doc = file_get_html($url);
        $iframe = $doc->find("iframe",0)->src;
        $iframe_query = parse_url($iframe, PHP_URL_QUERY);
        // rimuovere la prima parte 
        foreach (explode("&", $iframe_query) as $p) {
            $kv = explode("=", $p);
            $val [$kv[0]] = $kv[1];
        }
        // A questo punto ho le chiavi 'file' e 'image'
        $qAddPodcast->execute([':file' => $val['file']]);        
    }
    
    /**
     * Genera l'XML del podcast e lo emette su stdout
     * @param string $prog SLUG programma
     */
    public function generaXmlProgramma($prog) {
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
        $q_episodi = "SELECT * FROM `episodio` WHERE `id_programma` = '{$programma['id']}' LIMIT 10 ";

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

            $item->appendChild($xml->createElement('pubDate', date('D, d M Y H:i:s O', strtotime($episodio['data_inserimento']))));

        //    $getID3 = new getID3();
        //    $fileinfo = $getID3->analyze($episode['audio_file']);
        //    $item->appendChild($xml->createElement('itunes:duration', $fileinfo['playtime_string']));
        }

        $xml->formatOutput = true;
        print $xml->saveXML();
    }
    
    /**
     * Torna l'elenco dei programmi
     * @return array
     */
    public function generaElencoProgrammi () {
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
        return $programmi;
    }
    
    /**
     * Genera lo slug partendo dal nome del programma
     * @param string $name
     * @return string
     */
    public static function createSlug ($name) {
        return strtolower(preg_replace("/\s/", "-", $name));
    }
    
    /**
     * Genera l'oggetto della connessione SQLite
     * @return PDO
     */
    public function getDbConnection () {
        if (empty($this->conn)) {
            try {
                $this->conn = new PDO("sqlite:radiodeejayreloaded.sqldb");
            } catch (PDOException $e) {
                die ("SQLite db missing: ".$e->getMessage());
            }
        }
        return $this->conn;
    }
}
