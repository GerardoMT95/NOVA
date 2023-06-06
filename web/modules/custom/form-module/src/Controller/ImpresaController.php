<?php

namespace Drupal\ex81\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

use Drupal\Core\Entity\Query;
use Drupal\Core\Entity\Query\QueryInterface;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

use \Symfony\Component\HttpFoundation\Response;

class ImpresaController extends \Twig_Extension {

  public function getName() {
    return 'ex81.ImpresaController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('get_all_imprese', array($this, 'get_all_imprese')),
      new \Twig_SimpleFunction('data_impresa_active',  array($this, 'data_impresa_active')),
      new \Twig_SimpleFunction('get_info_impresa',  array($this, 'get_info_impresa')),
      new \Twig_SimpleFunction('get_all_imprese_gestite',  array($this, 'get_all_imprese_gestite')),
      new \Twig_SimpleFunction('count_progetti',  array($this, 'count_progetti')),
      new \Twig_SimpleFunction('count_prodotti',  array($this, 'count_prodotti')),
      new \Twig_SimpleFunction('count_tecnologie',  array($this, 'count_tecnologie')),
      new \Twig_SimpleFunction('count_news',  array($this, 'count_news')),
      new \Twig_SimpleFunction('count_servizi',  array($this, 'count_servizi')),
      new \Twig_SimpleFunction('count_spazi',  array($this, 'count_spazi')),
      new \Twig_SimpleFunction('get_progetti',  array($this, 'get_progetti')),
      //new \Twig_SimpleFunction('get_settore_tassonomia',  array($this, 'get_settore_tassonomia')),
      new \Twig_SimpleFunction('list_progetti_by_settore',  array($this, 'list_progetti_by_settore')),
      new \Twig_SimpleFunction('list_progetti_by_settore_prodotti',  array($this, 'list_progetti_by_settore_prodotti')),

      new \Twig_SimpleFunction('list_progetti_by_categoria',  array($this, 'list_progetti_by_categoria')),
      new \Twig_SimpleFunction('list_tecnologie_by_categoria',  array($this, 'list_tecnologie_by_categoria')),
      new \Twig_SimpleFunction('list_imprese_by_mercato',  array($this, 'list_imprese_by_mercato')),
      new \Twig_SimpleFunction('get_name_stakeholder',  array($this, 'get_name_stakeholder')),
      new \Twig_SimpleFunction('get_comuni',  array($this, 'get_comuni')),
      new \Twig_SimpleFunction('get_settore_impresa',  array($this, 'get_settore_impresa')),
      new \Twig_SimpleFunction('get_elementi_innovazione',  array($this, 'get_elementi_innovazione')),
      new \Twig_SimpleFunction('get_cat_territoriali',  array($this, 'get_cat_territoriali')),
      new \Twig_SimpleFunction('get_name_tax',  array($this, 'get_name_tax')),
      new \Twig_SimpleFunction('get_categorie_progetto',  array($this, 'get_categorie_progetto')),


    );
  }

  public function get_cat_territoriali(){
    $current_tid=  \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    $vid = 'categoria_oppurtunity';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);
      if($current_tid == $term->tid){
        $current_tid_nor = 'true';
      }else{
        $current_tid_nor = 'false';
      }
      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
        'current_tid' => $current_tid_nor
      );
    }
    return $term_data;
  }

  public function get_comuni(){
    $vid = 'comune';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }
  public function get_settore_impresa(){
    $vid = 'settore_impresa';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }

  public function get_elementi_innovazione(){
    $vid = 'innovazione';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }

  public function data_impresa_active(){
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    if(!empty($user->get('field_azienda')->getValue()[0]['target_id'])){

      $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$user->get('field_azienda')->getValue()[0]['target_id']);

      if(!empty($azienda->get('field_email_contatto_riferimento'))){
        $email = $azienda->get('field_email_contatto_riferimento')->getValue()[0]['value'];
      }else{
        $email = '';
      }

      if(!empty($azienda->get('field_sito_web')->getValue()[0]['value'])){
        $sito_web = $azienda->get('field_sito_web')->getValue()[0]['value'];
      }else{
        $sito_web = '';
      }

      if(!empty($azienda->get('field_logo_impresa')->getValue()[0]['target_id'])){
        $image = File::load($azienda->get('field_logo_impresa')->getValue()[0]['target_id']);
        $url = $image->getFileUri();
      }else{
        $url = '';
      }

      if(!empty($azienda->get('field_indirizzo_della_sede')->getValue()[0]['value'])){
        $indirizzo = $azienda->get('field_indirizzo_della_sede')->getValue()[0]['value'];
      }else{
        $indirizzo = '';
      }

      if(!empty($azienda->get('field_numero_civico')->getValue()[0]['value'])){
        $num_civico = $azienda->get('field_numero_civico')->getValue()[0]['value'];
      }else{
        $num_civico = '';
      }

      if(!empty($azienda->get('field_codice_comune')->getValue()[0]['value'])){
        $cod_comune = $azienda->get('field_codice_comune')->getValue()[0]['value'];
      }else{
        $cod_comune = '';
      }

      if(!empty($azienda->get('field_codice_fiscale_impresa')->getValue()[0]['value'])){
        $piva = $azienda->get('field_codice_fiscale_impresa')->getValue()[0]['value'];
      }else{
        $piva = '';
      }

      if(!empty($azienda->get('field_telefono_aienda')->getValue()[0]['value'])){
        $telefono = $azienda->get('field_codice_fiscale_impresa')->getValue()[0]['value'];
      }else{
        $telefono = '';
      }

      if(!empty($azienda->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
        $email = $azienda->get('field_email_contatto_riferimento')->getValue()[0]['value'];
      }else{
        $email = '';
      }

      if(!empty($azienda->get('field_id_impresa')->getValue()[0]['value'])){
        $impresa_vetrina =1;
      }else{
        $impresa_vetrina = 0;
      }

      if(!empty($azienda->get('field_sito_web')->getValue()[0]['value'])){
        $sito_web = $azienda->get('field_sito_web')->getValue()[0]['value'];
      }else{
        $sito_web =  $azienda->get('field_sito_web')->getValue()[0]['value'];
      }

      if(!empty($azienda->get('field_cap_impresa')->getValue()[0]['value'])){
        $cap = $azienda->get('field_cap_impresa')->getValue()[0]['value'];
      }else{
        $cap = '';
      }

      if(!empty($azienda->get('field_elementi_di_innovazione2')->getValue()[0]['target_id'])){
        $tid_elementi = $azienda->get('field_elementi_di_innovazione2')->getValue()[0]['value'];
      }else{
        $tid_elementi = '';
      }

      if(!empty($azienda->get('field_settori_dell_impresa')->getValue()[0]['target_id'])){
        $tid_settore = $azienda->get('field_settori_dell_impresa')->getValue()[0]['value'];
      }else{
        $tid_settore = '';
      }

      if(!empty($azienda->get('field_descrizione_dell_impresa')->getValue()[0]['value'])){
        $descr = $azienda->get('field_descrizione_dell_impresa')->getValue()[0]['value'];
      }else{
        $descr = '';
      }

      if(!empty($azienda->get('field_descrizione_delle_attivita')->getValue()[0]['value'])){
        $att_svolte = $azienda->get('field_descrizione_delle_attivita')->getValue()[0]['value'];
      }else{
        $att_svolte = '';
      }




      $azienda_arr=array(
        'nid' => $user->get('field_azienda')->getValue()[0]['target_id'],
        'alias' => $alias,
        'title' => $azienda->get('title')->getValue()[0]['value'],
        'email' =>$email,
        'sito_web' => $sito_web,
        'logo' => $url,
        'indirizzo_sede' => $indirizzo.', '.$num_civico.', '.$cod_comune,
        'piva' => $piva,
        'impresa_vetrina' =>$impresa_vetrina,
        'email' => $email,
        'telefono' => $telefono,
        'cap' => $cap,
        'tid_elementi' =>$tid_elementi,
        'tid_settori' => $tid_settore,
        'descrizione' => $descr,
        'atti_svolte' => $att_svolte,



      );


    }else{
      $azienda_arr = [];
    }

    return $azienda_arr;

  }

  public function get_all_imprese($home_imprese){
    $start = rand(0, 200);
    $end = $start+10;

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'impresa')
    ->condition('field_id_impresa', '', '<>')
    ->condition('status', 1)
    ->condition('field_stakeholder_', 0);
    if($home_imprese == 1){
      $query->range(0,10);
      $query->addTag('sort_by_random');
    }
    $imprese = $query->execute();


    if(count($imprese)>0){

      foreach ($imprese as $value) {
        $node = Node::load($value);

        $query_1 = \Drupal::entityQuery('node')
        ->condition('type', 'richesta')
        ->notExists('field_servizio')
        ->condition('field_utente', $value)
        ->condition('status', 1);


        $richieste_personalizzate = $query_1->execute();

        if(count($richieste_personalizzate)>0){
          $richiesta_person = 'true';
        }else{
          $richiesta_person = 'false';
        }

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
          $email = $node->get('field_email_contatto_riferimento')->getValue()[0]['value'];
        }else{
          $email = '';
        }

        if(!empty($node->get('field_sito_web')->getValue()[0]['value'])){
          $sito_web = $node->get('field_sito_web')->getValue()[0]['value'];
        }else{
          $sito_web = '';
        }

        if(!empty($node->get('field_natura_giuridica')->getValue()[0]['value'])){
          $natura_giuridica = $node->get('field_natura_giuridica')->getValue()[0]['value'];
        }else{
          $natura_giuridica = '';
        }


        if(!empty($node->get('field_mercati_di_riferimento')->getValue()[0]['target_id'])){
          $term_mercati = Term::load($node->get('field_mercati_di_riferimento')->getValue()[0]['target_id']);
          $mercati = $term_mercati->get('name')->getValue()[0]['value'];
        }else{
          $mercati = '';
        }


        if(!empty($node->get('field_logo_impresa')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_logo_impresa')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else{
          $url = '';
        }

        if(!empty($node->get('field_stato_impresa')->getValue()[0]['value'])){
          $stato_impresa = $node->get('field_stato_impresa')->getValue()[0]['value'];
        }else{
          $stato_impresa = '';
        }


        $aziende_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'email' => $email,
          'sito_web' => $sito_web,
          'richieste_person' => count($richieste_personalizzate),
          'field_natura_giuridica' =>$natura_giuridica,
          'field_mercati_riferimento'=>$mercati,
          //'field_elementi_di_innovazione',$elementi_innovazione,
          'url_image' => $url,
          'stato_impresa' => $stato_impresa,
        );
      }
    }else{
      $aziende_lista = [];
    }

    return $aziende_lista;
  }



  public function create_add_new_agency_user($request){
    //$uid,$nome_azienda,$numero_telefono,$email,$indirizzo,$tid_comune,$cap,$tid_innovazione,$tid_settore,$descrizione,$descrizione_attivita_impresa,$piva_azienda,$nome_legale_rapp,$cognome_legale_rapp,$nome_delegato_rapp,$cognome_delegato_rapp



    $arr = json_decode($request);

    $user = User::load($arr[14]);

    $nome = $user->get('field_nome')->getValue()[0]['value'];
    $cognome = $user->get('field_cognome')->getValue()[0]['value'];
    if($arr[4] != ''){
      $comune = Term::load($arr[4]);
      $nome_comune =$comune->get('name')->getValue()[0]['value'];
    }else{
      $nome_comune = '';
    }

    if($arr[12] == 'true'){
      $nome_legale_rapp = $nome;
      $cognome_legale_rapp = $cognome;
    }else{
      $nome_legale_rapp = '';
      $cognome_legale_rapp = '';
    }

    if($arr[13] == 'true'){
      $nome_delegato_rapp = $nome;
      $cognome_delegato_rapp = $cognome;
    }else{
      $nome_delegato_rapp = '';
      $cognome_delegato_rapp = '';
    }

    $node = Node::create([
      'type' => 'impresa',
      'title' => $arr[0],
      'field_ragione_sociale_' => $arr[0],
      'field_telefono_aienda' =>$arr[1],
      'field_email_contatto_riferimento'=>$arr[2],
      'field_indirizzo_della_sede' =>$arr[3],
      'field_codice_comune' =>$nome_comune,
      'field_cap_impresa' => $arr[6],
      'field_elementi_di_innovazione2' =>$arr[9],
      'field_settori_dell_impresa' =>$arr[8],
      'field_descrizione_dell_impresa' => $arr[10],
      'field_descrizione_delle_attivita' => $arr[11],
      'field_partita_iva' => $arr[5],

      'field_nome_legale_rappresentante' =>$nome_legale_rapp,
      'field_cognome_legale_rappresenta' => $cognome_legale_rapp,

      'field_nome_delegato_all_aggiorna' => $nome_delegato_rapp,
      'field_cognome_delegato_all_aggio' => $cognome_delegato_rapp,

      'field_sito_web' => $arr[15],

    ]);

    $node->save();
    $new_nid = $node->id();



    $user->field_aziende_associate->appendItem($new_nid);

    if(empty($user->get('field_azienda')->getValue()[0]['target_id'])){
      $user->set('field_azienda', $new_nid);
    }
    $user->save();

    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );

    return new Response(render($build));
  }

  public function edit_azienda($request){

    $azienda = json_decode($request);
    $node = Node::load($azienda[12]);

    $node->set('field_ragione_sociale_',$azienda[0]);
    $node->set('field_telefono_aienda',$azienda[1]);
    $node->set('field_email_contatto_riferimento',$azienda[2]);
    $node->set('field_indirizzo_della_sede',$azienda[3]);
    $node->set('field_cap_impresa',$azienda[5]);
    if(!empty($azienda[7])){
      $node->set('field_elementi_di_innovazione2',$azienda[7]);
    }
    if(!empty($azienda[6])){
      $node->set('field_settori_dell_impresa',$azienda[6]);
    }

    $node->set('field_descrizione_dell_impresa',$azienda[8]);
    $node->set('field_descrizione_delle_attivita',$azienda[9]);
    $node->set('field_partita_iva',$azienda[4]);
    $node->set('field_sito_web',$azienda[11]);

    $node->save();

    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );

    return new Response(render($build));
  }

  public function get_all_imprese_gestite(){
    $user = User::load(\Drupal::currentUser()->id());
    if(count($user->get('field_aziende_associate'))>=2){
      for($x = 0; $x < count($user->get('field_aziende_associate')); $x++){
        $azienda = Node::load($user->get('field_aziende_associate')->getValue()[$x]['target_id']);

        $nome_azienda = $azienda->get('title')->getValue()[0]['value'];
        $elenco_aziende[]=array(
          'nid_azienda' => $user->get('field_aziende_associate')->getValue()[$x]['target_id'],
          'nome_azienda' => $nome_azienda,
          'stakholder' => $azienda->get('field_stakeholder_')->getvalue()[0]['value'],
        );
      }
    }else{
      $elenco_aziende = [];
    }
    return $elenco_aziende;
  }

  public function count_progetti(){
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('status', 1)
    ->condition('field_tipologia_progetto', 210)
    ->condition('field_impresa_progetto', $nid);
    $progetti = $query->execute();

    if(count($progetti)>0){
      return 'true';
    }else {
      return 'false';
    }
  }

  public function count_prodotti(){
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('status', 1)
    ->condition('field_tipologia_progetto', 211)
    ->condition('field_impresa_progetto', $nid);
    $prodotti = $query->execute();

    if(count($prodotti)>0){
      return 'true';
    }else {
      return 'false';
    }
  }

  public function count_tecnologie(){
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('status', 1)
    ->condition('field_tipologia_progetto', 212)
    ->condition('field_impresa_progetto', $nid);
    $tecnologie = $query->execute();

    if(count($tecnologie)>0){
      return 'true';
    }else {
      return 'false';
    }
  }

  public function count_news(){
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->condition('status', 1)
    ->condition('field_azienda_associata', $nid);
    $tecnologie = $query->execute();

    if(count($tecnologie)>0){
      return 'true';
    }else {
      return 'false';
    }
  }

  public function count_servizi(){
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'servizio')
    ->condition('status', 1)
    ->condition('field_stakeholder', $nid);
    $tecnologie = $query->execute();

    if(count($tecnologie)>0){
      return 'true';
    }else {
      return 'false';
    }
  }

  public function count_spazi(){
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'spazio_di_lavoro')
    ->condition('status', 1)
    ->condition('field_impresa', $nid);
    $tecnologie = $query->execute();

    if(count($tecnologie)>0){
      return 'true';
    }else {
      return 'false';
    }
  }

  public function get_progetti($nid, $tipo){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('status', 1)
    ->condition('field_tipologia_progetto', $tipo);
    if($nid != 0 ){
      $query->condition('field_impresa_progetto', $nid);
    }

    $progetti = $query->execute();

    if(count($progetti)>0){
      foreach ($progetti as $value) {
        $node = Node::load($value);
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);


    if(!empty($node->get('field_settore_progetto')->getValue())){
      $arr_settori = [];
      $num_settori_progetto  = count($node->get('field_settore_progetto')->getValue());
      for($x =0;$x<$num_settori_progetto; $x++){
        $settore = Term::load($node->get('field_settore_progetto')->getValue()[$x]['target_id']);
        //$nome_settore = $settore->get('name')->getValue()[0]['value'];
        array_push($arr_settori, $node->get('field_settore_progetto')->getValue()[$x]['target_id']);
      }
    }else{
      $arr_settori = [];
    }


    if(!empty($node->get('field_stato_progetto')->getValue()[0]['target_id'])){
      $stato = Term::load($node->get('field_stato_progetto')->getValue()[0]['target_id']);
      $name_stato = $stato->get('name')->getValue()[0]['value'];
    }else{
      $name_stato = '';
    }

    if(!empty($node->get('field_categoria_progetto')->getValue()[0]['target_id'])){
      $categoria = Term::load($node->get('field_categoria_progetto')->getValue()[0]['target_id']);
      $name_categoria = $categoria->get('name')->getValue()[0]['value'];
      $id_categoria_proetto = $node->get('field_categoria_progetto')->getValue()[0]['target_id'];
    }else{
      $name_categoria = '';
      $id_categoria_proetto = '';
    }



    if(!empty($node->get('field_categoria_tecnolo')->getValue())){
      $arr_settori_tecnologia = [];
      $num_settori_tecnologia  = count($node->get('field_categoria_tecnolo')->getValue());
      for($i =0;$i<$num_settori_tecnologia; $i++){
        $settore_tecno = Term::load($node->get('field_categoria_tecnolo')->getValue()[$i]['target_id']);
        //$nome_settore = $settore->get('name')->getValue()[0]['value'];
        array_push($arr_settori_tecnologia, $node->get('field_categoria_tecnolo')->getValue()[$i]['target_id']);
      }
    }else{
      $arr_settori_tecnologia = [];
    }

    if(!empty($node->get('field_valore_economico')->getValue()[0]['value'])){
      $prezzo = $node->get('field_valore_economico')->getValue()[0]['value'];
    }else{
      $prezzo = '';
    }

    if(!empty($node->get('field_data_scadenza_progetto')->getValue()[0]['value'])){
      $data = date('d/m/Y', strtotime($node->get('field_data_scadenza_progetto')->getValue()[0]['value']));
    }else{
      $data = '';
    }

    if(!empty($node->get('field_durata_progetto')->getValue()[0]['value'])){
      $durata_progetto = $node->get('field_durata_progetto')->getValue()[0]['value'];
    }else{
      $durata_progetto = '';
    }

    if(!empty($node->get('field_immagine_catalogo')->getValue()[0]['target_id'])){
      $image = File::load($node->get('field_immagine_catalogo')->getValue()[0]['target_id']);
      $url = $image->getFileUri();
    }else{
      $url = '';
    }

    $elenco_progetti[] = array(
      'nid' => $value,
      'title' => $node->get('title')->getValue()[0]['value'],
      'alias'=>$alias,
      //'id_settore'=>$id_settore,
      //'settore' => $name_settore,
      'settori_progetto' => json_encode($arr_settori),
      'settori_tecnologia' => json_encode($arr_settori_tecnologia),
      'stato' => $name_stato,
      'prezzo' => $prezzo,
      'data_scadenza' => $data,
      'collaborazione_partner' => $node->get('field_collaborazione_partners')->getValue()[0]['value'],
      'progetto_origine' =>$node->get('field_progetto_origine')->getValue()[0]['value'],
      /*'categoria_tecnologia' => $name_categoria_tecnologia,
      'id_categoria_tecnologia' =>$id_categoria_tecnologia,*/
      'durata' => $durata_progetto,
      'categoria' => $name_categoria,
      'id_categoria' =>$id_categoria_proetto,
      'image_bg' =>$url,
      'body' => substr($node->get('body')->getValue()[0]['value'],0,30).'...'
    );
  }

  return $elenco_progetti;

}else{
  return [];
}
}

