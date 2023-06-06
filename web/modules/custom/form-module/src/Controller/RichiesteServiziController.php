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


class RichiesteServiziController extends \Twig_Extension {

  public function getName() {
    return 'ex81.RichiesteServiziController';
  }

  public function getFunctions() {
    return array(

      //new \Twig_SimpleFunction('json_decode_arr', array($this, 'json_decode_arr')),

      new \Twig_SimpleFunction('get_richieste_servizi_cliente', array($this, 'get_richieste_servizi_cliente')),
      new \Twig_SimpleFunction('get_elenco_richieste_gestite', array($this, 'get_elenco_richieste_gestite')),
      new \Twig_SimpleFunction('get_elenco_richieste_gestite_personalizzate_stakeholder', array($this, 'get_elenco_richieste_gestite_personalizzate_stakeholder')),

      new \Twig_SimpleFunction('get_elenco_richieste_personalizzate', array($this, 'get_elenco_richieste_personalizzate')),
      new \Twig_SimpleFunction('get_elenco_richieste_personalizzate_all', array($this, 'get_elenco_richieste_personalizzate_all')),

      new \Twig_SimpleFunction('get_motivazione_conclusione', array($this, 'get_motivazione_conclusione')),
      new \Twig_SimpleFunction('richieste_personalizzate_public', array($this, 'richieste_personalizzate_public')),



    );
  }


