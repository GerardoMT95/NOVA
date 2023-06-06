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

class SpaziController extends \Twig_Extension {

  public function getName() {
    return 'ex81.SpaziController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('get_spazi', array($this, 'get_spazi')),
      new \Twig_SimpleFunction('get_spazi_by_stakholder', array($this, 'get_spazi_by_stakholder')),
      new \Twig_SimpleFunction('get_servizi_accessori', array($this, 'get_servizi_accessori')),
      new \Twig_SimpleFunction('get_tipologia_spazio', array($this, 'get_tipologia_spazio')),
      new \Twig_SimpleFunction('get_destinatari', array($this, 'get_destinatari')),
      new \Twig_SimpleFunction('get_condizione_utilizzo', array($this, 'get_condizione_utilizzo')),
    );
  }
  public function get_servizi_accessori(){
    $vid = 'servizi_accessori';
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

  public function get_tipologia_spazio(){
    $vid = 'tipologia_spazio';
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

  public function get_destinatari(){
    $vid = 'destinatari';
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

  public function get_condizione_utilizzo(){
    $vid = 'condizione_di_utilizzo';
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

  public function get_spazi(){
    $arr_servizi = [];
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'spazio_di_lavoro')
    ->condition('status', 1);
    if(isset($_GET['servizio_acc']) && $_GET['servizio_acc'] != 'null') $query->condition('field_servizi_accessori', $_GET['servizio_acc']);
    if(isset($_GET['fornitore']) && $_GET['fornitore'] != 'null') $query->condition('field_impresa', $_GET['fornitore']);
    if(isset($_GET['tip_spazio']) && $_GET['tip_spazio'] != 'null') $query->condition('field_tipologia', $_GET['tip_spazio']);
    if(isset($_GET['destinatari']) && $_GET['destinatari'] != 'null') $query->condition('field_destinatari', $_GET['destinatari']);
    if(isset($_GET['condizione_utilizzo']) && $_GET['condizione_utilizzo'] != 'null') $query->condition('field_condizione_di_utilizzo', $_GET['condizione_utilizzo']);

    $spazi = $query->execute();

    if(count($spazi)>0){

      foreach ($spazi as $value) {

        $arr_servizi = [];
        $node = Node::load($value);
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        $azienda = Node::load($node->get('field_impresa')->getValue()[0]['target_id']);

        if(!empty($node->get('field_tipologia')->getValue()[0]['target_id'])){
          $tipo = Term::load($node->get('field_tipologia')->getValue()[0]['target_id']);
          $nome_tipo = $tipo->get('name')->getValue()[0]['value'];
        }else{
          $nome_tipo = '';
        }

        if(!empty($node->get('field_pubblico_privato')->getValue()[0]['target_id'])){
          $pubblico_privato = Term::load($node->get('field_pubblico_privato')->getValue()[0]['target_id']);
          $nome_pubblico_privato = $pubblico_privato->get('name')->getValue()[0]['value'];
        }else{
          $nome_pubblico_privato = '';
        }

        if(count($node->get('field_servizi_accessori')->getValue())>0){
          for($x =0;$x<count($node->get('field_servizi_accessori')->getValue()); $x++){
            $servizio = Term::load($node->get('field_servizi_accessori')->getValue()[$x]['target_id']);
            $nome_servizo_accessori = $servizio->get('name')->getValue()[0]['value'];
            array_push($arr_servizi, $nome_servizo_accessori);
          }
        }

        if(!empty($node->get('field_indirizzo')->getValue()[0]['value'])){
          $indirizzo = $node->get('field_indirizzo')->getValue()[0]['value'];
        }else{
          $indirizzo = '';
        }

        if(!empty($node->get('field_indirizzo_e_mail_di_contat')->getValue()[0]['value'])){
          $email = $node->get('field_indirizzo_e_mail_di_contat')->getValue()[0]['value'];
        }else{
          $email = '';
        }

        $spazi_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'nome_azienda' =>$azienda->get('title')->getValue()[0]['value'],
          'tipo_spazio' => $nome_tipo,
          'indirizzo' => $indirizzo,
          'email' => $email,
          'pubblico_privato' =>$nome_pubblico_privato,
          'servizi_accessori' => json_encode($arr_servizi)
        );
      }
    }else{
      $spazi_lista = [];
    }
    //return $spazi_lista;
    if(isset($_GET['fornitore']) && $_GET['fornitore'] != 'null' or
    isset($_GET['servizio_acc']) && $_GET['servizio_acc'] != 'null' or
    isset($_GET['tip_spazio']) && $_GET['tip_spazio'] != 'null' or
    isset($_GET['destinatari']) && $_GET['destinatari'] != 'null' or
    isset($_GET['condizione_utilizzo']) && $_GET['condizione_utilizzo'] != 'null'  or
    isset($_GET['json_spazi'])) {

      if($_GET['fornitore'] == 'null' && $_GET['servizio_acc'] == 'null' && $_GET['tip_spazio']  == 'null' && $_GET['destinatari'] == 'null' && $_GET['condizione_utilizzo'] == 'null'){
        $spazi_lista = [];
      }
      $build = array(
        '#type' => 'markup',
        '#markup' => json_encode($spazi_lista),
      );
      return new Response(render($build));
    }else{
      return json_encode($spazi_lista);
    }
  }

  public function get_spazi_by_stakholder(){
    $user = User::load(\Drupal::currentUser()->id());
    //$azienda_servizio = $user->get('field_azienda')->getValue()[0]['target_id'];
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'spazio_di_lavoro')
    ->condition('field_impresa', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->condition('status', 1);
    $spazi = $query->execute();

    if(count($spazi)>0){
      foreach($spazi as $value){
        $node = Node::load($value);
        $spazi_lista[]=array(
          'nid' => $value,
          'title' => $node->get('title')->getValue()[0]['value'],
        );
      }

    }
    return $spazi_lista;
  }

  public function confronta_spazi($nid_spazi){

    $spazi = json_decode($nid_spazi);


    for($x =0;$x<count($spazi);$x++){


      $node = Node::load($spazi[$x]);
      $azienda = Node::load($node->get('field_impresa')->getValue()[0]['target_id']);


      if(!empty($node->get('field_tipologia')->getValue()[0]['target_id'])){
        $tipo = Term::load($node->get('field_tipologia')->getValue()[0]['target_id']);
        $nome_tipo = $tipo->get('name')->getValue()[0]['value'];
      }else{
        $nome_tipo = '';
      }

      if(!empty($node->get('field_costo')->getValue()[0]['value'])){
        $costo = $node->get('field_costo')->getValue()[0]['value'];
      }else{
        $costo = '';
      }

      $spazi_arr[]=array(
        'nid' =>$spazi[$x],
        'title' => $node->get('title')->getValue()[0]['value'],
        'nome_azienda' =>$azienda->get('title')->getValue()[0]['value'],
        'tipo_spazio' => $nome_tipo,
        'indirizzo' => $node->get('field_indirizzo')->getValue()[0]['value'],
        'costo' => $costo,
      );
    }

    $url_page = '/node/16?confronta_spazi='.json_encode($spazi_arr);

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());



  }





}