public function get_num_settore_catalogo($id_azienda, $id_tipo_progetto){

  $vid = 'settore_progetto';
  $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
  foreach ($terms as $term) {
    //echo $term->name;
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('status', 1)
    ->condition('field_tipologia_progetto', $id_tipo_progetto)
    ->condition('field_settore_progetto', $term->tid)
    ->execute();

    if(isset($query)){
      $num_item =  count($query);

      $arr[] = array(
        'tag' => $term->name,
        'weight' => $num_item,
        'url' =>'/taxonomy/term/'.$term->tid.'?nid_impresa=0&tipo_progetto='.$id_tipo_progetto
      );
    }
  }

  $build = array(
    '#type' => 'markup',
    '#markup' => json_encode($arr),
  );

  return new Response(render($build));

}

public function get_num_categoria_tecnologia($id_azienda, $id_tipo_progetto){

  $vid = 'categoria_tecnologia';
  $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
  foreach ($terms as $term) {
    //echo $term->name;
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('status', 1)
    ->condition('field_tipologia_progetto', $id_tipo_progetto)
    ->condition('field_categoria_tecnolo', $term->tid)
    ->execute();

    if(isset($query)){
      $num_item =  count($query);

      $arr[] = array(
        'tag' => $term->name,
        'weight' => $num_item,
        'url' => '/taxonomy/term/'.$term->tid.'?nid_impresa=0&tipo_progetto='.$id_tipo_progetto
      );

    }

  }


  $build = array(
    '#type' => 'markup',
    '#markup' => json_encode($arr),
  );

  return new Response(render($build));

}

