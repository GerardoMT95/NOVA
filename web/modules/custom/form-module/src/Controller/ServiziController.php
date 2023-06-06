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

use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Response;


class ServiziController extends \Twig_Extension {

  public function getName() {
    return 'ex81.ServiziController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('get_macroaree_1', array($this, 'get_macroaree_1')),
      new \Twig_SimpleFunction('get_macroaree_filter', array($this, 'get_macroaree_filter')),
      new \Twig_SimpleFunction('get_service', array($this, 'get_service')),
      new \Twig_SimpleFunction('get_service_by_stakholder', array($this, 'get_service_by_stakholder')),

      new \Twig_SimpleFunction('get_packet_service', array($this, 'get_packet_service')),


      new \Twig_SimpleFunction('area_competenza', array($this, 'area_competenza')),
      new \Twig_SimpleFunction('tipologia_servizio', array($this, 'tipologia_servizio')),
      new \Twig_SimpleFunction('tipologia_erogazione', array($this, 'tipologia_erogazione')),
      new \Twig_SimpleFunction('get_stato', array($this, 'get_stato')),
      new \Twig_SimpleFunction('stato_richiesta', array($this, 'stato_richiesta')),
      new \Twig_SimpleFunction('tax_richiesta_collaborazione', array($this, 'tax_richiesta_collaborazione')),
      new \Twig_SimpleFunction('tax_richiesta_pubblicazione', array($this, 'tax_richiesta_pubblicazione')),

      new \Twig_SimpleFunction('json_decode_arr', array($this, 'json_decode_arr')),
    );
  }

  public function get_macroaree_1(){
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $vid = 'macroaree';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

    foreach ($terms as $term) {
      //if($term->hasTranslation($language)){
      //  $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $language);

        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$term->tid);
        $macroarea = Term::load($term->tid);
        if(!empty($macroarea->get('field_testo_cerchio_homepage')->getValue()[0]['value'])){
          $term_data[] = array(
            'id' => $term->tid,
            'name' => $term->name,
            'titolo_cerchio' => $macroarea->get('field_testo_cerchio_homepage')->getValue()[0]['value'],
            'bottone' => $macroarea->get('field_testo_bottone_homepage')->getValue()[0]['value'],
            'alias' =>$alias
          );
        }
      //}
    }
    return $term_data;
  }

  public function get_stato(){
    $vid = 'stato_richiesta';
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

  public function area_competenza(){
    $vid = 'a';
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

  public function get_macroaree_filter(){
    $vid = 'macroaree';
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

  public function tipologia_servizio(){
    $vid = 'tipo_di_servizio';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/portalelavoro_new/web/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }

  public function tipologia_erogazione(){
    $vid = 'tipologie_erogazione';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/portalelavoro_new/web/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }

  public function stato_richiesta(){
    $vid = 'stato_richiesta';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/portalelavoro_new/web/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }

  public function tax_richiesta_collaborazione(){
    $vid = 'richiesta_personalizzata_motivaz';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/portalelavoro_new/web/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }

  public function tax_richiesta_pubblicazione(){
    $vid = 'richiesta_pubblicabile_sul_sito';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/portalelavoro_new/web/node/'.$term->tid);
      $macroarea = Term::load($term->tid);

      $term_data[] = array(
        'tid' => $term->tid,
        'name' => $term->name,
      );
    }
    return $term_data;
  }


  public function get_service(){

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'servizio')
    //->notExists('field_pacchetto')
    ->condition('status', 1);
    //->orConditionGroup();
    if(isset($_GET['area_competenza']) && $_GET['area_competenza'] != 'null') $query->condition('field_area_di_competenza', $_GET['area_competenza']);

    if(isset($_GET['tipologia_servizio']) && $_GET['tipologia_servizio'] != 'null') $query->condition('field_tipo_servizio', $_GET['tipologia_servizio']);
    if(isset($_GET['tipologia_erogazione']) && $_GET['tipologia_erogazione'] != 'null') $query->condition('field_tipologia_di_erogazione', $_GET['tipologia_erogazione']);
    if(isset($_GET['fornitore']) && $_GET['fornitore'] != 'null') $query->condition('field_stakeholder', $_GET['fornitore']);
    $servizio = $query->execute();
    if(isset($_GET['macroarea']) && $_GET['macroarea'] != 'null') $query->condition('field_macroarea', $_GET['macroarea']);
    $servizio = $query->execute();

    if(count($servizio)>0){

      foreach ($servizio as $value) {

        $node = Node::load($value);
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
        if(!empty($node->get('field_stakeholder')->getValue() )){
          $azienda = Node::load($node->get('field_stakeholder')->getValue()[0]['target_id']);

          if(!empty($node->get('field_tipo_servizio')->getValue()[0]['target_id'])){
            $tipo = Term::load($node->get('field_tipo_servizio')->getValue()[0]['target_id']);
            $nome_tipo = $tipo->get('name')->getValue()[0]['value'];
          }else{
            $nome_tipo = '';
          }

          if(!empty($node->get('field_area_di_competenza')->getValue()[0]['target_id'])){
            $area = Term::load($node->get('field_area_di_competenza')->getValue()[0]['target_id']);
            $nome_area = $area->get('name')->getValue()[0]['value'];
          }else{
            $nome_area = '';
          }
          if(!empty($node->get('field_tipologia_di_erogazione')->getValue()[0]['target_id'])){
            $erogazione = Term::load($node->get('field_area_di_competenza')->getValue()[0]['target_id']);
            $nome_erogazione = $erogazione->get('name')->getValue()[0]['value'];
          }else{
            $nome_erogazione = '';
          }

          if(!empty($node->get('field_pacchetto')->getValue())){
            $pacchetto = 1;
          }else{
            $pacchetto = 0;
          }

          if(!empty($azienda->get('title')->getValue()[0]['value'])){
            $nome_azienda = $azienda->get('title')->getValue()[0]['value'];
          }else{
            $nome_azienda  = '';
          }

          $servizio_lista[]=array(
            'nid' => $value,
            'alias' => $alias,
            'title' => $node->get('title')->getValue()[0]['value'],
            'nome_azienda' =>$nome_azienda,
            'tipo_servizio' => $nome_tipo,
            'area' => $nome_area,
            'erogazione' => $nome_erogazione,
            'pacchetto' => $pacchetto

          );
        }
      }

    }else{
      $servizio_lista = [];
    }

    if(isset($_GET['macroarea']) && $_GET['macroarea'] != 'null' or
    isset($_GET['fornitore']) && $_GET['fornitore'] != 'null' or
    isset($_GET['tipologia_erogazione']) && $_GET['tipologia_erogazione'] != 'null' or
    isset($_GET['tipologia_servizio']) && $_GET['tipologia_servizio'] != 'null' or
    isset($_GET['area_competenza']) && $_GET['area_competenza'] != 'null'or
    isset($_GET['json'])) {
      if($_GET['fornitore'] == 'null' && $_GET['macroarea'] == 'null' && $_GET['tipologia_erogazione']  == 'null' && $_GET['tipologia_servizio'] == 'null' && $_GET['area_competenza'] == 'null'){
        $servizio_lista = [];
      }
      $build = array(
        '#type' => 'markup',
        '#markup' => json_encode($servizio_lista),
      );

      return new Response(render($build));
    }else{
      return json_encode($servizio_lista);
    }

    //return json_encode($servizio_lista);
  }

  public function get_packet_service(){

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'servizio')
    ->condition('field_pacchetto', '', '<>')
    ->condition('status', 1);
    //->orConditionGroup();
    if(isset($_GET['area_competenza']) && $_GET['area_competenza'] != 'null') $query->condition('field_area_di_competenza', $_GET['area_competenza']);

    if(isset($_GET['tipologia_servizio']) && $_GET['tipologia_servizio'] != 'null') $query->condition('field_tipo_servizio', $_GET['tipologia_servizio']);
    if(isset($_GET['tipologia_erogazione']) && $_GET['tipologia_erogazione'] != 'null') $query->condition('field_tipologia_di_erogazione', $_GET['tipologia_erogazione']);
    if(isset($_GET['fornitore']) && $_GET['fornitore'] != 'null') $query->condition('field_stakeholder', $_GET['fornitore']);
    $servizio = $query->execute();
    if(isset($_GET['macroarea']) && $_GET['macroarea'] != 'null') $query->condition('field_macroarea', $_GET['macroarea']);
    $servizio = $query->execute();

    if(count($servizio)>0){

      foreach ($servizio as $value) {

        $node = Node::load($value);
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
        $azienda = Node::load($node->get('field_stakeholder')->getValue()[0]['target_id']);

        if(!empty($node->get('field_tipo_servizio')->getValue()[0]['target_id'])){
          $tipo = Term::load($node->get('field_tipo_servizio')->getValue()[0]['target_id']);
          $nome_tipo = $tipo->get('name')->getValue()[0]['value'];
        }else{
          $nome_tipo = '';
        }

        if(!empty($node->get('field_area_di_competenza')->getValue()[0]['target_id'])){
          $area = Term::load($node->get('field_area_di_competenza')->getValue()[0]['target_id']);
          $nome_area = $area->get('name')->getValue()[0]['value'];
        }else{
          $nome_area = '';
        }
        if(!empty($node->get('field_tipologia_di_erogazione')->getValue()[0]['target_id'])){
          $erogazione = Term::load($node->get('field_area_di_competenza')->getValue()[0]['target_id']);
          $nome_erogazione = $erogazione->get('name')->getValue()[0]['value'];
        }else{
          $nome_erogazione = '';
        }

        if(count($node->get('field_pacchetto')->getValue())>0){


          for($x=0;$x<count($node->get('field_pacchetto')->getValue());$x++){
            $pacchetto = Node::load($node->get('field_pacchetto')->getValue()[$x]['target_id']);
            $list_pacchetti[]=array(
              'nid' => $pacchetto->get('nid')->getValue()[0]['value'],
              'title' => substr($pacchetto->get('title')->getValue()[0]['value'],0,20).'...',
            );
          }
        }

        $servizio_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'nome_azienda' =>$azienda->get('title')->getValue()[0]['value'],
          'tipo_servizio' => $nome_tipo,
          'area' => $nome_area,
          'erogazione' => $nome_erogazione,
          'pacchetto' => json_encode($list_pacchetti)

        );
      }

    }else{
      $servizio_lista = [];
    }

    if(isset($_GET['macroarea']) && $_GET['macroarea'] != 'null' or
    isset($_GET['fornitore']) && $_GET['fornitore'] != 'null' or
    isset($_GET['tipologia_erogazione']) && $_GET['tipologia_erogazione'] != 'null' or
    isset($_GET['tipologia_servizio']) && $_GET['tipologia_servizio'] != 'null' or
    isset($_GET['area_competenza']) && $_GET['area_competenza'] != 'null') {

      $build = array(
        '#type' => 'markup',
        '#markup' => json_encode($servizio_lista),
      );

      return new Response(render($build));
    }else{
      return json_encode($servizio_lista);
    }

    //return json_encode($servizio_lista);
  }

  public function get_service_by_stakholder(){
    $user = User::load(\Drupal::currentUser()->id());
    //$azienda_servizio = $user->get('field_azienda')->getValue()[0]['target_id'];
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'servizio')
    ->condition('field_stakeholder', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->condition('status', 1);
    $servizi = $query->execute();

    if(count($servizi)>0){
      foreach($servizi as $value){
        $node = Node::load($value);
        $servizio_lista[]=array(
          'nid' => $value,
          'title' => $node->get('title')->getValue()[0]['value'],
        );
      }

    }
    return $servizio_lista;
  }

  public function confronta_servizi($nid_service){
    $servizi = json_decode($nid_service);


    //foreach ($servizi as $value) {
    for($x=0;$x<count($servizi);$x++){

      $node = Node::load($servizi[$x]);

      $azienda = Node::load($node->get('field_stakeholder')->getValue()[0]['target_id']);

      if(!empty($node->get('field_tipo_servizio')->getValue()[0]['target_id'])){
        $tipo = Term::load($node->get('field_tipo_servizio')->getValue()[0]['target_id']);
        $nome_tipo = $tipo->get('name')->getValue()[0]['value'];
      }else{
        $nome_tipo = '';
      }

      if(!empty($node->get('field_area_di_competenza')->getValue()[0]['target_id'])){
        $area = Term::load($node->get('field_area_di_competenza')->getValue()[0]['target_id']);
        $nome_area = $area->get('name')->getValue()[0]['value'];
      }else{
        $nome_area = '';
      }
      if(!empty($node->get('field_tipologia_di_erogazione')->getValue()[0]['target_id'])){
        $erogazione = Term::load($node->get('field_tipologia_di_erogazione')->getValue()[0]['target_id']);
        $nome_erogazione = $erogazione->get('name')->getValue()[0]['value'];
      }else{
        $nome_erogazione = '';
      }

      $servizi_arr[]=array(
        'nid' => $servizi[$x],
        'title' => $node->get('title')->getValue()[0]['value'],
        'body' => $node->get('body')->getValue()[0]['value'],
        'nome_azienda' =>$azienda->get('title')->getValue()[0]['value'],
        'tipo_servizio' => $nome_tipo,
        'area' => $nome_area,
        'erogazione' => $nome_erogazione,

      );
    }


    $url_page = '/node/11?confronta_servizi='.json_encode($servizi_arr);

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());

  }


  public function json_decode_arr($array){
    return json_decode($array);
  }
}
