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
class ArticleController extends \Twig_Extension {

  public function getName() {
    return 'ex81.ArticleController';
  }

  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('get_last_article', array($this, 'get_last_five_event')),
      new \Twig_SimpleFunction('get_last_article_by_nid', array($this, 'get_last_article_by_nid')),
      new \Twig_SimpleFunction('get_article', array($this, 'get_article')),
      new \Twig_SimpleFunction('get_news_marketing', array($this, 'get_news_marketing')),
      new \Twig_SimpleFunction('get_news_formazione', array($this, 'get_news_formazione')),
      new \Twig_SimpleFunction('get_article_json', array($this, 'get_article_json')),
      new \Twig_SimpleFunction('get_news_marketing_json', array($this, 'get_news_marketing_json')),
      new \Twig_SimpleFunction('get_news_formazione_json', array($this, 'get_news_formazione_json')),
      new \Twig_SimpleFunction('get_category_posizione', array($this, 'get_category_posizione')),
      new \Twig_SimpleFunction('opportunity_by_cat', array($this, 'opportunity_by_cat')),
      new \Twig_SimpleFunction('opportunity_by_cat_json', array($this, 'opportunity_by_cat_json')),
      new \Twig_SimpleFunction('news_by_tags', array($this, 'news_by_tags')),
      new \Twig_SimpleFunction('get_opportunita_feed', array($this, 'get_opportunita_feed')),
      new \Twig_SimpleFunction('get_alltags', array($this, 'get_alltags')),

    );
  }

  public function opportunity_by_cat_json(){
    $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    if(isset($tid)){
      $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('field_opportunita',1)
      ->condition('field_categoria_posizione', $tid)
      ->execute();

      if(isset($query)){
        foreach ($query as $key => $value) {
          $node = Node::load($value);
          $giorno = date('d', $node->get('created')->getValue()[0]['value']);
          $anno = date('Y', $node->get('created')->getValue()[0]['value']);
          $mese = date('m', $node->get('created')->getValue()[0]['value']);
          $data = $giorno.'-'.$mese.'-'.$anno;
          //echo $data;
          /*  $timestamp  = strtotime($data);
          echo date('m/d/Y', $timestamp);*/
          $articoli_lista[]=array(
            'nid' => $value,
            'timestamp_created' => strtotime($data),
          );
        }
      }
    }
    return json_encode($articoli_lista);
  }
  public function get_alltags(){
    $vid = 'tags';
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
  public function opportunity_by_cat(){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');


    $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    if(isset($tid)){
      $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('field_opportunita',1)
      ->condition('field_categoria_posizione', $tid)
      ->execute();

      if(isset($query)){
        foreach ($query as $key => $value) {


          $node = Node::load($value);
          $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

          if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
            $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
            $url = $image->getFileUri();
          }else{
            $url = '';
          }
          //echo $node->get('created')->getValue()[0]['value'];
          $giorno = date('d', $node->get('created')->getValue()[0]['value']);
          $anno = date('Y', $node->get('created')->getValue()[0]['value']);
          $mese = date('F', $node->get('created')->getValue()[0]['value']);

          $mese = str_ireplace($nen, $nit, $mese);

          if(!empty($node->get('field_categoria_posizione')->getValue()[0]['target_id'])){


            $posizione = Term::load($node->get('field_categoria_posizione')->getValue()[0]['target_id']);
            if(!empty($posizione->get('field_immagine_categoria')->getValue()[0]['target_id'])){
              $image_pos = File::load($posizione->get('field_immagine_categoria')->getValue()[0]['target_id']);
              $url_pos= $image_pos->getFileUri();
            }else{
              $url_pos = '';
            }
            $name_pos = $posizione->get('name')->getValue()[0]['value'];
            $tid_pos = $node->get('field_categoria_posizione')->getValue()[0]['target_id'];

          }else{
            $url_pos = '';
            $name_pos = '';
            $tis_pos= '';
          }

          $list[]=array(
            'nid' => $node->get('nid')->getValue()[0]['value'],
            'alias' => $alias,
            'title' => substr(html_entity_decode($node->get('title')->getValue()[0]['value']),0,40).'...',
            'url' => $url,
            'giorno' => $giorno,
            'mese' => $mese,
            'anno' => $anno,
            'url_pos' =>$url_pos,
            'name_pos' => $name_pos,
            'tid_pos' => $tis_pos,
            'timestamp_created' =>$node->get('created')->getValue()[0]['value'],
            'body' => $node->get('field_sottotitolo')->getValue()[0]['value'],
          );
        }

      }else{
        $list = [];
      }
    }
    return $list;
  }
  public function news_by_tags(){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');


    $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');

    if(isset($tid)){
      $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('field_opportunita',1)
      ->condition('field_tags', $tid)
      ->execute();

      if(isset($query)){
        foreach ($query as $key => $value) {


          $node = Node::load($value);
          $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

          if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
            $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
            $url = $image->getFileUri();
          }else{
            $url = '';
          }
          //echo $node->get('created')->getValue()[0]['value'];
          $giorno = date('d', $node->get('created')->getValue()[0]['value']);
          $anno = date('Y', $node->get('created')->getValue()[0]['value']);
          $mese = date('F', $node->get('created')->getValue()[0]['value']);

          $mese = str_ireplace($nen, $nit, $mese);

          if(!empty($node->get('field_categoria_posizione')->getValue()[0]['target_id'])){


            $posizione = Term::load($node->get('field_categoria_posizione')->getValue()[0]['target_id']);
            if(!empty($posizione->get('field_immagine_categoria')->getValue()[0]['target_id'])){
              $image_pos = File::load($posizione->get('field_immagine_categoria')->getValue()[0]['target_id']);
              $url_pos= $image_pos->getFileUri();
            }else{
              $url_pos = '';
            }
            $name_pos = $posizione->get('name')->getValue()[0]['value'];
            $tid_pos = $node->get('field_categoria_posizione')->getValue()[0]['target_id'];

          }else{
            $url_pos = '';
            $name_pos = '';
            $tis_pos= '';
          }

          $list[]=array(
            'nid' => $node->get('nid')->getValue()[0]['value'],
            'alias' => $alias,
            'title' => substr(html_entity_decode($node->get('title')->getValue()[0]['value']),0,40).'...',
            'url' => $url,
            'giorno' => $giorno,
            'mese' => $mese,
            'anno' => $anno,
            'url_pos' =>$url_pos,
            'name_pos' => $name_pos,
            'tid_pos' => $tis_pos,
            'timestamp_created' =>$node->get('created')->getValue()[0]['value'],
            'body' => $node->get('field_sottotitolo')->getValue()[0]['value'],
          );
        }

      }else{
        $list = [];
      }
    }
    return $list;
  }
  public function get_category_posizione(){
    $vid = 'categoria_oppurtunity';
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

  public function get_last_article(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('created' , 'DESC')
    ->condition('status', 1)
    ->range(0,1);
    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $node = Node::load($value);
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);

        if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else{
          $url = '';
        }

        $data = data('d/m/Y', strtotime($node->get('created')->getValue()[0]['value']));



        $articoli_lista=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'url' => $url,
          'data' => $data
        );
      }
    }else{
      $articoli_lista = [];
    }

    return $articoli_lista;
  }

  public function get_last_article_by_nid($nid){

    $node = Node::load($nid);

    $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$nid);

    if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
      $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
      $url = $image->getFileUri();
    }else{
      $url = '';
    }

    if(!empty($node->get('field_data_di_pubblicazione')->getValue()[0]['value'])){
    $data = $node->get('field_data_di_pubblicazione')->getValue()[0]['value'];
  }else{
    $data = '';
  }
    $articolo=array(
      'nid' => $nid,
      'alias' => $alias,
      'title' => $node->get('title')->getValue()[0]['value'],
      'data' =>$data,
      'url' => $url,
    );

    return $articolo;
  }


  public function get_article($range,$home, $id_impresa){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('field_data_di_pubblicazione' , 'DESC')
    ->condition('field_formazione', 0,'=')
    ->condition('status', 1);


    if($range != 0){ $query->range(0,$range); }
    if($id_impresa != ''){ $query->condition('field_azienda_associata',$id_impresa); }
    if($home == true){
      $query->condition('promote', 1);
    }
    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $node = Node::load($value);


        if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }else if(!empty($node->get('field_immagine_url')->getValue()[0]['value'])){
          $url = $node->get('field_immagine_url')->getValue()[0]['value'];
        }else{
          $url='';
        }


        //echo $node->get('created')->getValue()[0]['value'];
        $giorno = date('d', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $anno = date('Y', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $mese = date('F', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));

        $mese = str_ireplace($nen, $nit, $mese);

        if(!empty($node->get('field_categoria_posizione')->getValue()[0]['target_id'])){
          $posizione = Term::load($node->get('field_categoria_posizione')->getValue()[0]['target_id']);

          if(!empty($posizione->get('field_immagine_categoria')->getValue()[0]['target_id'])){
            $image_pos = File::load($posizione->get('field_immagine_categoria')->getValue()[0]['target_id']);
            $url_pos= $image_pos->getFileUri();
          }else{
            $url_pos = '';
          }
          $name_pos = $posizione->get('name')->getValue()[0]['value'];
          $tid_pos =  $posizione->get('tid')->getValue()[0]['value'];
        }else{
          $name_pos='';
          $tid_pos='';
          $url_pos  = '';
        }

        if(!empty($node->get('field_url')->getValue()[0]['value'])){
          $alias = $node->get('field_url')->getValue()[0]['value'];
          $target="_blank";
        }else{
          $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
          $target="";
        }

        /*$image = File::load($node->get('field_image')->getValue()[0]['target_id']);
        $url = $image->getFileUri();*/

        if(!empty($node->get('field_sottotitolo')->getValue()[0]['value'])){
          $body = $node->get('field_sottotitolo')->getValue()[0]['value'];
        }else{
          $body = '';
        }

        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'target' => $target,
          'title' => substr($node->get('title')->getValue()[0]['value'],0,30).'...',
          'url' => $url,
          'giorno' => $giorno,
          'mese' => $mese,
          'anno' => $anno,
          'url_pos' =>$url_pos,
          'name_pos' => $name_pos,
          'tid_pos' => $tid_pos,
          'timestamp_created' =>$node->get('created')->getValue()[0]['value'],
          'body' => $body,
        );
      }
    }else{
      $articoli_lista = [];
    }

    return $articoli_lista;
  }

  public function get_article_json(){


    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('field_data_di_pubblicazione' , 'DESC')
    ->condition('status', 1)
    ->condition('field_formazione', 0,'=');

    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $node = Node::load($value);
        $giorno = date('d', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $anno = date('Y', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $mese = date('m', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $data = $giorno.'-'.$mese.'-'.$anno;
        //echo $data;
        /*  $timestamp  = strtotime($data);
        echo date('m/d/Y', $timestamp);*/
        $articoli_lista[]=array(
          'nid' => $value,
          'timestamp_created' => strtotime($data),
        );
      }
    }else{
      $articoli_lista = [];
    }

    return json_encode($articoli_lista);
  }

  public function get_news_marketing($range){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('field_data_di_pubblicazione' , 'DESC')
    ->condition('status', 1)
    ->condition('field_formazione', 0);
    if($range != 0){ $query->range(0,$range); }

    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $alias = "";
        $target ="";
        $node = Node::load($value);
        if($node->get('field_url')->getValue()[0]['value']){
          $alias = $node->get('field_url')->getValue()[0]['value'];
          $target="_blank";
        }else{
          $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
          $target="";
        }

        if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }elseif ($node->get('field_immagine_url')->getValue()[0]['value']){
          $url = $node->get('field_immagine_url')->getValue()[0]['value'];
        }else{
          $url='';
        }

        $giorno = date('d', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $anno = date('Y', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $mese = date('m', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));

        $mese = str_ireplace($nen, $nit, $mese);



        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'ente' => $node->get('field_ente')->getValue()[0]['value'],
          'url' => $url,
          'giorno' => $giorno,
          'mese' => $mese,
          'anno' => $anno,
          'target' => $target,
        );
      }
    }else{
      $articoli_lista = [];
    }

    return $articoli_lista;
  }

  public function get_news_formazione($range){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('field_data_di_pubblicazione' , 'DESC')
    ->condition('status', 1)
    ->condition('field_formazione', 1);
    if($range != 0){ $query->range(0,$range); }
    if($_GET["ente"]) $query = \Drupal::entityQuery('node')->condition('field_ente',$_GET["ente"]);

    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $alias = "";
        $target ="";
        $node = Node::load($value);
        if($node->get('field_url')->getValue()[0]['value']){
          $alias = $node->get('field_url')->getValue()[0]['value'];
          $target="_blank";
        }else{
          $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
          $target="";
        }

        if(!empty($node->get('field_image')->getValue()[0]['target_id'])){
          $image = File::load($node->get('field_image')->getValue()[0]['target_id']);
          $url = $image->getFileUri();
        }elseif ($node->get('field_immagine_url')->getValue()[0]['value']){
          $url = $node->get('field_immagine_url')->getValue()[0]['value'];
        }else{
          $url='';
        }

        $giorno = date('d', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $anno = date('Y', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $mese = date('m', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));

        $mese = str_ireplace($nen, $nit, $mese);



        $articoli_lista[]=array(
          'nid' => $value,
          'alias' => $alias,
          'title' => $node->get('title')->getValue()[0]['value'],
          'url' => $url,
          'giorno' => $giorno,
          'mese' => $mese,
          'anno' => $anno,
          'target' => $target,
          'ente' => $node->get('field_ente')->getValue()[0]['value'],
        );
      }
    }else{
      $articoli_lista = [];
    }

    return $articoli_lista;
  }

  public function get_news_marketing_json(){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');
//    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('field_data_di_pubblicazione' , 'DESC')
    ->condition('status', 1)
    ->condition('field_formazione', 0)
    ->condition('field_opportunita', 0);

    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $node = Node::load($value);
        $giorno = date('d', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $anno = date('Y', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $mese = date('m', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $data = $giorno.'-'.$mese.'-'.$anno;
        //echo $data;
        /*  $timestamp  = strtotime($data);
        echo date('m/d/Y', $timestamp);*/
        $articoli_lista[]=array(
          'nid' => $value,
          'timestamp_created' => strtotime($data),
        );
      }
    }else{
      $articoli_lista = [];
    }
    return json_encode($articoli_lista);
  }

  public function get_news_formazione_json(){
    $nen = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September','October', 'November', 'December');
    $nit = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');

    $query = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->sort('field_data_di_pubblicazione' , 'DESC')
    ->condition('status', 1)
    ->condition('field_formazione', 1);

    $articoli = $query->execute();

    if(count($articoli)>0){
      foreach ($articoli as $value) {
        $node = Node::load($value);
        $giorno = date('d', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $anno = date('Y', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $mese = date('m', strtotime($node->get('field_data_di_pubblicazione')->getValue()[0]['value']));
        $data = $giorno.'-'.$mese.'-'.$anno;
        //echo $data;
        /*  $timestamp  = strtotime($data);
        echo date('m/d/Y', $timestamp);*/
        $articoli_lista[]=array(
          'nid' => $value,
          'timestamp_created' => strtotime($data),
        );
      }
    }else{
      $articoli_lista = [];
    }
    return json_encode($articoli_lista);
  }

  public function get_opportunita_feed(){
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'opportunity')
    ->sort('created' , 'DESC')
    ->condition('status', 1);

    if($_GET["categoria"]!="") $query->condition('field_categoria',$_GET["categoria"]);
    if($_GET["city"]!="") $query->condition('field_citta',$_GET["city"],'CONTAINS');
    if($_GET["superficie"]=="500-") $$query->condition('field_superficie',$_GET["superficie"],"<=");
    if($_GET["superficie"]=="500+"){
      $query->condition('field_superficie','500',">=");
      $query->condition('field_superficie','1000',"<=");
    }
    if($_GET["superficie"]=="1000+"){
      $query->condition('field_superficie','1000',">=");
      $query->condition('field_superficie','2000',"<=");
    }
    if($_GET["superficie"]=="2000+"){
      $query->condition('field_superficie','2000',">=");
    }

    $articoli = $query->execute();

    if(isset($articoli)){
      foreach ($articoli as $value) {
        $node = Node::load($value);
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$value);
        if(!empty($node->get('field_categoria')->getValue()[0]['value'])){
          $categoria = $node->get('field_categoria')->getValue()[0]['value'];
        }else{
          $categoria = '';
        }

        if(!empty($node->get('field_citta')->getValue()[0]['value'])){
          $città = $node->get('field_citta')->getValue()[0]['value'];
        }else{
          $città = '';
        }

        if(!empty($node->get('field_superficie')->getValue()[0]['value'])){
          $superficie = $node->get('field_superficie')->getValue()[0]['value'];
        }else{
          $superficie = '';
        }

        if(!empty($node->get('body')->getValue()[0]['value'])){
          $body = $node->get('body')->getValue()[0]['value'];
        }else{
          $body = '';
        }

        if(!empty($node->get('field_url')->getValue()[0]['value'])){
          $url = $node->get('field_url')->getValue()[0]['value'];
        }else{
          $url = '';
        }

        if(!empty($node->get('field_immagine')->getValue()[0]['value'])){
          $immagine = $node->get('field_immagine')->getValue()[0]['value'];
        }else{
          $immagine = '';
        }

        $opportunita[]=array(
          'alias' => $alias,
          'nid' => $value,
          'title' =>$node->get('title')->getValue()[0]['value'],
          'immagine_url' =>$node->get('field_immagine_url')->getValue()[0]['value'],
          'categoria' => $categoria,
          'citta' => $città,
          'superficie' => $superficie,
          'body' => $body,
          'url' => $url,
          'image' => $immagine
        );
      }
    }else{
      $opportunita = [];
    }
    return $opportunita;

  }

}