public function get_num_mercato_riferimento(){
  $vid = 'mercato_impresa';
  $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
  foreach ($terms as $term) {
    //echo $term->name;
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'impresa')
    ->condition('status', 1)
    ->condition('field_stakeholder_', 0)
    ->condition('field_mercati_di_riferimento', $term->tid)
    ->execute();

    if(isset($query)){
      $num_item =  count($query);
      if($num_item>0){
        $arr[] = array(
          'tag' => $term->name,
          'weight' => $num_item,
          'url' => '/taxonomy/term/'.$term->tid.'?nid_impresa=0&tipo_progetto='.$id_tipo_progetto
        );
      }

    }

  }

  $build = array(
    '#type' => 'markup',
    '#markup' => json_encode($arr),
  );

  return new Response(render($build));
}

public function list_progetti_by_settore(){
  $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');

  if(isset($tid)){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('field_settore_progetto', $tid)
    ->execute();

    if(isset($query)){
      foreach ($query as $value) {
        $node = Node::load($value);

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_immagine_catalogo')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_immagine_catalogo')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else{
          $url = '';
        }
        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'body' => substr($node->get('body')->getValue()[0]['value']).'...',
          'image' => $url
        );
      }
    }
  }
  return $articoli_lista;
}

public function list_progetti_by_settore_prodotti(){
  $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');

  if(isset($tid)){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('field_settore_progetto', $tid)
    ->execute();

    if(isset($query)){
      foreach ($query as $value) {
        $node = Node::load($value);

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_immagine_catalogo')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_immagine_catalogo')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else{
          $url = '';
        }
        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'body' => substr($node->get('body')->getValue()[0]['value']).'...',
          'image' => $url
        );
      }
    }
  }
  return $articoli_lista;
}

