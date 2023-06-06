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
class BandiController extends \Twig_Extension {

  public function getName() {
    return 'ex81.BandiController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('get_bandi', array($this, 'get_bandi')),
    );
  }

  public function get_bandi(){

      $query = \Drupal::entityQuery('node')
      ->condition('type', 'bando')
      ->condition('field_data_di_pubblicazione',date("Y-01-01"),'>=')
      ->sort('field_data_di_pubblicazione' , 'DESC')
      ->condition('status', 1);
      if($_GET["ente"]) $query = \Drupal::entityQuery('node')->condition('field_ente',$_GET["ente"]);
      $bandi = $query->execute();

      if(count($bandi)>0){

        foreach ($bandi as $value) {

          $node = Node::load($value);
          $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
          $bandi_lista[]=array(
            'nid' => $value,
            'url' => $node->get('field_url_bando')->getValue()[0]['value'],
            'ente' => $node->get('field_ente')->getValue()[0]['value'],
            'categoria' => $node->get('field_categoria')->getValue()[0]['value'],
            'note' => strip_tags($node->get('field_note')->getValue()[0]['value'],'<p><br><\br>'),
            'data_di_pubblicazione' => date("d/m/Y",strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value'])),
            'title' => $node->get('title')->getValue()[0]['value'],
          );
        }

      }else{
        $bandi_lista = [];
      }

      return $bandi_lista;
  }


}
