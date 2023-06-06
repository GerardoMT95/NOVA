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

use Drupal\ex81\Controller\NotificationController;
class RichiesteController extends \Twig_Extension {

  public function getName() {
    return 'ex81.RichiesteController';
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

      new \Twig_SimpleFunction('elenco_richieste_attesa', array($this, 'elenco_richieste_attesa')),
      new \Twig_SimpleFunction('info_richiesta', array($this, 'info_richiesta')),
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
    $elenco_richieste = [];

    if(\Drupal::currentUser()->id() != 0){
      $user = User::load(\Drupal::currentUser()->id());
      if(!empty($user->get('field_azienda')->getvalue()[0]['target_id'])){


        $query = \Drupal::entityQuery('node')
        ->condition('type', 'richesta')
        ->condition('field_utente', $user->get('field_azienda')->getvalue()[0]['target_id'])
          ->sort('created' , 'DESC')
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

              if(isset($servizio->get('field_macroarea')->getValue()[0])){
                $tid_macroarea = Term::load($servizio->get('field_macroarea')->getValue()[0]['target_id']);
                $nome_macroarea = $tid_macroarea->get('name')->getValue()[0]['value'];
              }else{
                $tid_macroarea ='';
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

              if(isset($stakeholder->get('title')->getValue()[0]['value'])){
                $nome_stakeholder = $stakeholder->get('title')->getValue()[0]['value'];
              }else{
                $nome_stakeholder = '';
              }
              //print_r($richiesta->get('field_commento_richiesta')->getValue()[0]['comment_count']);
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
      }
    }

    return $elenco_richieste;
  }

  public function get_elenco_richieste_gestite(){

    $user = User::load(\Drupal::currentUser()->id());
    //$azienda_servizio = $user->get('field_azienda')->getValue()[0]['target_id'];
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_azienda_proprietaria', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->condition('status', 1)

    ->sort('created' , 'DESC');
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
            'numero_commenti' => $richiesta->get('field_commento_richiesta')->getValue()[0]['comment_count']
          );
        }
      }
    }else{

      $elenco_richieste = [];
    }

    return $elenco_richieste;
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
        $data_fine_richiesta = date('d/m/Y', strtotime($richiesta->get('field_data_fine')->getValue()[0]['value']));
        $motivazione = $richiesta->get('field_motivazione')->getValue()[0]['value'];


        $elenco_richieste[]=array(
          'id_richiesta' => $value,
          'stato_richiesta' => $nome_stato,
          'motivazione_std' =>$nome_motivazione_std,
          'motivazione'=>$motivazione,
          'data' =>$data_invio_richiesta,
          'data_fine' =>$data_fine_richiesta
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
    ->notExists('field_servizio')
    ->notExists('field_spazio');
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
        $data_fine_richiesta = date('d/m/Y', strtotime($richiesta->get('field_data_fine')->getValue()[0]['value']));
        $motivazione = $richiesta->get('field_motivazione')->getValue()[0]['value'];

        if(!empty($richiesta->get('field_pubblicazione_personalizza')->getValue()[0]['target_id'])){
          $tid_pubblicazione = $richiesta->get('field_pubblicazione_personalizza')->getValue()[0]['target_id'];
          if($tid_pubblicazione == 201){
            $richiesta_pubblicazione = 'si';
          }else{
            $richiesta_pubblicazione = 'no';
          }
        }
        if(!empty($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id'])){
          $stakeholder = Node::load($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
          $nome_stakeholder= $stakeholder->get('title')->getValue()[0]['value'];

        }else{
          $nome_stakeholder = '';
        }

        if(!empty($richiesta->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id'])){
          $term_motivazione = Term::load($richiesta->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id']);
          $nome_motivazione = $term_motivazione->get('name')->getValue()[0]['value'];
        }else{
          $nome_motivazione = '';
        }
        $elenco_richieste[]=array(
          'id_richiesta' => $value,
          'data_invio' => $data_invio_richiesta,
          'data_fine' => $data_fine_richiesta,
          'stato_richiesta' => $nome_stato,
          'motivazione'=>$motivazione,
          'richiesta_pubblicazione'=>$richiesta_pubblicazione,
          'status' =>$richiesta->get('status')->getValue()[0]['value'],
          'num_commenti' => $richiesta->get('field_commento_richiesta')->getValue()[0]['comment_count'],
          'nome_motivazione' => $nome_motivazione,
          'nome_stakeholder' =>$nome_stakeholder,
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
          'numero_commenti' => $richiesta->get('field_commento_richiesta')->getValue()[0]['comment_count']
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
      echo 'ciao';
      NotificationController::cambio_stato_richiesta($email_azienda, $nome_stato, $id_richiesta);
    }

    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    return new Response(render($build));
  }

  public function update_stato_richiesta_nonerogabile($id_richiesta){

    $richesta = Node::load($id_richiesta);
    $richesta->set('field_stato', 464);
    $richesta->save();

    $azienda_cliente = Node::load($richesta->get('field_utente')->getValue()[0]['target_id']);
    if(!empty($azienda_cliente->get('field_email_contatto_riferimento')->getValue()[0])){
      $email_azienda = $azienda_cliente->get('field_email_contatto_riferimento')->getValue()[0]['value'];
    }else{
      $email_azienda = '';
    }

    $stato = Term::load(464);
    $nome_stato = $stato->get('name')->getValue()[0]['value'];

    if($email_azienda != ''){
      NotificationController::cambio_stato_richiesta_nonerogabile($email_azienda, $nome_stato, $id_richiesta);
    }

    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    return new Response(render($build));
  }

  /*public function nuova_richiesta($testo_nuova_richeista,$pubblicabile, $motivazione_select){
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
      'field_motivazione_richiesta_pers' =>$motivazione_select ,
      'field_pubblicazione_personalizza' =>$pubblicabile,
      'status' => 0
    ]);
    $node->save();
    NotificationController::richiesta_peronalizzata($email_user, $node->id());

    $url_page = '/user';

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }*/

  public function conferma_pubblicazione($id_richiesta){
    $node=Node::load($id_richiesta);
    $node->set('status', 1);
    $node->save();

    $url_page = '/node/72';

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }

  public function invia_richiesta($nid_service){
    $uid = \Drupal::currentUser()->id();


    if($uid != 0){
      $user = User::load(\Drupal::currentUser()->id());
      $email_user = $user->get('mail')->getValue()[0]['value'];

      $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);

      $service = json_decode($nid_service);
      //print_r($service);
      for($y = 0; $y < count($service); $y++){

        $node = Node::load($service[$y][0]);
        $type_name = $node->type->entity->label();

        if($service[$y][1] != ''){
          $motivazione = $service[$y][1];
        }else{
          $motivazione = '';
        }

        if($type_name == 'Spazio di lavoro'){

          $stakeholder = Node::load($node->get('field_impresa')->getValue()[0]['target_id']);
          if(!empty($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
            $email_stakeholder = $stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'];
          }else{
            $email_stakeholder = '';
          }
          $node_new = Node::create([
            'type'        => 'richesta',
            'title'       => 'Richiesta - '.$azienda->get('title')->getValue()[0]['value'],
            'field_spazio' => $service[$y][0],
            'field_utente' => $user->get('field_azienda')->getValue()[0]['target_id'],
            'field_azienda_proprietaria' =>$node->get('field_impresa')->getValue()[0]['target_id'],
            'field_user_richiedente' => $uid,
            'field_stato' => 196,
            'field_motivazione' => $motivazione,
            'status' => 1
          ]);
          $node_new->save();
          $new_nid = $node_new->id();

          if( $email_stakeholder != ''){
            NotificationController::nuova_richiesta_stakeholder_spazio($email_stakeholder, $service[$y][0], $new_nid, $type_name, $service[$y][0], $uid);
          }else{
            NotificationController::nuova_richiesta_stakeholder_spazio('assistenza@gmgnet.com', $service[$y][0], $new_nid, $type_name, $service[$y][0], $uid);
          }

        }else{ 
          $stakeholder = Node::load($node->get('field_stakeholder')->getValue()[0]['target_id']);
          if(!empty($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
            $email_stakeholder = $stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'];
          }else{
            $email_stakeholder = '';
          }

          $node_new = Node::create([
            'type'        => 'richesta',
            'title'       => 'Richiesta - '.$azienda->get('title')->getValue()[0]['value'],
            'field_servizio' => $service[$y][0],
            'field_utente' => $user->get('field_azienda')->getValue()[0]['target_id'],
            'field_azienda_proprietaria' =>$node->get('field_stakeholder')->getValue()[0]['target_id'],
            'field_stato' => 196,
            'field_motivazione' => $motivazione,
            'field_user_richiedente' => $uid,
            'status' => 1
          ]);
          $node_new->save();
          $new_nid = $node_new->id();

          if( $email_stakeholder != ''){
            NotificationController::nuova_richiesta_stakeholder_service($email_stakeholder, $service[$y][0], $new_nid, $type_name);
          }else{
            NotificationController::nuova_richiesta_stakeholder_service('assistenza@gmgnet.com', $service[$y][0], $new_nid, $type_name);
          }
        }

        NotificationController::nuova_richiesta_cliente($email_user, $service[$y][0]);



      }
      $user->set('field_riep', '');

      $user->save();

      $url_page = '/node/30';
    }else{
      $url_page = '/404';
    }


    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }


  /*public function servizio_nonerogabile($id_richiesta){
    //if(\Drupal::currentUser()->id() != 0 ){

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
      $url_page = '/'.$id_richiesta;
    }else{
      $url_page = '/';
    }
    return new RedirectResponse(URL::fromUserInput($url_page)->toString());

  }

  public function servizio_erogabile($id_richiesta){
    //if(\Drupal::currentUser()->id() != 0 ){

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
    //$node->save();

    echo 'o';
    if(!empty($node->get('field_user_richiedente')->getValue()[0]['target_id'])){
      echo 'sono qua';
      $user = User::load($node->get('field_user_richiedente')->getValue()[0]['target_id']);
      $mail = $user->get('mail')->getValue()[0]['value'];
      NotificationController::cambio_stato_richiesta_conclusione($mail, $obiettivi, $id_richiesta);
    }
    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    //return new Response(render($build));
  }*/

  public function elenco_richieste_attesa(){
    $nid_richieste = [];
    $user = User::load(\Drupal::currentUser()->id());
    $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_utente', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->condition('field_servizio', '', '<>')
    ->condition('field_stato',196);
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach ($richieste as $value) {
        $node = Node::load($value);
        if(!empty($node->get('field_servizio')->getValue()[0]['target_id'])){

          $nid = $node->get('field_servizio')->getValue()[0]['target_id'];
        }else{
          $nid = '';
        }
        array_push($nid_richieste,$nid);

      }
    }
    $build = array(
      '#type' => 'markup',
      '#markup' => json_encode($nid_richieste),
    );
    return $nid_richieste;
  }

  public function info_richiesta($id_richiesta){

    $node = Node::load($id_richiesta);

    if(!empty($node->get('field_utente')->getvalue()[0]['target_id'])){
      $azienda = Node::load($node->get('field_utente')->getvalue()[0]['target_id']);


      $id_azienda = $node->get('field_utente')->getvalue()[0]['target_id'];
      $nome_azienda = $azienda->get('field_ragione_sociale_')->getValue()[0]['value'];

      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$id_azienda);

      if(!empty($azienda->get('field_logo_impresa')->getValue()[0]['target_id'])){
        $image = File::load($azienda->get('field_logo_impresa')->getValue()[0]['target_id']);
        $url = $image->getFileUri();
      }else{
        $url = '';
      }

      if(!empty($azienda->get('field_codice_fiscale_impresa')->getValue()[0]['value'])){
        $cod_fiscale_impresa = $azienda->get('field_codice_fiscale_impresa')->getValue()[0]['value'];
      }else{
        $cod_fiscale_impresa = '';
      }

      if(!empty($azienda->get('field_indirizzo_della_sede')->getValue()[0]['value'])){
        $indirizzo_sede = $azienda->get('field_indirizzo_della_sede')->getValue()[0]['value'];
      }else{
        $indirizzo_sede = '';
      }

      if(!empty($azienda->get('field_numero_civico')->getValue()[0]['value'])){
        $num_civico_sede = $azienda->get('field_numero_civico')->getValue()[0]['value'];
      }else{
        $num_civico_sede = '';
      }

      if(!empty($azienda->get('field_codice_comune')->getValue()[0]['value'])){
        $cod_comune_sede = $azienda->get('field_codice_comune')->getValue()[0]['value'];
      }else{
        $cod_comune_sede = '';
      }

      if(!empty($azienda->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
        $email_azienda = $azienda->get('field_email_contatto_riferimento')->getValue()[0]['value'];
      }else{
        $email_azienda = '';
      }

      if(!empty($azienda->get('field_sito_web')->getValue()[0]['value'])){
        $sito_web = $azienda->get('field_sito_web')->getValue()[0]['value'];
      }else{
        $sito_web = '';
      }

    }else{
      $id_azienda = '';
      $nome_azienda = '';
      $alias = '';
      $url = '';
      $cod_fiscale_impresa = '';
      $indirizzo_sede = '';
      $num_civico_sede = '';
      $cod_comune_sede = '';
      $email_azienda = '';
      $sito_web = '';
    }

    if($node->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id']){
      $motivazione_term = Term::load($node->get('field_motivazione_richiesta_pers')->getValue()[0]['target_id']);
      $name_motivazione = $motivazione_term->get('name')->getValue()[0]['value'];
    }else{
      $name_motivazione = '';
    }

    if(!empty($node->get('field_motivazione')->getValue()[0]['value'])){
      $motivazione_testo = $node->get('field_motivazione')->getValue()[0]['value'];
    }else{
      $motivazione_testo = '';
    }

    $informazioni_richiesta = array(
      'id_azienda' => $id_azienda,
      'alias' => $alias,
      'nome_azienda' => $nome_azienda,
      'logo' => $url,
      'cod_fiscale' => $cod_fiscale_impresa,
      'indirizzo_sede' => $indirizzo_sede,
      'numero_civico' =>$num_civico_sede,
      'codice_comune'=>$cod_comune_sede,
      'email_azienda' =>$email_azienda,
      'sito_web' => $sito_web,
      'motivazione_term' => $name_motivazione,
      'motivazione_testo' => $motivazione_testo
    );
    return $informazioni_richiesta;
  }

  public function nuovo_commento(){
    $uid = \Drupal::currentUser()->id();

    if($uid != 0){
      $user = User::load($uid);
      $name = $user->get('name')->getValue()[0]['value'];

      $mail_user = $user->get('mail')->getValue()[0]['value'];
      $query = \Drupal::entityQuery('comment')
      ->condition('uid',$uid)
      ->sort('created' , 'DESC')
      ->range(0,1)
      ->condition('status', 1);
      $commenti = $query->execute();

      if(count($commenti)>0){
        foreach ($commenti as $value) {

          $comment = \Drupal::entityTypeManager()->getStorage('comment')->load($value);
          $uid_cretore =  $comment->get('uid')->getvalue()[0]['target_id'];
          $body =  $comment->get('comment_body')->value;

          $id = $comment->get('entity_id')->getvalue()[0]['target_id'];
          $richiesta = Node::load($comment->get('entity_id')->getvalue()[0]['target_id']);

          $titolo_richiesta = $richiesta->get('title')->getValue()[0]['title'];

          /*if(!empty($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id'])){

            $stakeholder = Node::load($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
            if(!empty($stakeholder->get('field_email_contatto_riferimento')->getvalue()[0]['value'])){
              $email_stakeholder = $stakeholder->get('field_email_contatto_riferimento')->getvalue()[0]['value'];
              //NotificationController::send_email_nuovo_commento($email_stakeholder,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body);
            }else{
              $email_stakeholder = '';
            }

            $utente_associato = RichiesteController::get_user_associated_stakeholder($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
            if($utente_associato != 'false'){
              $user_stakeholder = User::load($utente_associato);
              $email_user_stakholder = $user_stakeholder->get('mail')->getvalue()[0]['value'];

              //NotificationController::send_email_nuovo_commento($email_user_stakholder,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body);

            }
          }*/

          if($uid_cretore == $richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id']){
            $stakeholder = Node::load($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
            if(!empty($stakeholder->get('field_email_contatto_riferimento')->getvalue()[0]['value'])){
              $email_stakeholder = $stakeholder->get('field_email_contatto_riferimento')->getvalue()[0]['value'];
              NotificationController::send_email_nuovo_commento_creatore($email_stakeholder,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body);
            }else{
              $email_stakeholder = '';
            }
          }else{
            NotificationController::send_email_nuovo_commento($email_stakeholder,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body, $uid_cretore);
          }

          if($uid_cretore == $richiesta->get('field_user_richiedente')->getValue()[0]['target_id']){
            $user_add_comment = User::load($richiesta->get('field_user_richiedente')->getValue()[0]['target_id']);
            $email = $user_add_comment->get('mail')->getValue()[0]['value'];
            NotificationController::send_email_nuovo_commento_creatore($email,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body);
          }else{
            NotificationController::send_email_nuovo_commento($email_stakeholder,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body, $uid_cretore);
          }



          //NotificationController::send_email_nuovo_commento($mail_user,$titolo_richiesta, $comment->get('entity_id')->getvalue()[0]['target_id'],$body);

        }
      }

    }
    $url_page = '/node/'.$id;

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }

  public function get_user_associated_stakeholder($id_stakeholder){
    $query = \Drupal::entityQuery('user')
    ->condition('field_azienda',$id_stakeholder)
    ->range(0,1)
    ->condition('status', 1);

    $utenti = $query->execute();

    if(count($utenti)>0){
      foreach($utenti as $value){
        return $value;
      }
    }else{
      return 'false';
    }
  }

}
