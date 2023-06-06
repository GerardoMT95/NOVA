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
class StakeHolderController extends \Twig_Extension {

  public function getName() {
    return 'ex81.StakeHolderController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('is_stakeholder', array($this, 'is_stakeholder')),
      new \Twig_SimpleFunction('get_all_stakeholder', array($this, 'get_all_stakeholder')),
    );
  }

  public function is_stakeholder(){
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);

    if(!empty($user->get('field_azienda')->getValue()[0]['target_id'])){
      $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);

      if($azienda->get('field_stakeholder_')->getValue()[0]['value'] == 1){
        return 'true';
      }else{
        return 'false';
      }
    }else{
        return 'false';
    }



  }

  public function get_all_stakeholder(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'impresa')
    ->condition('status', 1)
    ->condition('field_stakeholder_',1);
    $imprese = $query->execute();

    if(count($imprese)>0){
      foreach ($imprese as $value) {
        $node = Node::load($value);
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
        if(!empty($node->get('field_sito_web')->getValue()[0]['value'])){
          $sito_web = $node->get('field_sito_web')->getValue()[0]['value'];
        }else{
          $sito_web = '';
        }
        if(!empty($node->get('field_indirizzo_della_sede')->getValue()[0]['value'])){
          $indirizzo = $node->get('field_indirizzo_della_sede')->getValue()[0]['value'];
        }else{
          $indirizzo = '';
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
          'indirizzo' => $indirizzo,
          'piva' =>$node->get('field_codice_fiscale_impresa')->getValue()[0]['value'],
          'url_image' => $url
        );
      }
    }else{
      $aziende_lista = [];
    }

    return $aziende_lista;
  }

}
