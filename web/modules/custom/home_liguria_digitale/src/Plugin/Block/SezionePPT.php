<?php
namespace Drupal\home_liguria_digitale\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a PPT block.
 *
 * @Block(
 *  id = "sezionePPT",
 *  admin_label = "sezionePPT",
 * )
 */
class SezionePPT extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager=null;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'sezionePPT';
//    $build['planner5tblock']['#markup'] = 'Implement Planner5TBlock.';
//    $build['#attached']['library'][] = 'bbs_5t_planner_integration/client-5t';
//    $build['#attached']['library'][] = 'bbs_5t_planner_integration/drupal-client-5t';
//    $build['#attached']['library'][] = 'bbs_5t_planner_integration/travelPlannerBuilderLib';
//
//    $settings = [
//      'token' => $this->bbs5tPlannerIntegrationService->getAuthToken(),
//      'pathimg' => \Drupal::moduleHandler()->getModule('bbs_5t_planner_integration')->getPath()
//    ];
//    $build['#attached']['drupalSettings']['planner5T'] = $settings;


    $nodes = $this->getNodiSezionePPT();
    $elements=[];
    foreach ($nodes as $node){
      $tipologiaTerm = $node->field_tipologia_progetto->entity;
      $subArray = [
        '#type'=>'container',
        '#attributes' => array(
          'class' => 'ppt-single-element',
        ),
        'tipologia'=>[
          '#type'=>'container',
          'valore'=>$this->entityTypeManager->getViewBuilder('taxonomy_term')->view($tipologiaTerm),
        ],
        'ppt'=>[
          '#type'=>'container',
          'valore'=>$this->entityTypeManager->getViewBuilder('node')->view($node, 'teaser'),
        ]
      ];
      $elements[]=$subArray;
    }

    $build['titolo'] = [
      '#type'=>'markup',
      '#markup'=>t('Prodotti, tecnologie e progetti innovativi realizzati per te, per tutti!'),
    ];
    $build['elementi_ppt'] = $elements;


    return $build;
  }

  private function getNodiSezionePPT(){

    $ret = [];
    $allTerms = $this->loadAllTipologiePPT();
    foreach ($allTerms as $term){
      $node = $this->getRandomPPT($term->id());
      if($node){
        $ret[] = $node;
      }
    }
    return $ret;
  }


  /**
   * @param $id_tipologia
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\Node|null
   */
  private function getRandomPPT( $id_tipologia ){
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'catalogo_prodotti_progetti_tecno')
      ->condition('status', 1)
      ->condition('field_tipologia_progetto', $id_tipologia)
      ->addTag('sort_by_random')
      ->range(0,1)->execute();
    if($nids){
      return Node::load(reset($nids));
    }

    return null;

  }

  /**
   * @return \Drupal\taxonomy\Entity\Term[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function loadAllTipologiePPT(){

    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    /**
     * @var \Drupal\taxonomy\Entity\Term[] $allTerms
     */
    $allTerms = $termStorage->loadByProperties(['vid'=>'tipo_progetto']);
    return $allTerms;
  }

}
