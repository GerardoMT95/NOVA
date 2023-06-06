<?php

namespace Drupal\ex81\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Query;
use Drupal\Core\Database\Query\ConditionInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Datetime\DrupalDateTime;


class CronController extends \Twig_Extension {
  public function getName() {
    return 'ex81.CronController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('countFeedBandiFilse', array($this, 'countFeedBandiFilse')),
      new \Twig_SimpleFunction('countFeedBandiRegione', array($this, 'countFeedBandiRegione')),
      new \Twig_SimpleFunction('countFeedBandiAgri', array($this, 'countFeedBandiAgri')),
    );
  }

  public function richiesta_stato_attesa(){

    $today = strtotime(date("Y-m-d"));

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_stato',196)
    //->condition('created', $now->format(DATETIME_DATETIME_STORAGE_FORMAT))
    ->condition('status', 1);

    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach ($richieste as $value) {
        $node = Node::load($value);

        if(!empty($node->get('field_cron_alert_cambio_stato')->getValue()[0]['value'])){
          //if($node->get('created')->getValue()[0]['value'] == )
          $date_created = date('Y-m-d',$node->get('field_cron_alert_cambio_stato')->getValue()[0]['value']);
          $date_created_stakeholder = date('d-m-Y',$node->get('created')->getValue()[0]['value']);
          $new_date = date('Y-m-d', strtotime($date_created. ' + 3 days'));
          $stakeholder = Node::load($node->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
          if($today == $new_date){
            if(!empty($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
              NotificationController::cron_alert_stato_attesa($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'],$value,$node->get('title')->getValue()[0]['value'],$date_created_stakeholder);

              $node->set('field_cron_alert_cambio_stato', $date_created);
              $node->save();
            }
          }
        }else{
          //$created = date('Y-m-d',$node->get('created')->getValue()[0]['value']);
          //echo $node->get('created')->getValue()[0]['value'];
          $date_created = date('Y-m-d',$node->get('created')->getValue()[0]['value']);
          $date_created_stakeholder = date('d-m-Y',$node->get('created')->getValue()[0]['value']);

          $new_date = date('Y-m-d', strtotime($date_created. ' + 3 days'));
          //$created = $date_created. ' + 3 days';
          if($today == strtotime($new_date)){
            $stakeholder = Node::load($node->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
            if(!empty($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
              NotificationController::cron_alert_stato_attesa($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'],$value,$node->get('title')->getValue()[0]['value'],$date_created_stakeholder);

              $node->set('field_cron_alert_cambio_stato', $date_created);
              $node->save();
            }
          }
          //$node->get('created')->getValue()[0]['value']
        }
      }
    }
  }

  public function importFeedOpportunity(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'opportunity')
    ->condition('status', 1);
    $spazi = $query->execute();
    if(count($spazi)>0){
      foreach ($spazi as $value) {
        $node = Node::load($value);
        $node->delete($node);
      }
    }
    $json = file_get_contents('https://www.opportunityliguria.it/it/?option=com_spproperty&view=properties&searchitem=1&Itemid=605&format=json');
    // you can save $json to a file, if needed :
    //file_put_contents('file/path/my-file.txt', $json);
    $data = json_decode($json);
    foreach($data as $location){
      if($location->provincia=="GE"):
        echo"".$location->image." - ".$location->title." - ".$location->address." - ".$location->psize."mq - https://www.opportunityliguria.it/".$location->image." - https://www.opportunityliguria.it".$location->url."<br>";
        /*$source_directory = 'https://www.opportunityliguria.it/';
        $directory_for_saving_images = 'public://' . date('Y-m', time());
        file_prepare_directory($directory_for_saving_images, FILE_CREATE_DIRECTORY);
        $filename_to_be_saved = $directory_for_saving_images . '/' . $location->image;
        $image_data = file_get_contents($source_directory . '/' . $location->image);
        $file_object = file_save_data($image_data, $filename_to_be_saved, FILE_EXISTS_RENAME);*/
        $node_new = Node::create([
            'type'        => 'opportunity',
            'title'       => $location->title,
            'field_categoria' => $location->category_name,
            'field_citta' => $location->address,
            'field_superficie' =>$location->psize,
            'field_url' => 'https://www.opportunityliguria.it'.$location->url,
            'field_immagine_url' => 'https://www.opportunityliguria.it/'.$location->image,
            'status' => 1
          ]);
          /*$node_new->set('field_immagine', [
            'target_id' => $file_object,
            'alt' => $title,
          ]);*/
          $node_new->save();
        endif;
    }
  }

  public function importFeedBandiFilse(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'bando')
    ->condition('field_ente','FILSE')
    ->condition('status', 1);
    $bandi = $query->execute();
    if(count($bandi)>0){
      foreach ($bandi as $value) {
        $node = Node::load($value);
        $node->delete($node);
      }
    }
    $myXMLData = file_get_contents('https://www.filse.it/servizi/agevolazioni.html?format=feed');
    $xml=simplexml_load_string($myXMLData) or die("Error: Cannot create object");
    //echo '<pre>';
    //print_r($xml);
    //exit();
    foreach($xml->channel->item as $bando){
      if(strpos($bando->description, "impres")>0 /*&& date("Y-m-d",strtotime($bando->pubDate))>=date("Y-01-01")*/ ):
        $apertura_ok = "";
        print"Titolo: ".($bando->title)."<br>Link: ".$bando->link."<br>Data: ".$bando->pubDate."<br>Categoria: ".$bando->category."<br><br>---------------------------------<br><br>";
        $link_domanda = strpos($bando->link, "%3Fview");
        $link_ok = substr($bando->link, 0, $link_domanda);
        if($link_ok=="") {
          $link_domanda = strpos($bando->link, "?view");
          $link_ok = substr($bando->link, 0, $link_domanda);
        }
        $apertura_pos = strpos($bando->description, "Data di apertura: ");
        $apertura_pos2 = strpos($bando->description, "<br/>Data di chiusura");
        if($apertura_pos>0) $apertura_ok = substr($bando->description, $apertura_pos+18,-1);
        //echo "Data di apertura: ".$apertura_ok;
        //exit();
        $node_new = Node::create([
            'type'        => 'bando',
            'title'       => $bando->title,
            'body'       => $bando->description,
            'field_categoria' => $bando->category,
            'field_ente' => 'FILSE',
            'field_importato' => 1,
            'field_url_bando' => $link_ok,
            'field_note' => "Data di apertura: ".$apertura_ok,
            'field_data_di_pubblicazione' => date("Y-m-d",strtotime($bando->pubDate)),
            'status' => 1
          ]);
        $node_new->save();
      endif;
    }
  }

  public function importFeedBandiRegione(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'bando')
    ->condition('field_ente','Regione Liguria (RL)')
    ->condition('status', 1);
    $bandi = $query->execute();
    if(count($bandi)>0){
      foreach ($bandi as $value) {
        $node = Node::load($value);
        $node->delete($node);
      }
    }
    $myXMLData = file_get_contents('https://www.regione.liguria.it/bandi-e-avvisi/contributi/contributi-aperti.html?format=feed');
    $xml=simplexml_load_string($myXMLData) or die("Error: Cannot create object");
    //echo '<pre>';
    //print_r($xml);
    //exit();
    foreach($xml->channel->item as $bando){
      $apertura_ok = "";
      if(strpos($bando->description, "impres")>0 /*&& date("Y-m-d",strtotime($bando->pubDate))>=date("Y-01-01")*/ ):
        $link_domanda = strpos($bando->link, "%3Fview");
        $link_ok = substr($bando->link, 0, $link_domanda);
        if($link_ok=="") {
          $link_domanda = strpos($bando->link, "?view");
          $link_ok = substr($bando->link, 0, $link_domanda);
        }
        $apertura_pos = strpos($bando->description, "Data di apertura: ");
        $apertura_pos2 = strpos($bando->description, "<br/>Data di chiusura");
        if($apertura_pos>0) $apertura_ok = substr($bando->description, $apertura_pos,-1);
        $node_new = Node::create([
            'type'        => 'bando',
            'title'       => $bando->title,
            'body'       => $bando->description,
            'field_categoria' => $bando->category,
            'field_ente' => 'Regione Liguria (RL)',
            'field_note' => $apertura_ok,
            'field_importato' => 1,
            'field_url_bando' => $link_ok,
            'field_data_di_pubblicazione' => date("Y-m-d",strtotime($bando->pubDate)),
            'status' => 1
          ]);
        $node_new->save();
      print"Titolo: ".($bando->title)."<br>Link: ".$bando->link."<br>Data: ".$bando->pubDate."<br>Note: ".$apertura_ok."<br><br>---------------------------------<br><br>";

      endif;
    }
  }

  public function importFeedBandiAgri(){
    /*$query = \Drupal::entityQuery('node')
    ->condition('type', 'bando')
    ->condition('field_ente','Regione Liguria (RL)')
    ->condition('status', 1);
    $bandi = $query->execute();
    if(count($bandi)>0){
      foreach ($bandi as $value) {
        $node = Node::load($value);
        //$node->delete($node);
      }
    }*/
    $myXMLData = file_get_contents('http://www.agriligurianet.it/it/impresa/sostegno-economico/programma-di-sviluppo-rurale-psr-liguria/psr-2014-2020/bandi-aperti-psr2014-2020/publiccompetitions/?view=publiccompetitions&id=&showStatus=1&showBandi=1&format=feed');
    $xml=simplexml_load_string($myXMLData) or die("Error: Cannot create object");
    /*echo '<pre>';
    print_r($xml);
    exit();*/
    foreach($xml->channel->item as $bando){
      if(strpos($bando->description, "impres")>0 /*&& date("Y-m-d",strtotime($bando->pubDate))>=date("Y-01-01")*/ ):
        $apertura_ok = "";
        $link_domanda = strpos($bando->link, "%3Fview");
        $link_ok = substr($bando->link, 0, $link_domanda);
        if($link_ok=="") {
          $link_domanda = strpos($bando->link, "?view");
          $link_ok = substr($bando->link, 0, $link_domanda);
        }
        $apertura_pos = strpos($bando->description, "Data di chiusura: ");
        $apertura_pos2 = strpos($bando->description, "<br/>Data di chiusura");
        if($apertura_pos>0) $apertura_ok = substr($bando->description, $apertura_pos,-1);
        $node_new = Node::create([
            'type'        => 'bando',
            'title'       => $bando->title,
            'body'       => $bando->description,
            'field_categoria' => $bando->category,
            'field_ente' => 'Regione Liguria (RL)',
            'field_note' => $apertura_ok,
            'field_importato' => 1,
            'field_url_bando' => $link_ok,
            'field_data_di_pubblicazione' => date("Y-m-d",strtotime($bando->pubDate)),
            'status' => 1
          ]);
        print"Titolo: ".($bando->title)."<br>Link: ".$bando->link."<br>Data: ".$bando->pubDate."<br>Note: ".$apertura_ok."<br><br>---------------------------------<br><br>";

        $node_new->save();
      endif;
    }
  }



  public function countFeedBandiFilse(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'bando')
    ->condition('field_ente','FILSE')
    ->condition('status', 1);
    $bandi = $query->execute();
    return count($bandi);
  }

  public function countFeedBandiRegione(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'bando')
    ->condition('field_ente','Regione Liguria (RL)')
    ->condition('status', 1);
    $bandi = $query->execute();
    return count($bandi);
  }

  public function countFeedBandiAgri(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'bando')
    ->condition('field_ente','Regione Liguria (RL)')
    ->condition('status', 1);
    $bandi = $query->execute();
    return count($bandi);
  }

  public function importFeedNewsWylab(){

    setlocale(LC_TIME, 'ita', 'it_IT');

    $ch = curl_init("https://wylab.net/?call_custom_simple_rss=1&csrp_cat=-26&csrp_thumbnail_size=large");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    //print_r($result);
    curl_close($ch);

    $xml=simplexml_load_string($result) or die("Error: Cannot create object");
    /*echo '<pre>';
    print_r($xml);
    exit();*/

    foreach($xml->channel->item as $news){
      $data_news = substr($news->pubDate, 4,12);
      $node_new = Node::create([
          'type'        => 'article',
          'title'       => ($news->title),
          'field_immagine_url'       => ($news->enclosure["url"]),
          'field_url' => $news->link,
          'field_ente' => 'Wylab',
          'field_formazione' => '0',
          'field_importato' => 1,
          'field_data_di_pubblicazione' => date("Y-m-d",strtotime(str_replace("Dic","Dec",str_replace("Ott","Oct",str_replace("Set","Sep",str_replace("Ago","Aug",str_replace("Lug","Jul",str_replace("Giu","Jun",str_replace("Mag","May",$data_news))))))))),
          'status' => 0
        ]);
        $query = \Drupal::entityQuery('node')
        ->condition('type', 'article')
        ->condition('field_url', $news->link);
        $notizie = $query->execute();
        if(count($notizie)==0){
          $node_new->save();
        }
        print"Titolo: ".($news->title)."<br>Link: ".$news->link."<br>Data: ".$data_news."<br>Immagine: ".$news->enclosure["url"]."<br><br>";
        //exit();

    }
  }

  public function importFeedFormazioneWylab(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->condition('field_ente','Wylab')
    ->condition('field_formazione',1);
    $bandi = $query->execute();
    if(count($bandi)>0){
      foreach ($bandi as $value) {
        $node = Node::load($value);
        $node->delete($node);
      }
    }

    setlocale(LC_TIME, 'ita', 'it_IT');

    $ch = curl_init("https://wylab.net/?call_custom_simple_rss=1&csrp_cat=26&csrp_thumbnail_size=large");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    //print_r($result);
    curl_close($ch);

    $xml=simplexml_load_string($result) or die("Error: Cannot create object");
    /*echo '<pre>';
    print_r($xml);
    exit();*/

    foreach($xml->channel->item as $news){
      $data_news = substr($news->pubDate, 4,12);
      $node_new = Node::create([
          'type'        => 'article',
          'title'       => ($news->title),
          'field_immagine_url'       => ($news->enclosure["url"]),
          'field_url' => $news->link,
          'field_ente' => 'Wylab',
          'field_importato' => 1,
          'field_formazione' => '1',
          'field_data_di_pubblicazione' => date("Y-m-d",strtotime(str_replace("Dic","Dec",str_replace("Ott","Oct",str_replace("Set","Sep",str_replace("Ago","Aug",str_replace("Lug","Jul",str_replace("Giu","Jun",str_replace("Mag","May",$data_news))))))))),
          'status' => 1
        ]);
        $node_new->save();
        print"Titolo: ".($news->title)."<br>Link: ".$news->link."<br>Data: ".$data_news."<br>Description: ".$news->description."<br><br>";

    }
  }

  public function importFeedVetrinaTassonomie(){

    $serverName = "192.168.152.42";
    $connectionOptions = array(
        "Database" => "sislavt",
        "Uid" => "gmgnet",
        "PWD" => "X5H3ZbC2J"
    );
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    //mercati di riferimento
    $sql = "SELECT * FROM PLF_T_MERCATI";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      echo $nome."<br>";
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'mercato_impresa')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
        
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'mercato_impresa',
        ])->save();
        
      }
    }
    sqlsrv_free_stmt($stmt);
    // fine mercati

    //settori impresa
    $sql = "SELECT * FROM PLF_T_SETTORE_IMPRESA";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      //echo $nome."<br>";
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'settore_impresa')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'settore_impresa',
        ])->save();
      }

    }
    sqlsrv_free_stmt($stmt);
    // fine settori impresa

    //settori progetti
    $sql = "SELECT * FROM PLF_T_SETTORE_PROGETTI";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'settore_progetto')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'settore_progetto',
        ])->save();
      }
    }
    sqlsrv_free_stmt($stmt);
    // fine settori progetti

    //settori prodotti
    $sql = "SELECT * FROM PLF_T_SETTORE_PROGETTI_PRODOTTI";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'settori_prodotti')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'settori_prodotti',
        ])->save();
      }
    }
    sqlsrv_free_stmt($stmt);
    // fine settori prodotti

    //settori tecnologie
    $sql = "SELECT * FROM PLF_T_SETTORE_TECNOLOGIE";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'settori_tecnologie')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'settori_tecnologie',
        ])->save();
      }
    }
    sqlsrv_free_stmt($stmt);
    // fine settori tecnologie

    // innovazione
    $sql = "SELECT * FROM PLF_T_INNOVAZIONE";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'innovazione')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'innovazione',
        ])->save();
      }
    }
    sqlsrv_free_stmt($stmt);
    // fine innovazione

    // MACROAREE servizi
    $sql = "SELECT * FROM PLF_T_MACROAREA_SERVIZI";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    $nome = "";
    $codice = "";
    $id = "";
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
      $nome = $row['DESCRIZIONE'];
      $codice = $row['CODICE'];
      $id_vetrina = $row['ID'];
      if ($terms = taxonomy_term_load_multiple_by_name($nome, 'macroaree')) {
        // Only use the first term returned; there should only be one anyways if we do this right.
      } else {
        $term = Term::create([
          'name' => $nome,
          'field_id_vetrina' => $id_vetrina,
          'field_codice_vetrina' => $codice,
          'vid' => 'macroaree',
        ])->save();
      }
    }
    sqlsrv_free_stmt($stmt);
    // fine MACROAREE servizi

    echo"Fine";
    exit();

  }

  public function importFeedVetrinaImprese(){

    $serverName = "192.168.152.42";
    $connectionOptions = array(
        "Database" => "sislavt",
        "Uid" => "gmgnet",
        "PWD" => "X5H3ZbC2J"
    );
    //Establishes the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if($conn)
        //echo "Connected!";
    //if($_GET["tipo"]=="") $sql = "SELECT PLF_V_IMPRESA.*, PLF_IMPRESA.* FROM PLF_V_IMPRESA, PLF_IMPRESA WHERE PLF_V_IMPRESA.ID_IMPRESA = PLF_IMPRESA.ID_PLF_IMPRESA AND PLF_IMPRESA.PUBBLICATO = 1 ";
    $sql = "SELECT * FROM PLF_IMPRESA, PLF_IMPRESA_TRANSLATION WHERE PLF_IMPRESA_TRANSLATION.ID_PLF_IMPRESA =PLF_IMPRESA.ID_PLF_IMPRESA AND PLF_IMPRESA.PUBBLICATO = 1";
    $stmt = sqlsrv_query( $conn, $sql );
    if( $stmt === false) {
        //die( print_r( sqlsrv_errors(), true) );
    }

    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
        $ID_IMPRESA = $row['ID_PLF_IMPRESA'];
        $RAGIONE_SOCIALE = $row['DESC_IMPRESA'];
        $CODICE_FISCALE = $row['COD_FISCALE'];
        $PARTITA_IVA = $row['PARTITA_IVA'];
        $NOME_LEGALE_RAPPRESENTANTE = $row['NOME_LEGALE_RAPPRESENTANTE'];
        $COGNOME_LEGALE_RAPPRESENTANTE = $row['COGNOME_LEGALE_RAPPRESENTANTE'];
        $DESC_INDIRIZZO = $row['DESC_INDIRIZZO'];
        $NUMERO_CIVICO = $row['NUMERO_CIVICO'];
        $COORD_X = $row['COORD_X'];
        $COORD_Y = $row['COORD_Y'];
        $ID_PLF_T_COMUNE = $row['ID_PLF_T_COMUNE'];
        $ACCREDITATA = $row['DATA_ACCREDITAMENTO'];
        $ID_PLF_T_SETTORE_IMPRESA = $row['ID_PLF_T_SETTORE_IMPRESA']; //tassonomia
        $ID_PLF_T_ATECO = $row['ID_PLF_T_ATECO']; //tassonomia
        $ID_PLF_T_COMUNE = $row['ID_PLF_T_COMUNE']; //tassonomia
        $COD_CAP = $row['COD_CAP'];
        $DESC_TELEFONO = $row['DESC_TELEFONO'];
        $ID_PLF_T_CLASSE_ADDETTI = $row['ID_PLF_T_CLASSE_ADDETTI']; //tassonomia
        $ID_PLF_T_CLASSE_CAPITALE = $row['ID_PLF_T_CLASSE_CAPITALE']; //tassonomia
        $ID_PLF_T_CLASSE_PRODUZIONE = $row['ID_PLF_T_CLASSE_PRODUZIONE']; //tassonomia
        $DESC_SITO = $row['DESC_SITO'];
        $FACEBOOK = $row['FACEBOOK'];
        $YOUTUBE = $row['YOUTUBE'];
        $TWITTER = $row['TWITTER'];
        $LINKEDIN = $row['LINKEDIN'];
        $FLICKR = $row['FLICKR'];
        $INTRAGRAM = $row['INTRAGRAM'];
        $PUBBLICATO = $row['PUBBLICATO'];
        //$STAKEHOLDER = $row['STAKEHOLDER'];
        $ID_STATO_IMPRESA = $row['ID_STATO_IMPRESA']; //tassonomia
        $ID_NATURA_GIURIDICA = $row['ID_NATURA_GIURIDICA'];
        $PRIMO_REQUISITO_PMI = $row['PRIMO_REQUISITO_PMI'];
        $SECONDO_REQUISITO_PMI = $row['SECONDO_REQUISITO_PMI'];
        $TERZO_REQUISITO_PMI = $row['TERZO_REQUISITO_PMI'];
        $COGNOME_CONTATTO = $row['COGNOME_CONTATTO'];
        $NOME_CONTATTO = $row['NOME_CONTATTO'];
        $EMAIL_CONTATTO = $row['EMAIL_CONTATTO'];
        $ID_PLF_T_PREVALENZA_FEMMINILE = $row['ID_PLF_T_PREVALENZA_FEMMINILE']; //tassonomia
        $ID_PLF_T_PREVALENZA_GIOVANILE = $row['ID_PLF_T_PREVALENZA_GIOVANILE']; //tassonomia
        $ID_PLF_T_PREVALENZA_STRANIERA = $row['ID_PLF_T_PREVALENZA_STRANIERA']; //tassonomia
        $NUM_ULTIMO_FATTURATO = $row['NUM_ULTIMO_FATTURATO'];
        $BREVETTO = $row['BREVETTO'];
        //echo"<pre>";
        //echo print_r($row)."<br />";

        //codice ateco
        if($ID_PLF_T_ATECO!=0 || $ID_PLF_T_ATECO!=NULL){
          $sql = "SELECT C_ATTIVITA, DESCRIZIONE FROM PLF_T_ATECO WHERE ID_PLF_T_ATECO = $ID_PLF_T_ATECO ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $CODICE_ATECO = $row2['C_ATTIVITA'];
              $DESC_CODICE_ATECO = $row2['DESCRIZIONE'];
          }
        }

        //natura giuridica
        if($ID_NATURA_GIURIDICA!=0 || $ID_NATURA_GIURIDICA!=NULL){
          $sql = "SELECT DESCRIZIONE FROM PLF_T_NATURA_GIURIDICA WHERE ID = $ID_NATURA_GIURIDICA ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $NATURA_GIURIDICA = $row2['DESCRIZIONE'];
          }
        }

        //comune
        if($ID_PLF_T_COMUNE!=0 || $ID_PLF_T_COMUNE!=NULL){
          $sql = "SELECT DESC_COMUNE FROM PLF_T_COMUNE WHERE ID_PLF_T_COMUNE = $ID_PLF_T_COMUNE ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $COMUNE = $row2['DESC_COMUNE'];
          }
        }

        //classe addetti
        if($ID_PLF_T_CLASSE_ADDETTI!=0 || $ID_PLF_T_CLASSE_ADDETTI!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_CLASSE_ADDETTI WHERE ID = $ID_PLF_T_CLASSE_ADDETTI ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $CLASSE_ADDETTI = $row2['CODICE']." ".$row2["DESCRIZIONE"];
          }
        }

        //classe capitale
        if($ID_PLF_T_CLASSE_CAPITALE!=0 || $ID_PLF_T_CLASSE_CAPITALE!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_CLASSE_CAPITALE WHERE ID = $ID_PLF_T_CLASSE_CAPITALE ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $CLASSE_CAPITALE = $row2['CODICE']." ".$row2["DESCRIZIONE"];
          }
        }

        //classe produzione
        if($ID_PLF_T_CLASSE_PRODUZIONE!=0 || $ID_PLF_T_CLASSE_PRODUZIONE!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_CLASSE_PRODUZIONE WHERE ID = $ID_PLF_T_CLASSE_PRODUZIONE ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $CLASSE_PRODUZIONE = $row2['CODICE']." ".$row2["DESCRIZIONE"];
          }
        }

        //stato impresa
        if($ID_STATO_IMPRESA!=0 || $ID_STATO_IMPRESA!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_STATO_IMPRESA WHERE ID = $ID_STATO_IMPRESA ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $STATO_IMPRESA = $row2["DESCRIZIONE"];
              $STAKEHOLDER = 0;
              if($STATO_IMPRESA=="Stakeholder") $STAKEHOLDER = 1;
          }
        }

        if($PRIMO_REQUISITO_PMI!="S") $PRIMO_REQUISITO_PMI = 0;
        if($PRIMO_REQUISITO_PMI=="S") $PRIMO_REQUISITO_PMI = 1;
        if($SECONDO_REQUISITO_PMI!="S") $SECONDO_REQUISITO_PMI = 0;
        if($SECONDO_REQUISITO_PMI=="S") $SECONDO_REQUISITO_PMI = 1;
        if($TERZO_REQUISITO_PMI!="S") $TERZO_REQUISITO_PMI = 0;
        if($TERZO_REQUISITO_PMI=="S") $TERZO_REQUISITO_PMI = 1;

        $PREVALENZA_FEMMINILE = "";
        $PREVALENZA_GIOVANILE = "";
        $PREVALENZA_STRANIERA = "";
        $CLASSE_ADDETTI = "";
        $CLASSE_CAPITALE = "";
        $CLASSE_PRODUZIONE = "";

        //stato prevalenza
        if($ID_PLF_T_PREVALENZA_FEMMINILE!=0 || $ID_PLF_T_PREVALENZA_FEMMINILE!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_PREVALENZA WHERE ID = $ID_PLF_T_PREVALENZA_FEMMINILE ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $PREVALENZA_FEMMINILE = $row2["CODICE"];
          }
        }
        if($ID_PLF_T_PREVALENZA_GIOVANILE!=0 || $ID_PLF_T_PREVALENZA_GIOVANILE!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_PREVALENZA WHERE ID = $ID_PLF_T_PREVALENZA_GIOVANILE ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $PREVALENZA_GIOVANILE = $row2["CODICE"];
          }
        }
        if($ID_PLF_T_PREVALENZA_STRANIERA!=0 || $ID_PLF_T_PREVALENZA_STRANIERA!=NULL){
          $sql = "SELECT CODICE, DESCRIZIONE FROM PLF_T_PREVALENZA WHERE ID = $ID_PLF_T_PREVALENZA_STRANIERA ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $PREVALENZA_STRANIERA = $row2["CODICE"];
          }
        }

        if($ID_IMPRESA!=0 || $ID_IMPRESA!=NULL){
          $sql = "SELECT DESC_IMPRESA, DESC_MISSIONE, DESC_ATTIVITA, DESC_BREVE_IMPRESA FROM PLF_IMPRESA_TRANSLATION WHERE ID_PLF_IMPRESA = $ID_IMPRESA ";
          $stmt2 = sqlsrv_query( $conn, $sql );
          if( $stmt2 === false) {
              die( print_r( sqlsrv_errors(), true) );
          }
          while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
              $DESCRIZIONE_IMPRESA = $row2["DESC_IMPRESA"];
              $DESCRIZIONE_MISSIONE = $row2["DESC_MISSIONE"];
              $DESC_ATTIVITA = $row2["DESC_ATTIVITA"];
              $DESC_BREVE_IMPRESA = $row2['DESC_BREVE_IMPRESA'];
          }
        }



        //if($ID_IMPRESA==316){
        if($PUBBLICATO==1){
          // importo le imprese nel sito
          $INDIRIZZO = "";
          if($ACCREDITATA!="S") $status = 1;
          if($ACCREDITATA=="" || !$ACCREDITATA) $status = 0;
          if($DESC_INDIRIZZO) $INDIRIZZO = $DESC_INDIRIZZO;
          $node_new = Node::create([
              'type'        => 'impresa',
              'title'       => ($RAGIONE_SOCIALE),
              'field_id_impresa' => $ID_IMPRESA,
              'field_ragione_sociale_' => $RAGIONE_SOCIALE,
              'field_codice_ateco' => $CODICE_ATECO,
              'field_codice_comune' => $COMUNE,
              'field_numero_civico' => $NUMERO_CIVICO,
              //'field_codice_fiscale_legale_rapp' => $RAGIONE_SOCIALE,
              'field_codice_fiscale_impresa' => $CODICE_FISCALE,
              'field_partita_iva' => $PARTITA_IVA,
              'field_cognome_legale_rappresenta' => $COGNOME_LEGALE_RAPPRESENTANTE,
              'field_cognome_delegato_all_aggio' => $COGNOME_CONTATTO,
              'field_descrizione_delle_attivita' => $DESCRIZIONE_IMPRESA,
              //'field_elementi_di_innovazione' => $RAGIONE_SOCIALE,
              'field_email_contatto_riferimento' => $EMAIL_CONTATTO,
              //'field_incubatore_certificato' => $RAGIONE_SOCIALE,
              'field_indirizzo_della_sede' => $COD_CAP,
              'field_cap_impresa' => $INDIRIZZO,
              'field_indirizzo_facebook' => $FACEBOOK,
              'field_indirizzo_instagram' => $INTRAGRAM,
              'field_indirizzo_linkedin' => $LINKEDIN,
              'field_indirizzo_twitter' => $TWITTER,
              'field_indirizzo_youtube' => $YOUTUBE,
              'field_mission' => $DESCRIZIONE_MISSIONE,
              'field_natura_giuridica' => $NATURA_GIURIDICA,
              //'field_natura_giuridica' => $RAGIONE_SOCIALE,
              'field_nome_legale_rappresentante' => $NOME_LEGALE_RAPPRESENTANTE,
              'field_nome_delegato_all_aggiorna' => $NOME_CONTATTO,
              'field_prevalenza_femmil' => $PREVALENZA_FEMMINILE,
              'field_prevalenza_giovanile' => $PREVALENZA_GIOVANILE,
              'field_prevalenza_straniera' => $PREVALENZA_STRANIERA,
              'field_sito_web' => $DESC_SITO,
              'field_stato_impresa' => $STATO_IMPRESA,
              'field_telefono_aienda' => $DESC_TELEFONO,
              'field_coordinata_x' => $COORD_X,
              'field_coordinata_y_' => $COORD_Y,
              'field_stakeholder_' => $STAKEHOLDER,
              'field_classe_addetti' => $CLASSE_ADDETTI,
              'field_classe_capitale' => $CLASSE_CAPITALE,
              'field_classe_' => $CLASSE_PRODUZIONE,
              'field_primo_requisito_pmi' => $PRIMO_REQUISITO_PMI,
              'field_secondo_requisito_pmi' => $SECONDO_REQUISITO_PMI,
              'field_terzo_requisito_pmi' => $TERZO_REQUISITO_PMI,
              'field_ultimo_' => $NUM_ULTIMO_FATTURATO,
              'field_brevetto' => $BREVETTO,
              'field_descrizione_delle_attivita' => $DESC_ATTIVITA,
              'field_descrizione_dell_impresa' => $DESC_BREVE_IMPRESA,
              'status' => $status
            ]);
          $query = \Drupal::entityQuery('node')
          ->condition('type', 'impresa')
          ->condition('field_id_impresa', $ID_IMPRESA);
          $imprese = $query->execute();
          if(count($imprese)>0){
            foreach ($imprese as $value) {
              $node = Node::load($value);
              $node->set('title', $RAGIONE_SOCIALE);
              $node->set('status', $status);
              $node->set('field_ragione_sociale_', $RAGIONE_SOCIALE);
              $node->set('field_partita_iva', $PARTITA_IVA);
              $node->set('field_codice_fiscale_impresa', $CODICE_FISCALE);
              $node->set('field_codice_ateco', $CODICE_ATECO);
              $node->set('field_codice_comune', $COMUNE);
              $node->set('field_cognome_legale_rappresenta', $COGNOME_LEGALE_RAPPRESENTANTE);
              $node->set('field_cognome_delegato_all_aggio', $COGNOME_CONTATTO);
              $node->set('field_descrizione_delle_attivita', $DESCRIZIONE_IMPRESA);
              $node->set('field_email_contatto_riferimento', $EMAIL_CONTATTO);
              $node->set('field_indirizzo_della_sede', $INDIRIZZO);
              $node->set('field_cap_impresa', $COD_CAP);
              $node->set('field_numero_civico', $NUMERO_CIVICO);
              $node->set('field_indirizzo_facebook', $FACEBOOK);
              $node->set('field_indirizzo_instagram', $INTRAGRAM);
              $node->set('field_indirizzo_linkedin', $LINKEDIN);
              $node->set('field_indirizzo_twitter', $TWITTER);
              $node->set('field_indirizzo_youtube', $YOUTUBE);
              $node->set('field_mission', $DESCRIZIONE_MISSIONE);
              $node->set('field_natura_giuridica', $NATURA_GIURIDICA);
              $node->set('field_nome_legale_rappresentante', $NOME_LEGALE_RAPPRESENTANTE);
              $node->set('field_nome_delegato_all_aggiorna', $NOME_CONTATTO);
              $node->set('field_prevalenza_femmil', $PREVALENZA_FEMMINILE);
              $node->set('field_prevalenza_giovanile', $PREVALENZA_GIOVANILE);
              $node->set('field_prevalenza_straniera', $PREVALENZA_STRANIERA);
              $node->set('field_sito_web', $DESC_SITO);
              $node->set('field_stato_impresa', $STATO_IMPRESA);
              $node->set('field_telefono_aienda', $DESC_TELEFONO);
              $node->set('field_coordinata_x', $COORD_X);
              $node->set('field_coordinata_y_', $COORD_Y);
              $node->set('field_stakeholder_', $STAKEHOLDER);
              $node->set('field_classe_addetti', $CLASSE_ADDETTI);
              $node->set('field_classe_capitale', $CLASSE_CAPITALE);
              $node->set('field_classe_', $CLASSE_PRODUZIONE);
              $node->set('field_primo_requisito_pmi', $PRIMO_REQUISITO_PMI);
              $node->set('field_secondo_requisito_pmi', $SECONDO_REQUISITO_PMI);
              $node->set('field_terzo_requisito_pmi', $TERZO_REQUISITO_PMI);
              $node->set('field_ultimo_', $NUM_ULTIMO_FATTURATO);
              $node->set('field_brevetto', $BREVETTO);
              $node->set('field_descrizione_delle_attivita', $DESC_ATTIVITA);
              $node->set('field_descrizione_dell_impresa', $DESC_BREVE_IMPRESA);
              $node->set('field_mercati_di_riferimento', []);
              $node->set('field_elementi_di_innovazione2', []);

              $terms = array();
              $innovazioni = array();

              $sql22 = "SELECT PLF_T_MERCATI.* FROM PLF_R_IMPRESA_MERCATI, PLF_T_MERCATI WHERE PLF_R_IMPRESA_MERCATI.ID_PLF_IMPRESA = $ID_IMPRESA AND PLF_R_IMPRESA_MERCATI.ID_MERCATI = PLF_T_MERCATI.ID";
              $stmt22 = sqlsrv_query( $conn, $sql22 );
              if( $stmt22 === false) {
                  //die( print_r( sqlsrv_errors(), true) );
              }
              while( $row22 = sqlsrv_fetch_array( $stmt22, SQLSRV_FETCH_ASSOC) ) {
                $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                  ->loadByProperties(['name' => $row22["DESCRIZIONE"], 'vid' => 'mercato_impresa']);
                if ($term) {
                  // Only use the first term returned; there should only be one anyways if we do this right.
                  $term = reset($term);
                  $terms[] = $term->id();
                  $node->field_mercati_di_riferimento->appendItem($term->id());
                }
                //$node->set('field_mercati_di_riferimento', $terms);
              }
              sqlsrv_free_stmt($stmt22);

              $sql22 = "SELECT PLF_T_INNOVAZIONE.* FROM PLF_R_IMPRESA_INNOVAZIONE, PLF_T_INNOVAZIONE WHERE PLF_R_IMPRESA_INNOVAZIONE.ID_PLF_IMPRESA = $ID_IMPRESA AND PLF_R_IMPRESA_INNOVAZIONE.ID_INNOVAZIONE = PLF_T_INNOVAZIONE.ID";
              $stmt22 = sqlsrv_query( $conn, $sql22 );
              if( $stmt22 === false) {
                  //die( print_r( sqlsrv_errors(), true) );
              }
              while( $row22 = sqlsrv_fetch_array( $stmt22, SQLSRV_FETCH_ASSOC) ) {
                $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                  ->loadByProperties(['name' => $row22["DESCRIZIONE"], 'vid' => 'innovazione']);
                if ($term) {
                  // Only use the first term returned; there should only be one anyways if we do this right.
                  $term = reset($term);
                  $terms[] = $term->id();
                  $node->field_elementi_di_innovazione2->appendItem($term->id());
                }
                //$node->set('field_elementi_di_innovazione2', $terms);
              }
              sqlsrv_free_stmt($stmt22);

              //settore impresa
              if($ID_PLF_T_SETTORE_IMPRESA!=0 || $ID_PLF_T_SETTORE_IMPRESA!=NULL){
                $sql = "SELECT PLF_T_SETTORE_IMPRESA.* FROM PLF_IMPRESA, PLF_T_SETTORE_IMPRESA WHERE PLF_T_SETTORE_IMPRESA.ID = $ID_PLF_T_SETTORE_IMPRESA AND PLF_T_SETTORE_IMPRESA.ID = PLF_IMPRESA.ID_PLF_T_SETTORE_IMPRESA ";
                $stmt2 = sqlsrv_query( $conn, $sql );
                if( $stmt2 === false) {
                    //die( print_r( sqlsrv_errors(), true) );
                }
                while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                    ->loadByProperties(['name' => $row2["DESCRIZIONE"], 'vid' => 'settore_impresa']);
                  if ($term) {
                    // Only use the first term returned; there should only be one anyways if we do this right.
                    $term = reset($term);
                    $terms[] = $term->id();
                    $node->field_settori_dell_impresa->appendItem($term->id());
                  }
                  //$node->set('field_settori_dell_impresa', $terms);
                }
              }
              sqlsrv_free_stmt($stmt2);
              // fine settore impresa

              //allegati impresa
              $allegati = "";
              $sql = "SELECT * FROM PLF_IMPRESA_ALLEGATI WHERE ID_PLF_IMPRESA = $ID_IMPRESA";
              $stmt2 = sqlsrv_query( $conn, $sql );
              if( $stmt2 === false) {
                  //die( print_r( sqlsrv_errors(), true) );
              }
              if( $stmt2 != false) {
                  //die( print_r( sqlsrv_errors(), true) );
                  $allegati .= "<div class='row'>";
              }
              while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                if($row2["ALLEGATO"]!=""){
                  $b64 = $row2["ALLEGATO"];
                  $bin = base64_encode($b64);
                  $bin2 = base64_decode($b64);
                  $time = time();
                  $myfile = fopen("/var/www/lavoro/produzione/web/sites/default/files/".$time."".$row2["PATH_NAME"], "w") or die("Unable to open file!");
                  $url_file = "/sites/default/files/".$time."".$row2["PATH_NAME"];
                  $path_parts = pathinfo("/var/www/lavoro/produzione/web/sites/default/files/".$time."".$row2["PATH_NAME"]);
                  fwrite($myfile, $b64);
                  fclose($myfile);
                  //$node->field_url_allegati->appendItem($url_file);
                  $allegati .= "<div class='col-md-3'><div style='box-shadow:1px 1px 10px #ccc;padding:10px;text-align:center;margin-bottom:30px;min-height:140px;border-radius:5px;'><a href='".$url_file."' class='btn btn-primary btn-primary-small m-b-10' target='_blank'><span style='text-transform:uppercase;'>".$path_parts['extension']."</span></a><br><a href='".$url_file."' target='_blank' style='padding-top:10px;'><b>".$row2["NOME"]."</b></a></div></div>";
                }
              }
              $allegati .= "</div>";
              sqlsrv_free_stmt($stmt2);
              // fine allegati impresa

              $node->set('field_allegati', $allegati);

              $node->save();
            }
          }
          else{
            $node_new->save();
          }
          echo "ID Impresa: ".$ID_IMPRESA;

          //inizio import Progetti
          if($_GET["progetti"]==1):
          $sql3 = "SELECT PLF_PROGETTI_PRODOTTI.*, PLF_PROGETTI_PRODOTTI_TRANSLATION.* FROM PLF_PROGETTI_PRODOTTI, PLF_PROGETTI_PRODOTTI_TRANSLATION WHERE PLF_PROGETTI_PRODOTTI.ID_PLF_PROGETTI_PRODOTTI = PLF_PROGETTI_PRODOTTI_TRANSLATION.ID_PLF_PROGETTI_PRODOTTI AND PLF_PROGETTI_PRODOTTI.ID_PLF_IMPRESA =  $ID_IMPRESA AND PLF_PROGETTI_PRODOTTI.PUBBLICATO = 1";
          $stmt3 = sqlsrv_query( $conn, $sql3 );
          while( $row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC) ) {
            $ID_PROGETTO = $row3['ID_PLF_PROGETTI_PRODOTTI'];
            $NOME_PROGETTO_PRODOTTO = $row3['NOME_PROGETTO_PRODOTTO'];
            $DESCRIZIONE = $row3['DESCRIZIONE'];
            $DESC_CONTATTI = $row3['DESC_CONTATTI'];
            $DESC_SITO = $row3['DESC_SITO'];
            $CARATTERISTICHE_TECNICHE = $row3['CARATTERISTICHE_TECNICHE'];
            $OBIETTIVI = $row3['OBIETTIVI'];
            $DESC_CONTATTI = $row3['DESC_CONTATTI'];
            $NUM_DURATA = $row3['NUM_DURATA'];
            $DATA_SCADENZA = $row3['DATA_SCADENZA'];
            $NUM_VALORE_ECONOMICO = $row3['NUM_VALORE_ECONOMICO'];
            $ID_TIPOLOGIA_PROGETTO = $row3['ID_TIPOLOGIA_PROGETTO'];
            $ID_TIPO_PROGETTI_PRODOTTI = $row3['ID_TIPO_PROGETTI_PRODOTTI'];
            $ID_PLF_IMPRESA = $row3['ID_PLF_IMPRESA'];
            $TIPO="";
            if($ID_TIPO_PROGETTI_PRODOTTI!="" || $ID_TIPO_PROGETTI_PRODOTTI!=0):
              $sql4 = "SELECT * FROM PLF_T_TIPO_PROGETTI_PRODOTTI WHERE ID = $ID_TIPO_PROGETTI_PRODOTTI";
              $stmt4 = sqlsrv_query( $conn, $sql4 );
              if( $stmt4 === false) {
                  //die( print_r( sqlsrv_errors(), true) );
              }
              while( $row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC) ) {
                $TIPO = $row4['DESCRIZIONE'];
              }
              if($TIPO=="Progetto") $IDTIPO = 210;
              if($TIPO=="Prodotto") $IDTIPO = 211;
              if($TIPO=="Tecnologia") $IDTIPO = 212;
              $TIPOLOGIA_PROGETTO = 0;
              if($TIPO=="Progetto") $TIPOLOGIA_PROGETTO = 340;
              if($ID_TIPOLOGIA_PROGETTO=="Sviluppo") $TIPOLOGIA_PROGETTO = 215;
              if($ID_TIPOLOGIA_PROGETTO=="Industrializzazione") $TIPOLOGIA_PROGETTO = 216;
              if($ID_TIPOLOGIA_PROGETTO=="Studi di fattibilità") $TIPOLOGIA_PROGETTO = 213;
              if($ID_TIPOLOGIA_PROGETTO=="Ricerca industriale") $TIPOLOGIA_PROGETTO = 214;
              if($ID_TIPOLOGIA_PROGETTO=="Open innovation") $TIPOLOGIA_PROGETTO = 339;
            endif;
            //settore impresa
            if($ID_PLF_IMPRESA!=0 || $ID_PLF_IMPRESA!=NULL){
              $query = \Drupal::entityQuery('node')
              ->condition('type', 'impresa')
              ->condition('field_id_impresa', $ID_PLF_IMPRESA);
              $progetti = $query->execute();
              if(count($imprese)>0){
                foreach ($imprese as $value) {
                  $NID_IMPRESA = $value;
                }
              }
            }
            // fine settore impresa
            $node_new_progetto = Node::create([
                'type'        => 'catalogo_prodotti_progetti_tecno',
                'title'       => ($NOME_PROGETTO_PRODOTTO),
                'field_id_progetto' => $ID_PROGETTO,
                'body' => $DESCRIZIONE,
                'field_categoria_progetto' => $TIPOLOGIA_PROGETTO,
                'field_tipologia_progetto' => $IDTIPO,
                'field_valore_economico' => $NUM_VALORE_ECONOMICO,
                'field_durata_progetto' => $NUM_DURATA,
                'field_sito_web' => $DESC_SITO,
                'field_impresa_progetto' => $NID_IMPRESA,
                'field_caratteristiche_tecniche' => $CARATTERISTICHE_TECNICHE,
                'field_obbiettivi' => $OBIETTIVI,
                'field_riferimenti' => $DESC_CONTATTI,
                'status' => 1
            ]);
            $query = \Drupal::entityQuery('node')
            ->condition('type', 'catalogo_prodotti_progetti_tecno')
            ->condition('field_id_progetto', $ID_PROGETTO);
            $progetti = $query->execute();
            if(count($progetti)>0){
              foreach ($progetti as $value) {
                $node_progetto = Node::load($value);
                $node_progetto->set('title', $NOME_PROGETTO_PRODOTTO);
                $node_progetto->set('field_id_progetto', $ID_PROGETTO);
                $node_progetto->set('body', $DESCRIZIONE);
                $node_progetto->set('field_categoria_progetto', $TIPOLOGIA_PROGETTO);
                $node_progetto->set('field_tipologia_progetto', $IDTIPO);
                $node_progetto->set('field_impresa_progetto', $NID_IMPRESA);
                $node_progetto->set('field_valore_economico', $NUM_VALORE_ECONOMICO);
                $node_progetto->set('field_durata_progetto', $NUM_DURATA);
                $node_progetto->set('field_sito_web', $DESC_SITO);
                $node_progetto->set('field_caratteristiche_tecniche', $CARATTERISTICHE_TECNICHE);
                $node_progetto->set('field_obbiettivi', $OBIETTIVI);
                $node_progetto->set('field_riferimenti', $DESC_CONTATTI);
                $node_progetto->set('field_settore_progetto', []);
                $node_progetto->set('field_categoria_tecnolo', []);
                echo " - ID Progetto: ".$ID_PROGETTO." (".$TIPO.")";
                $node_progetto->save();

                $node_progetto = Node::load($value);
                //settore progetto prodotto
                $sql22 = "SELECT PLF_T_SETTORE_PROGETTI_PRODOTTI.* FROM PLF_R_PROGETTO_SETTORE_PROGETTI_PRODOTTI, PLF_T_SETTORE_PROGETTI_PRODOTTI WHERE PLF_R_PROGETTO_SETTORE_PROGETTI_PRODOTTI.ID_PLF_PROGETTI_PRODOTTI = $ID_PROGETTO AND PLF_R_PROGETTO_SETTORE_PROGETTI_PRODOTTI.ID_PLF_T_SETTORE_PROGETTI_PRODOTTI = PLF_T_SETTORE_PROGETTI_PRODOTTI.ID";
                $stmt22 = sqlsrv_query( $conn, $sql22 );
                if( $stmt22 === false) {
                    //die( print_r( sqlsrv_errors(), true) );
                }
                while( $row22 = sqlsrv_fetch_array( $stmt22, SQLSRV_FETCH_ASSOC) ) {
                  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                    ->loadByProperties(['name' => $row22["DESCRIZIONE"], 'vid' => 'settori_prodotti']);
                  if ($term) {
                    // Only use the first term returned; there should only be one anyways if we do this right.
                    $term = reset($term);
                    $terms[] = $term->id();
                    $node_progetto->field_settore_progetto->appendItem($term->id());
                  }
                  //$node_progetto->set('field_settore_progetto', $terms);
                }
                sqlsrv_free_stmt($stmt22);

                //settore tecnologia
                $sql22 = "SELECT PLF_T_SETTORE_TECNOLOGIE.* FROM PLF_R_PROGETTO_SETTORE_TECNOLOGIE, PLF_T_SETTORE_TECNOLOGIE WHERE PLF_R_PROGETTO_SETTORE_TECNOLOGIE.ID_PLF_PROGETTI_PRODOTTI = $ID_PROGETTO AND PLF_R_PROGETTO_SETTORE_TECNOLOGIE.ID_PLF_T_SETTORE_TECNOLOGIE = PLF_T_SETTORE_TECNOLOGIE.ID";
                $stmt22 = sqlsrv_query( $conn, $sql22 );
                if( $stmt22 === false) {
                    //die( print_r( sqlsrv_errors(), true) );
                }
                while( $row22 = sqlsrv_fetch_array( $stmt22, SQLSRV_FETCH_ASSOC) ) {
                  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                    ->loadByProperties(['name' => $row22["DESCRIZIONE"], 'vid' => 'categoria_tecnologia']);
                  if ($term) {
                    // Only use the first term returned; there should only be one anyways if we do this right.
                    $term = reset($term);
                    $terms[] = $term->id();
                    $node_progetto->field_categoria_tecnolo->appendItem($term->id());
                  }
                  //$node_progetto->set('field_categoria_tecnolo', $terms);
                }
                sqlsrv_free_stmt($stmt22);

                //allegati progetto
                $allegati_progetti = "";
                $sql = "SELECT * FROM PLF_PROGETTI_PRODOTTI_ALLEGATI WHERE ID_PLF_PROGETTI_PRODOTTI = $ID_PROGETTO";
                $stmt2 = sqlsrv_query( $conn, $sql );
                if( $stmt2 === false) {
                    //die( print_r( sqlsrv_errors(), true) );
                }
                if( $stmt2 != false) {
                    //die( print_r( sqlsrv_errors(), true) );
                    $allegati_progetti .= "<div class='row'>";
                }
                while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                  if($row2["ALLEGATO"]!=""){
                    $b64 = $row2["ALLEGATO"];
                    $bin = base64_encode($b64);
                    $bin2 = base64_decode($b64);
                    $time = time();
                    $myfile = fopen("/var/www/lavoro/produzione/web/sites/default/files/".$time."".$row2["PATH_NAME"], "w") or die("Unable to open file!");
                    $url_file = "/sites/default/files/".$time."".$row2["PATH_NAME"];
                    $path_parts = pathinfo("/var/www/lavoro/produzione/web/sites/default/files/".$time."".$row2["PATH_NAME"]);
                    fwrite($myfile, $b64);
                    fclose($myfile);
                    echo $ID_PROGETTO." - ".$row2["PATH_NAME"]."<br>";
                    //$node->field_url_allegati->appendItem($url_file);
                    $allegati_progetti .= "<div class='col-md-3'><div style='box-shadow:1px 1px 10px #ccc;padding:10px;text-align:center;margin-bottom:30px;min-height:140px;border-radius:5px;'><a href='".$url_file."' class='btn btn-primary btn-primary-small m-b-10' target='_blank'><span style='text-transform:uppercase;'>".$path_parts['extension']."</span></a><br><a href='".$url_file."' target='_blank' style='padding-top:10px;'><b>".$row2["NOME"]."</b></a></div></div>";
                  }
                }
                $allegati_progetti .= "</div>";
                sqlsrv_free_stmt($stmt2);
                $node_progetto->set('field_allegati', $allegati_progetti);
                // fine allegati impresa

                $node_progetto->save();

              }
            }
            else{
              $node_new_progetto->save();
            }
          }
          endif;
          //fine import progetti

          //inizio import news
          if($_GET["news"]==1):
          $sql3 = "SELECT PLF_NEWS_IMPRESA.*, PLF_NEWS_IMPRESA_TRANSLATION.* FROM PLF_NEWS_IMPRESA, PLF_NEWS_IMPRESA_TRANSLATION WHERE PLF_NEWS_IMPRESA.ID_NEWS_IMPRESA = PLF_NEWS_IMPRESA_TRANSLATION.ID_NEWS_IMPRESA AND PLF_NEWS_IMPRESA.ID_PLF_IMPRESA =  $ID_IMPRESA AND PLF_NEWS_IMPRESA.PUBBLICATO = 1 AND PLF_NEWS_IMPRESA.EVIDENZA_PORTALE = 1";
          $stmt3 = sqlsrv_query( $conn, $sql3 );
          while( $row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC) ) {
            $ID_NEWS = $row3['ID_NEWS_IMPRESA'];
            $TITOLO_NEWS = $row3['DESCRIZIONE'];
            $DESCRIZIONE_NEWS = $row3['DATA_TESTO'];
            $LINK_NEWS = $row3['LINK_APPROFONDIMENTO'];
            $DATA_NEWS = $row3['DATA_INIZIO'];
            //settore impresa
            if($ID_PLF_IMPRESA!=0 || $ID_PLF_IMPRESA!=NULL){

              $query = \Drupal::entityQuery('node')
              ->condition('type', 'impresa')
              ->condition('field_id_impresa', $ID_PLF_IMPRESA);
              $progetti = $query->execute();
              if(count($imprese)>0){
                foreach ($imprese as $value) {
                  $NID_IMPRESA = $value;
                }
              }
            }
            // fine settore impresa
            $node_new_news = Node::create([
                'type'        => 'article',
                'title'       => ($TITOLO_NEWS),
                'body' => $DESCRIZIONE_NEWS,
                //'field_data_di_pubblicazione' => $DATA_NEWS,
                'field_id_news_' => $ID_NEWS,
                'field_formazione' => 0,
                'field_opportunita' => 0,
                'field_url' => $LINK_NEWS,
                'field_azienda_associata' => $NID_IMPRESA,
                'status' => 1
            ]);
            $query = \Drupal::entityQuery('node')
            ->condition('type', 'article')
            ->condition('field_id_news_', $ID_NEWS);
            $news = $query->execute();
            if(count($news)>0){
              foreach ($news as $value) {
                $node_news = Node::load($value);
                $node_news->set('title', $TITOLO_NEWS);
                $node_news->set('field_id_news_', $ID_NEWS);
                $node_news->set('body', $DESCRIZIONE_NEWS);
                //$node_news->set('field_data_di_pubblicazione', $DATA_NEWS);
                $node_news->set('field_formazione', 0);
                $node_news->set('field_opportunita', 0);
                $node_news->set('field_url', $LINK_NEWS);
                $node_news->set('field_azienda_associata', $NID_IMPRESA);
                $node_news->field_tags->appendItem(270);
                $node_news->field_categoria_posizione->appendItem(206);
                echo " - ID News: ".$NID_IMPRESA." ";
                $node_news->save();

              }
            }
            else{
              $node_new_news->save();
            }
          }
          endif;
          //fine import news

          if($_GET["servizi"]==1):
            // import servizi
            //$sql4 = "SELECT PLF_SERVIZI.*, PLF_SERVIZI_TRANSLATION.* FROM PLF_SERVIZI, PLF_SERVIZI_TRANSLATION, PLF_R_SERVIZI_IMPRESA WHERE PLF_SERVIZI.ID_SERVIZI = PLF_SERVIZI_TRANSLATION.ID_SERVIZI AND PLF_SERVIZI.ID_SERVIZI = PLF_R_SERVIZI_IMPRESA.ID_SERVIZI AND PLF_R_SERVIZI_IMPRESA.ID_PLF_IMPRESA = $ID_IMPRESA AND PLF_SERVIZI.PUBBLICATO = 1";

            if($ID_IMPRESA==316) $ID_IMPRESA=243;
            $sql4 = "SELECT PLF_R_SERVIZI_IMPRESA.* FROM PLF_R_SERVIZI_IMPRESA WHERE PLF_R_SERVIZI_IMPRESA.ID_PLF_IMPRESA = $ID_IMPRESA ";
            $stmt4 = sqlsrv_query( $conn, $sql4 );
            if( $stmt4 === false) {
                //die( print_r( sqlsrv_errors(), true) );
            }
            while( $row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC) ) {
              $ID_SERVIZIO = $row4['ID_SERVIZI'];
              $sql5 = "SELECT PLF_SERVIZI.*, PLF_SERVIZI_TRANSLATION.* FROM PLF_SERVIZI, PLF_SERVIZI_TRANSLATION WHERE PLF_SERVIZI.ID_SERVIZI = PLF_SERVIZI_TRANSLATION.ID_SERVIZI AND PLF_SERVIZI.PUBBLICATO = 1 AND PLF_SERVIZI.ID_SERVIZI = $ID_SERVIZIO";
              $stmt5 = sqlsrv_query( $conn, $sql5 );
              if( $stmt5 === false) {
                  //die( print_r( sqlsrv_errors(), true) );
              }
              while( $row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC) ) {

                $TITOLO = $row5['TITOLO'];
                if($row5['TITOLO']=="") $TITOLO = "Servizio #".$ID_SERVIZIO;
                $DESCRIZIONE = $row5['DESCRIZIONE'];
                $DESC_RIFERIMENTI = $row5['DESC_RIFERIMENTI'];
                $ID_TIPO_SERVIZIO = $row5['ID_TIPO_SERVIZIO'];
                $ID_PLF_T_AREE_COMPETENZA = $row5['ID_PLF_T_AREE_COMPETENZA'];
                $ID_MODALITA_EROGAZIONE_SERVIZIO = $row5['ID_MODALITA_EROGAZIONE_SERVIZIO'];
                $ID_DENOMINAZIONE_SERVIZIO = $row5['ID_DENOMINAZIONE_SERVIZIO'];
                $node_new_servizio = Node::create([
                    'type'        => 'servizio',
                    'title'       => ($TITOLO),
                    'field_stakeholder' => $NID_IMPRESA,
                    'field_id_servizio' => $ID_SERVIZIO,
                    'body' => $DESCRIZIONE,
                    'status' => 1
                ]);
                $query = \Drupal::entityQuery('node')
                ->condition('type', 'servizio')
                ->condition('field_id_servizio', $ID_SERVIZIO);
                $servizi = $query->execute();
                if(count($servizi)>0){
                  foreach ($servizi as $value) {
                    $node_servizio = Node::load($value);
                    $node_servizio->set('title', $TITOLO);
                    $node_servizio->set('field_id_servizio', $ID_SERVIZIO);
                    $node_servizio->set('field_stakeholder', $NID_IMPRESA);
                    $node_servizio->set('body', $DESCRIZIONE);
                    echo " - ID Servizio: ".$ID_SERVIZIO."";

                    //tipo servizio
                    if($ID_TIPO_SERVIZIO!=0 || $ID_TIPO_SERVIZIO!=NULL){
                      $sql = "SELECT PLF_T_TIPO_SERVIZIO.* FROM PLF_SERVIZI, PLF_T_TIPO_SERVIZIO WHERE PLF_T_TIPO_SERVIZIO.ID = $ID_TIPO_SERVIZIO AND PLF_T_TIPO_SERVIZIO.ID = PLF_SERVIZI.ID_TIPO_SERVIZIO ";
                      $stmt2 = sqlsrv_query( $conn, $sql );
                      if( $stmt2 === false) {
                          //die( print_r( sqlsrv_errors(), true) );
                      }
                      while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                          ->loadByProperties(['name' => $row2["DESCRIZIONE"], 'vid' => 'tipo_di_servizio']);
                        if ($term) {
                          // Only use the first term returned; there should only be one anyways if we do this right.
                          $term = reset($term);
                          $node_servizio->set('field_tipo_servizio', $term->id());
                        }

                      }
                    }
                    sqlsrv_free_stmt($stmt2);
                    // fine tipo servizio

                    //area competenza
                    if($ID_PLF_T_AREE_COMPETENZA!=0 || $ID_PLF_T_AREE_COMPETENZA!=NULL){
                      $sql = "SELECT PLF_T_AREE_COMPETENZA.* FROM PLF_SERVIZI, PLF_T_AREE_COMPETENZA WHERE PLF_T_AREE_COMPETENZA.ID = $ID_TIPO_SERVIZIO AND PLF_T_AREE_COMPETENZA.ID = PLF_SERVIZI.ID_PLF_T_AREE_COMPETENZA ";
                      $stmt2 = sqlsrv_query( $conn, $sql );
                      if( $stmt2 === false) {
                          //die( print_r( sqlsrv_errors(), true) );
                      }
                      while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                          ->loadByProperties(['name' => $row2["DESCRIZIONE"], 'vid' => 'a']);
                        if ($term) {
                          // Only use the first term returned; there should only be one anyways if we do this right.
                          $term = reset($term);
                          $node_servizio->set('field_area_di_competenza', $term->id());
                        }

                      }
                    }
                    sqlsrv_free_stmt($stmt2);
                    // fine area competenza

                    //macroarea
                    $sql22 = "SELECT PLF_T_MACROAREA_SERVIZI.* FROM PLF_R_SERVIZI_MACROAREA, PLF_T_MACROAREA_SERVIZI WHERE PLF_R_SERVIZI_MACROAREA.ID_SERVIZI = $ID_SERVIZIO AND PLF_R_SERVIZI_MACROAREA.ID_MACROAREA = PLF_T_MACROAREA_SERVIZI.ID";
                    $stmt22 = sqlsrv_query( $conn, $sql22 );
                    if( $stmt22 === false) {
                        //die( print_r( sqlsrv_errors(), true) );
                    }
                    while( $row22 = sqlsrv_fetch_array( $stmt22, SQLSRV_FETCH_ASSOC) ) {
                      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                        ->loadByProperties(['name' => $row22["DESCRIZIONE"], 'vid' => 'macroaree']);
                      if ($term) {
                        // Only use the first term returned; there should only be one anyways if we do this right.
                        $term = reset($term);
                        $terms[] = $term->id();
                        $node_servizio->field_macroarea->appendItem($term->id());
                        echo " - ID MACROAREA: ".$row22["DESCRIZIONE"]."";
                      }
                      //$node_progetto->set('field_categoria_tecnolo', $terms);
                    }
                    sqlsrv_free_stmt($stmt22);


                    //$node_servizio->save();

                  }
                }
                else{
                  $node_new_servizio->save();
                }
              }
            }

          endif; //fine import servizi

          }//fine controllo pubblicato
          echo"<br><br>";

    }

    sqlsrv_free_stmt( $stmt);
  }

  public function esegui_cron(){
      CronController::importFeedBandiFilse();
      CronController::importFeedBandiRegione();
      CronController::importFeedBandiAgri();
      CronController::importFeedFormazioneWylab();
      CronController::importFeedNewsWylab();
      CronController::importFeedVetrinaTassonomie();
      CronController::importFeedVetrinaImprese();
  }


}