  public function get_motivazione_conclusione(){
    $vid = 'motivazione_conclusione';
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
  public function get_richieste_servizi_cliente(){

    $user = User::load(\Drupal::currentUser()->id());

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_servizio', '', '<>')
    ->condition('field_utente', $user->get('field_azienda')->getvalue()[0]['target_id'])
    ->condition('status', 1);
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){
        $richiesta = Node::load($value);
        if(!empty($richiesta->get('field_servizio')->getValue()[0]['target_id'])){
          if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
            $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
            $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
          }else{
            $stato_richiesta = '';
          }


          $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);


          $servizio = Node::load($richiesta->get('field_servizio')->getValue()[0]['target_id']);

          $nome_servizio = $servizio->get('title')->getValue()[0]['value'];

          if(!empty($servizio->get('field_macroarea')->getValue()[0]['target_id'])){
            $tid_macroarea = Term::load($servizio->get('field_macroarea')->getValue()[0]['target_id']);
            $nome_macroarea = $tid_macroarea->get('name')->getValue()[0]['value'];
          }else{
            $id_macroarea ='';
            $nome_macroarea = '';
          }



          if(!empty($servizio->get('field_area_di_competenza')->getValue()[0]['target_id'])){
            $tid_area_competenza = Term::load($servizio->get('field_area_di_competenza')->getValue()[0]['target_id']);
            $nome_area_competenza = $tid_area_competenza->get('name')->getValue()[0]['value'];
          }else{
            $tid_area_competenza ='';
            $nome_area_competenza = '';
          }

          $stakeholder = Node::load($servizio->get('field_stakeholder')->getValue()[0]['target_id']);
          $nome_stakeholder = $stakeholder->get('field_ragione_sociale_')->getValue()[0]['value'];

          $elenco_richieste[]=array(
            'id_richiesta' => $value,
            'nid_servizio' => $richiesta->get('field_servizio')->getValue()[0]['target_id'],
            'title_servizio' => $nome_servizio,
            'nid_stakeholder' =>$servizio->get('field_stakeholder')->getValue()[0]['target_id'],
            'nome_stakeholder' => $nome_stakeholder,
            'tid_macroarea' =>$tid_macroarea,
            'nome_macroarea' =>$nome_macroarea,
            'tid_area_comptenza' => $tid_area_competenza,
            'area_competenza' => $nome_area_competenza,
            'data_invio' => $data_invio_richiesta,
            'stato_richiesta' => $nome_stato,
            'num_commenti' =>$richiesta->get('field_commento_richiesta')->getValue()[0]['comment_count']
          );
        }
      }
    }else{
      $elenco_richieste = [];
    }

    return $elenco_richieste;
  }

  public function get_elenco_richieste_gestite(){

    $user = User::load(\Drupal::currentUser()->id());
    //$azienda_servizio = $user->get('field_azienda')->getValue()[0]['target_id'];
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    //->condition('field_servizio', '', '<>')
    ->condition('field_azienda_proprietaria', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->condition('status', 1);
    if(isset($_GET['stato_richiesta']) && $_GET['stato_richiesta'] != 'null') $query->condition('field_stato', $_GET['stato_richiesta']);
    if(isset($_GET['imprese']) && $_GET['imprese'] != 'null') $query->condition('field_utente', $_GET['imprese']);
    if(isset($_GET['servizio_stakeholder']) && $_GET['servizio_stakeholder'] != 'null'){
      $query->condition('field_servizio', $_GET['servizio_stakeholder']);
    }else{
      $query->condition('field_servizio', '', '<>');
    }
    $servizio = $query->execute();

    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){

        $richiesta = Node::load($value);

        if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
          $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
          $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
          $id_stato = $richiesta->get('field_stato')->getValue()[0]['target_id'];
        }else{
          $stato_richiesta = '';
          $id_stato='';
        }


        $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);
        if(!empty($richiesta->get('field_servizio')->getValue()[0]['target_id'])){

          $servizio = Node::load($richiesta->get('field_servizio')->getValue()[0]['target_id']);
          $nome_servizio = $servizio->get('title')->getValue()[0]['value'];

          if(!empty($servizio->get('field_macroarea')->getValue()[0]['target_id'])){
            $tid_macroarea = Term::load($servizio->get('field_macroarea')->getValue()[0]['target_id']);
            $nome_macroarea = $tid_macroarea->get('name')->getValue()[0]['value'];
          }else{
            $id_macroarea ='';
            $nome_macroarea = '';
          }

          if(!empty($servizio->get('field_area_di_competenza')->getValue()[0]['target_id'])){
            $tid_area_competenza = Term::load($servizio->get('field_area_di_competenza')->getValue()[0]['target_id']);
            $nome_area_competenza = $tid_area_competenza->get('name')->getValue()[0]['value'];
          }else{
            $tid_area_competenza ='';
            $nome_area_competenza = '';
          }

          if(!empty($richiesta->get('field_utente')->getValue()[0]['target_id'])){
            $id_azienda_richiedente = Node::load($richiesta->get('field_utente')->getValue()[0]['target_id']);
            $nome_azienda_richiedente = $id_azienda_richiedente->get('title')->getValue()[0]['value'];
          }else{
            $id_azienda_richiedente ='';
            $nome_azienda_richiedente = '';
          }



          $elenco_richieste[]=array(
            'id_richiesta' => $value,
            'nid_servizio' => $richiesta->get('field_servizio')->getValue()[0]['target_id'],
            'title_servizio' => $nome_servizio,
            'tid_macroarea' =>$tid_macroarea,
            'nome_macroarea' =>$nome_macroarea,
            'tid_area_comptenza' => $tid_area_competenza,
            'area_competenza' => $nome_area_competenza,
            'data_invio' => $data_invio_richiesta,
            'stato_richiesta' => $stato_richiesta,
            'id_stato' => $id_stato,
            'id_azienda_richiedente' =>$richiesta->get('field_utente')->getValue()[0]['target_id'],
            'nome_azienda' => $nome_azienda_richiedente,
            'numero_commenti' => count($richiesta->get('field_commento_richiesta'))-1
          );
        }
      }
    }else{

      $elenco_richieste = [];
    }
    if(isset($_GET['stato_richiesta']) && $_GET['stato_richiesta'] != 'null' or
    isset($_GET['servizio_stakeholder']) && $_GET['servizio_stakeholder'] != 'null' or
    isset($_GET['imprese']) && $_GET['imprese'] != 'null'  ) {
      $build = array(
        '#type' => 'markup',
        '#markup' => json_encode($elenco_richieste),
      );

      return new Response(render($build));
    }else{
      return $elenco_richieste;
    }
  }

  public function richieste_personalizzate_public($nid_azienda){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_utente', $nid_azienda)
    ->notExists('field_servizio')
    ->condition('status', 1);
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){
        $richiesta = Node::load($value);

        if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
          $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
          $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
        }else{
          $stato_richiesta = '';
        }

        if(!empty($richiesta->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id'])){
          $motivazione_st = Term::load($richiesta->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id']);
          $nome_motivazione_std = $motivazione_st->get('name')->getValue()[0]['value'];
        }else{
          $nome_motivazione_std = '';
        }

        $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);
        $motivazione = $richiesta->get('field_motivazione')->getValue()[0]['value'];


        $elenco_richieste[]=array(
          'id_richiesta' => $value,
          'stato_richiesta' => $nome_stato,
          'motivazione_std' =>$nome_motivazione_std,
          'motivazione'=>$motivazione,
          'data' =>$data_invio_richiesta
        );

      }
    }else{
      $elenco_richieste=[];
    }
    return $elenco_richieste;
  }

  public function get_elenco_richieste_personalizzate(){
    $user = User::load(\Drupal::currentUser()->id());

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_utente', $user->get('field_azienda')->getvalue()[0]['target_id'])
    ->notExists('field_servizio');
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){
        $richiesta = Node::load($value);

        if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
          $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
          $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
        }else{
          $stato_richiesta = '';
        }

        $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);
        $motivazione = $richiesta->get('field_motivazione')->getValue()[0]['value'];


        $elenco_richieste[]=array(
          'id_richiesta' => $value,
          'data_invio' => $data_invio_richiesta,
          'stato_richiesta' => $nome_stato,
          'motivazione'=>$motivazione
        );

      }
    }else{
      $elenco_richieste = [];
    }

    return $elenco_richieste;
  }

  public function get_elenco_richieste_personalizzate_all(){

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->notExists('field_servizio');
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){
        $richiesta = Node::load($value);

        if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
          $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
          $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
        }else{
          $stato_richiesta = '';
        }

        $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);
        $motivazione = $richiesta->get('field_motivazione')->getValue()[0]['value'];
        $azienda_richiedente = Node::load($richiesta->get('field_utente')->getValue()[0]['target_id']);
        if(!empty($azienda_richiedente->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
          $email_azienda = $azienda_richiedente->get('field_email_contatto_riferimento')->getValue()[0]['value'];
        }else{
          $email_azienda = '';
        }

        if(!empty($richiesta->get('field_pubblicazione_personalizza')->getValue()[0]['target_id'])){
          $pubb_richiesta = Term::load($richiesta->get('field_pubblicazione_personalizza')->getValue()[0]['target_id']);
          $nome_pubb_richiesta = $pubb_richiesta->get('name')->getValue()[0]['value'];
        }else{
          $nome_pubb_richiesta = '';
        }

        if(!empty($richiesta->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id'])){
          $motivazione_tax = Term::load($richiesta->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id']);
          $nome_motivazione_tax = $motivazione_tax->get('name')->getValue()[0]['value'];
        }else{
          $nome_motivazione_tax = '';
        }


        $elenco_richieste[]=array(
          'id_richiesta' => $value,
          'data_invio' => $data_invio_richiesta,
          'stato_richiesta' => $nome_stato,
          'motivazione'=>$motivazione,
          'nome_azienda' => $azienda_richiedente->get('title')->getValue()[0]['value'],
          'email_azienda' => $email_azienda,
          'pubblicazione_richiesta' => $nome_pubb_richiesta,
          'motivazione_richiesta_tax' => $nome_motivazione_tax,
          'status' => $richiesta->get('status')->getValue()[0]['value']
        );

      }
    }else{
      $elenco_richieste = [];
    }

    return $elenco_richieste;
  }
  public function get_elenco_richieste_gestite_personalizzate_stakeholder(){

    $user = User::load(\Drupal::currentUser()->id());
    //$azienda_servizio = $user->get('field_azienda')->getValue()[0]['target_id'];
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_azienda_proprietaria', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->notExists('field_servizio');
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){

        $richiesta = Node::load($value);

        if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
          $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
          $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
          $id_stato = $richiesta->get('field_stato')->getValue()[0]['target_id'];
        }else{
          $stato_richiesta = '';
          $id_stato='';
        }

        $user_richiesta = Node::load($richiesta->get('field_utente')->getValue()[0]['target_id']);
        if(!empty($user_richiesta->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
          $email_azienda = $user_richiesta->get('field_email_contatto_riferimento')->getValue()[0]['value'];
        }else{
          $email_azienda= '';
        }

        $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);

        $elenco_richieste[]=array(
          'id_richiesta' => $value,
          'email_azienda' => $email_azienda,
          'data_invio' => $data_invio_richiesta,
          'stato_richiesta' => $stato_richiesta,
          'nome_stato' => $nome_stato,
          'id_stato' => $id_stato,
          'numero_commenti' => count($richiesta->get('field_commento_richiesta'))-1
        );

      }
    }else{
      $elenco_richieste = [];
    }

    return $elenco_richieste;
  }

  public function update_stato_richiesta($id_richiesta, $id_stato){

    $richesta = Node::load($id_richiesta);
    $richesta->set('field_stato', $id_stato);
    $richesta->save();

    $azienda_cliente = Node::load($richesta->get('field_utente')->getValue()[0]['target_id']);
    if(!empty($azienda_cliente->get('field_email_contatto_riferimento')->getValue()[0])){
      $email_azienda = $azienda_cliente->get('field_email_contatto_riferimento')->getValue()[0]['value'];
    }else{
      $email_azienda = '';
    }

    $stato = Term::load($id_stato);
    $nome_stato = $stato->get('name')->getValue()[0]['value'];

    if($email_azienda != ''){
      NotificationController::cambio_stato_richiesta($email_azienda, $nome_stato, $id_richiesta);
    }

    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    return new Response(render($build));
  }

  public function nuova_richiesta($testo_nuova_richeista,$pubblicabile, $motivazione_select){
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    $email_user = $user->get('mail')->getValue()[0]['value'];


    $azienda = $user->get('field_azienda')->getValue()[0]['target_id'];

    if($pubblicabile == 201){
      $status = 1;
    }else{
      $status = 0;
    }

    $node = Node::create([
      'type' => 'richesta',
      'title' => 'Richiesta personalizzata-'.$azienda,
      'field_motivazione' => $testo_nuova_richeista,
      'field_stato' => 196,
      'field_utente' => $azienda,
      'field_user_richiedente' => $uid,
      'field_motivazione_richiesta_pers' =>$motivazione_select ,
      'field_pubblicazione_personalizza' =>$pubblicabile,
      'status' => 0
    ]);
    $node->save();
    NotificationController::richiesta_peronalizzata($email_user, $node->id());

    $url_page = '/user';

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }

  public function conferma_pubblicazione($id_richiesta){
    $node=Node::load($id_richiesta);
    $node->set('status', 1);
    $node->save();

    if(!empty($node->get('field_user_richiedente')->getValue()[0]['target_id'])){
      $user = User::load($node->get('field_user_richiedente')->getValue()[0]['target_id']);
      $mail = $user->get('mail')->getValue()[0]['value'];

        NotificationController::conferma_pubblicazione($mail, $id_richiesta);
    }

    $url_page = '/node/72';

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }

  public function invia_richiesta_servizi($nid_service){
    $uid = \Drupal::currentUser()->id();
    if($uid != 0 ){
      $user = User::load(\Drupal::currentUser()->id());
      $email_user = $user->get('mail')->getValue()[0]['value'];

      $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);
      $service = json_decode($nid_service);
      //print_r($service);
      for($y = 0; $y < count($service); $y++){

        $servizio = Node::load($service[$y][0]);
        $azienda_proprietaria_servizio = $servizio->get('field_stakeholder')->getValue()[0]['target_id'];

        $stakeholder = Node::load($servizio->get('field_stakeholder')->getValue()[0]['target_id']);
        if(!empty($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
          $email_stakeholder = $stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'];
        }else{
          $email_stakeholder = '';
        }

        if($service[$y][1] != ''){
          $motivazione = $service[$y][1];
        }else{
          $motivazione = '';
        }

        $node_new = Node::create([
          'type'        => 'richesta',
          'title'       => 'Richiesta - '.$azienda->get('title')->getValue()[0]['value'],
          'field_servizio' => $service[$y][0],
          'field_utente' => $user->get('field_azienda')->getValue()[0]['target_id'],
          'field_azienda_proprietaria' =>$azienda_proprietaria_servizio,
          'field_stato' => 196,
          'field_user_richiedente' => $uid,
          'field_motivazione' => $motivazione,
          'status' => 1
        ]);
        $node_new->save();
        $new_nid = $node_new->id();

        NotificationController::nuova_richiesta_cliente($email_user, $service[$y][0]);
        if( $email_stakeholder != ''){
          NotificationController::nuova_richiesta_stakeholder($email_stakeholder, $service[$y][0], $new_nid, 'servizio');
        }

      }

      $url_page = '/node/30';
    }else{
      $url_page = '/404';
    }
    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }


  public function servizio_nonerogabile($id_richiesta){
    $node = Node::load($id_richiesta);


    $user_richiedente = Node::load($node->get('field_utente')->getValue()[0]['target_id']);

    if(!empty($node->get('field_user_richiedente')->getValue()[0]['target_id'])){
      $user2 =  User::load($node->get('field_user_richiedente')->getValue()[0]['target_id']);
      $mail_user = $user2->get('mail')->getValue()[0]['value'];
      NotificationController::servizio_nonerogabile_mail($mail_user, $id_richiesta);
    }

    if(!empty($user_richiedente->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
      $email_azienda = $user_richiedente->get('field_email_contatto_riferimento')->getValue()[0]['value'];
    }else{
      $email_azienda = '';
    }

    if($email_azienda != ''){
      NotificationController::servizio_nonerogabile_mail($email_azienda, $id_richiesta);
    }


    $node->set('field_stato', 198);

    if(\Drupal::currentUser()->id() != 0 ){
      $url_page = '/node/'.$id_richiesta;
    }else{
      $url_page = '/';
    }
    return new RedirectResponse(URL::fromUserInput($url_page)->toString());


  }

  public function servizio_erogabile($id_richiesta){
    $node = Node::load($id_richiesta);
    $node->set('field_stato', 197);
    $node->save();

    $user_richiedente = Node::load($node->get('field_utente')->getValue()[0]['target_id']);

    if(!empty($node->get('field_user_richiedente')->getValue()[0]['target_id'])){
      $user2 =  User::load($node->get('field_user_richiedente')->getValue()[0]['target_id']);
      $mail_user = $user2->get('mail')->getValue()[0]['value'];
      NotificationController::cambio_stato_richiesta($mail_user, 'Presa in carico', $id_richiesta);
    }


    if(!empty($user_richiedente->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
      $email_azienda = $user_richiedente->get('field_email_contatto_riferimento')->getValue()[0]['value'];
    }else{
      $email_azienda = '';
    }

    if($email_azienda != ''){
      NotificationController::cambio_stato_richiesta($email_azienda, 'Presa in carico', $id_richiesta);
    }

    if(\Drupal::currentUser()->id() != 0 ){
      $url_page = '/node/'.$id_richiesta;
    }else{
      $url_page = '/';
    }

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }

  public function invia_conclusioni($id_richiesta,$id_motivazione, $obiettivi){
    $node = Node::load($id_richiesta);
    $node->set('field_stato', 198);
    $node->set('field_obbiettivi', $obiettivi);
    $node->save();
    if(!empty($node->get('field_user_richiedente')->getValue()[0]['target_id'])){

      $user = User::load($node->get('field_user_richiedente')->getValue()[0]['target_id']);
      $mail = $user->get('mail')->getValue()[0]['value'];
      NotificationController::cambio_stato_richiesta_conclusione($mail, $obiettivi, $id_richiesta);
    }
    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    return new Response(render($build));
  }

}
