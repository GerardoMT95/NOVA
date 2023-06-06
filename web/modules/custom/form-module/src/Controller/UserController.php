<?php

namespace Drupal\ex81\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\UrlHelper;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\Query;
use Drupal\Core\Entity\Query\QueryInterface;
use \Symfony\Component\HttpFoundation\Response;
/**
* UserController controller.
*/
class UserController extends \Twig_Extension {


  public function getName() {
    return 'ex81.UserController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('info_user_logged', array($this, 'info_user_logged')),
      new \Twig_SimpleFunction('get_riepilogo', array($this, 'get_riepilogo')),
      new \Twig_SimpleFunction('get_riepilogo_definitivo', array($this, 'get_riepilogo_definitivo')),
      new \Twig_SimpleFunction('change_format_date', array($this, 'change_format_date')),
      new \Twig_SimpleFunction('is_amm_portale', array($this, 'is_amm_portale')),
    );
  }
  public function is_amm_portale(){
    $uid = \Drupal::currentUser()->id();
    if($uid != 0){
      $current_user = \Drupal::currentUser();
      $roles = $current_user->getRoles();
      if (in_array("amministratore_portale", $roles)){
        return true;
      }else{
        return false;
      }

    }else{
      return false;
    }


  }

  public function info_user_logged(){

    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);

    if(!empty($user->get('field_nome')->getValue()[0]['value'])){
      $nome = $user->get('field_nome')->getValue()[0]['value'];
    }else{
      $nome ='';
    }

    if(!empty($user->get('field_cognome')->getValue()[0]['value'])){
      $cognome = $user->get('field_cognome')->getValue()[0]['value'];
    }else{
      $cognome ='';
    }

    if(!empty($user->get('mail')->getValue()[0]['value'])){
      $email = $user->get('mail')->getValue()[0]['value'];
    }else{
      $email ='';
    }

    if(!empty($user->get('field_codice_fiscale')->getValue()[0]['value'])){
      $cod_fis = $user->get('field_codice_fiscale')->getValue()[0]['value'];
    }else{
      $cod_fis ='';
    }

    if(!empty($user->get('field_numero_di_telefono')->getValue()[0]['value'])){
      $numero_tel = $user->get('field_numero_di_telefono')->getValue()[0]['value'];
    }else{
      $numero_tel ='';
    }

    if(!empty($user->get('field_luogo_di_nascita')->getValue()[0]['value'])){
      $luogo_nascita = $user->get('field_luogo_di_nascita')->getValue()[0]['value'];
    }else{
      $luogo_nascita ='';
    }

    if(!empty($user->get('field_data_di_nascita')->getValue()[0]['value'])){
      $data_nascita = date("d/m/Y", strtotime($user->get('field_data_di_nascita')->getValue()[0]['value']));
    }else{
      $data_nascita ='';
    }

    if(!empty($user->get('field_codice_fiscale')->getValue()[0]['value'])){
      $cof_fis = $user->get('field_codice_fiscale')->getValue()[0]['value'];
    }else{
      $cof_fis ='';
    }



    $info_user=array(
      'uid'=>$uid,
      'nome'=>$nome,
      'cognome' => $cognome,
      'email' => $email,
      'cod_fis' => $cod_fis,
      'numero_telefono' => $numero_tel,
      'luogo_nascita' => $luogo_nascita,
      'data_nascita' => $data_nascita,
      'cod_fis'=> $cof_fis
    );

    return $info_user;

  }

  public function azienda_primaria($nid_azienda){
    $user = User::load(\Drupal::currentUser()->id());
    $user->set('field_azienda', $nid_azienda);
    $user->save();

    $url_page = '/user';

    return new RedirectResponse(URL::fromUserInput($url_page)->toString());

  }

  public function add_service_user($nid){
    $user = User::load(\Drupal::currentUser()->id());
    $user->field_riep->appendItem($nid);
    $user->save();
    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    return new Response(render($build));
  }

  public function remove_service_user($nid,$index){
    $user = User::load(\Drupal::currentUser()->id());
    $array = $user->get('field_riep')->getValue();
    unset($array[$index]);
    $new = array_values($array);
    $user->set('field_riep', $new);
    $user->save();
    $nuovo_riepilogo = UserController::get_riepilogo();
    $build = array(
      '#type' => 'markup',
      '#markup' => json_encode($nuovo_riepilogo),
    );
    return new Response(render($build));
  }



  public function get_riepilogo(){
    if(\Drupal::currentUser()->id() != 0){
      $user = User::load(\Drupal::currentUser()->id());
      if(count($user->get('field_riep')->getValue())>0){

        for($i=0;$i<count($user->get('field_riep')->getValue());$i++){
          if($user->get('field_riep')->getValue()[$i]['target_id'] != 0){
            $node=Node::load($user->get('field_riep')->getValue()[$i]['target_id']);
            $type_name = $node->type->entity->label();
            if($type_name == 'Spazio di lavoro'){
              $fornitore = Node::load($node->get('field_impresa')->getValue()[0]['target_id']);
            }else{
              $fornitore = Node::load($node->get('field_stakeholder')->getValue()[0]['target_id']);
            }
            $riepilogo[]=array(
              'nid' => $user->get('field_riep')->getValue()[$i]['target_id'],
              'title' =>$node->get('title')->getValue()[0]['value'],
              'fornitore' => $fornitore->get('title')->getValue()[0]['value'],
              'tipo' => $type_name
            );
          }else{
            $riepilogo = [];
          }
        }
      }else{
        $riepilogo = [];
      }
    }else{
      $riepilogo = [];
    }
    return $riepilogo;
  }

  public function get_riepilogo_definitivo(){
    $user = User::load(\Drupal::currentUser()->id());
    if(count($user->get('field_riepilogo_definitivo')->getValue())>0){
      for($i=0;$i<count($user->get('field_riepilogo_definitivo')->getValue());$i++){
        $node=Node::load($user->get('field_riepilogo_definitivo')->getValue()[$i]['target_id']);
        $type_name = $node->type->entity->label();
        if($type_name == 'Spazio di lavoro'){
          $fornitore = Node::load($node->get('field_impresa')->getValue()[0]['target_id']);
        }else{
          $fornitore = Node::load($node->get('field_stakeholder')->getValue()[0]['target_id']);
        }
        $riepilogo[]=array(
          'nid' => $user->get('field_riep')->getValue()[$i]['target_id'],
          'title' =>$node->get('title')->getValue()[0]['value'],
          'fornitore' => $fornitore->get('title')->getValue()[0]['value']
        );
      }
    }else{
      $riepilogo = [];
    }
    return $riepilogo;
  }

  public function add_service_user_def($nid){
    $user = User::load(\Drupal::currentUser()->id());
    $user->field_riepilogo_definitivo->appendItem($nid);
    $user->save();
    $build = array(
      '#type' => 'markup',
      '#markup' => 'true',
    );
    return new Response(render($build));
  }

  public function remove_service_user_def($nid,$index){
    $user = User::load(\Drupal::currentUser()->id());
    $array = $user->get('field_riepilogo_definitivo')->getValue();
    unset($array[$index]);
    $new = array_values($array);
    $user->set('field_riepilogo_definitivo', $new);
    $user->save();
    $nuovo_riepilogo = UserController::get_riepilogo_definitivo();
    $build = array(
      '#type' => 'markup',
      '#markup' => json_encode($nuovo_riepilogo),
    );
    return new Response(render($build));
  }
  public function change_format_date($data){

    $data_1 = explode(' ',$data[0]['#markup']);

    $timestamp = strtotime($data_1[1]);

    return date('d/m/Y h:i',$timestamp);
  }
}
