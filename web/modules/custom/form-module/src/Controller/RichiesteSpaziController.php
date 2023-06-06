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


class RichiesteSpaziController extends \Twig_Extension {

  public function getName() {
    return 'ex81.RichiesteSpaziController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('get_macroaree', array($this, 'get_macroaree')),
      new \Twig_SimpleFunction('get_richieste_spazi_cliente', array($this, 'get_richieste_spazi_cliente')),
      new \Twig_SimpleFunction('get_elenco_richieste_gestite_spazi', array($this, 'get_elenco_richieste_gestite_spazi')),
    );
  }

  public function invia_richiesta_spazi($nid_spazi){

    $user = User::load(\Drupal::currentUser()->id());
    $email_user = $user->get('mail')->getValue()[0]['value'];
    $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);

    $spazi= json_decode($nid_spazi);

    for($y = 0; $y < count($spazi); $y++){
      echo 'spazioooo'.$spazi[$y][0];
      $spazio = Node::load($spazi[$y][0]);
      $azienda_proprietaria_servizio = $spazio->get('field_impresa')->getValue()[0]['target_id'];
      if($spazi[$y][1] != ''){
        $motivazione = $spazi[$y][1];
      }else{
        $motivazione = '';
      }
      $stakeholder = Node::load($spazio->get('field_impresa')->getValue()[0]['target_id']);
      if(!empty($stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'])){
        $email_stakeholder = $stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'];
      }else{
        $email_stakeholder = '';
      }

      $node_new = Node::create([
        'type'        => 'richesta',
        'title'       => 'Richiesta - '.$azienda->get('title')->getValue()[0]['value'],
        'field_spazio' => $spazi[$y][0],
        'field_utente' => $user->get('field_azienda')->getValue()[0]['target_id'],
        'field_azienda_proprietaria' =>$azienda_proprietaria_servizio,
        'field_stato' => 196,
        'field_motivazione' => $motivazione,
        'status' => 1
      ]);
      $node_new->save();
      $new_nid = $node_new->id();
      NotificationController::nuova_richiesta_cliente($email_user, $spazi[$y][0]);
      if( $email_stakeholder != ''){
        NotificationController::nuova_richiesta_stakeholder($email_stakeholder, $spazi[$y][0], $new_nid, 'spazio');
      }
    }
    $url_page = '/node/30';
    return new RedirectResponse(URL::fromUserInput($url_page)->toString());
  }

  public function get_richieste_spazi_cliente(){

    $user = User::load(\Drupal::currentUser()->id());

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    ->condition('field_spazio', '', '<>')
    ->condition('field_utente', $user->get('field_azienda')->getvalue()[0]['target_id'])
    ->condition('status', 1);
    $richieste = $query->execute();

    if(count($richieste)>0){
      foreach($richieste as $value){
        $richiesta = Node::load($value);
        if(!empty($richiesta->get('field_spazio')->getValue()[0]['target_id'])){
          if(!empty($richiesta->get('field_stato')->getValue()[0]['target_id'])){
            $stato_richiesta = Term::load($richiesta->get('field_stato')->getValue()[0]['target_id']);
            $nome_stato = $stato_richiesta->get('name')->getValue()[0]['value'];
          }else{
            $stato_richiesta = '';
          }


          $data_invio_richiesta = date('d/m/Y', $richiesta->get('created')->getValue()[0]['value']);

          $spazio = Node::load($richiesta->get('field_spazio')->getValue()[0]['target_id']);

          $nome_spazio = $spazio->get('title')->getValue()[0]['value'];

          $stakeholder = Node::load($spazio->get('field_impresa')->getValue()[0]['target_id']);
          $nome_stakeholder = $stakeholder->get('field_ragione_sociale_')->getValue()[0]['value'];

          $elenco_richieste[]=array(
            'id_richiesta' => $value,
            'nid_spazio' => $richiesta->get('field_spazio')->getValue()[0]['target_id'],
            'title_spazio' => $nome_spazio,
            'nid_stakeholder' =>$spazio->get('field_impresa')->getValue()[0]['target_id'],
            'nome_stakeholder' => $nome_stakeholder,
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

  public function get_elenco_richieste_gestite_spazi(){

    $user = User::load(\Drupal::currentUser()->id());

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'richesta')
    //->condition('field_spazio', '', '<>')
    ->condition('field_azienda_proprietaria', $user->get('field_azienda')->getValue()[0]['target_id'])
    ->condition('status', 1);
    if(isset($_GET['stato_richiesta']) && $_GET['stato_richiesta'] != 'null') $query->condition('field_stato', $_GET['stato_richiesta']);
    if(isset($_GET['imprese']) && $_GET['imprese'] != 'null') $query->condition('field_utente', $_GET['imprese']);
    if(isset($_GET['spazio_stakeholder']) && $_GET['spazio_stakeholder'] != 'null'){
      $query->condition('field_spazio', $_GET['spazio_stakeholder']);
    }else{
      $query->condition('field_spazio', '', '<>');
    }
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
        if(!empty($richiesta->get('field_spazio')->getValue()[0]['target_id'])){

          $spazio = Node::load($richiesta->get('field_spazio')->getValue()[0]['target_id']);
          $nome_spazio = $spazio->get('title')->getValue()[0]['value'];



          if(!empty($richiesta->get('field_utente')->getValue()[0]['target_id'])){
            $id_azienda_richiedente = Node::load($richiesta->get('field_utente')->getValue()[0]['target_id']);
            $nome_azienda_richiedente = $id_azienda_richiedente->get('title')->getValue()[0]['value'];
          }else{
            $id_azienda_richiedente ='';
            $nome_azienda_richiedente = '';
          }

          $elenco_richieste[]=array(
            'id_richiesta' => $value,
            'nid_spazio' => $richiesta->get('field_spazio')->getValue()[0]['target_id'],
            'title_spazio' => $nome_spazio,
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
    isset($_GET['spazio_stakeholder']) && $_GET['spazio_stakeholder'] != 'null' or
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


}