public function list_progetti_by_categoria(){
  $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');

  if(isset($tid)){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('field_categoria_progetto', $tid)
    ->execute();

    if(isset($query)){
      foreach ($query as $value) {
        $node = Node::load($value);

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_immagine_catalogo')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_immagine_catalogo')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else{
          $url = '';
        }
        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'body' => substr($node->get('body')->getValue()[0]['value']).'...',
          'image' => $url
        );
      }
    }
  }
  return $articoli_lista;
}

public function list_tecnologie_by_categoria(){
  $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
  if(isset($tid)){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'catalogo_prodotti_progetti_tecno')
    ->condition('field_categoria_tecnolo', $tid)
    ->execute();

    if(isset($query)){
      foreach ($query as $value) {
        $node = Node::load($value);

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_immagine_catalogo')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_immagine_catalogo')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else{
          $url = '';
        }
        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'body' => substr($node->get('body')->getValue()[0]['value']).'...',
          'image' => $url
        );
      }
    }
  }
  return $articoli_lista;
}

public function list_imprese_by_mercato(){
  $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
  if(isset($tid)){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'impresa')
    ->condition('field_mercati_di_riferimento', $tid)
    ->condition('field_stakeholder_',0)
    ->execute();

    if(isset($query)){
      foreach ($query as $value) {
        $node = Node::load($value);

        $query_1 = \Drupal::entityQuery('node')
        ->condition('type', 'richesta')
        ->notExists('field_servizio')
        ->condition('field_utente', $value)
        ->condition('status', 1);

        $richieste_personalizzate = $query_1->execute();

        if(count($richieste_personalizzate)>0){
          $richiesta_person = 'true';
        }else{
          $richiesta_person = 'false';
        }

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
          $email = $node->get('field_email_contatto_riferimento')->getValue()[0]['value'];
        }else{
          $email = '';
        }

        if(!empty($node->get('field_sito_web')->getValue()[0]['value'])){
          $sito_web = $node->get('field_sito_web')->getValue()[0]['value'];
        }else{
          $sito_web = '';
        }

        if(!empty($node->get('field_natura_giuridica')->getValue()[0]['value'])){
          $natura_giuridica = $node->get('field_natura_giuridica')->getValue()[0]['value'];
        }else{
          $natura_giuridica = '';
        }

        /*if(!empty($node->get('field_settore_principale_dell_im')->getValue()[0]['value'])){
        $settore = $node->get('field_settore_principale_dell_im')->getValue()[0]['value'];
      }else{
      $settore = '';
    }*/
    if(!empty($node->get('field_mercati_di_riferimento')->getValue()[0]['target_id'])){
      $term_mercati = Term::load($node->get('field_mercati_di_riferimento')->getValue()[0]['target_id']);
      $mercati = $term_mercati->get('name')->getValue()[0]['value'];
    }else{
      $mercati = '';
    }


    if(!empty($node->get('field_logo_impresa')->getValue()[0]['target_id'])){
      $image = File::load($node->get('field_logo_impresa')->getValue()[0]['target_id']);
      $url = $image->getFileUri();
    }else{
      $url = '';
    }


    $aziende_lista[]=array(
      'nid' => $value,
      'alias' => $alias,
      'title' => $node->get('title')->getValue()[0]['value'],
      'email' => $email,
      'sito_web' => $sito_web,
      'richieste_person' => count($richieste_personalizzate),
      'field_natura_giuridica' =>$natura_giuridica,
      //'field_settore_principale_dell_im' =>$settore,
      'field_mercati_riferimento'=>$mercati,
      'url_image' => $url
    );
  }
}else{
  $aziende_lista = [];
}
}
return $aziende_lista;
}

public function get_name_stakeholder($id_stakeholder){
  if($id_stakeholder != 0){
    $node = Node::load($id_stakeholder);
    return $node->get('title')->getValue()[0]['value'];
  }
}
public function get_name_tax($id_term){
  $term = Term::load($id_term);
  return $term->get('name')->getValue()[0]['value'];
}

public function get_categorie_progetto($tipologia_progetto){
  if($tipologia_progetto == 211 or $tipologia_progetto ==210){
    $vid = 'settori_prodotti';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }

  }else{
    $vid = 'categoria_tecnologia';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
  }
  return $term_data;

  /*
  else if($tipologia_progetto == 210){
    $vid = 'settore_progetto';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
  }
  */
}
}
