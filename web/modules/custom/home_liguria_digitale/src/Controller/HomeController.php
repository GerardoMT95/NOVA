<?php
namespace Drupal\home_liguria_digitale\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;

/**
 * Class HomeController.
 */
class HomeController extends ControllerBase {

  /**
   * Buildhome.
   */
  public function buildHome() {

    /**
     * @var \Drupal\Core\Render\RendererInterface $renderer
     */
    $renderer = \Drupal::service('renderer');

    $view_1_array = [];
    $view_2_array = [];
    $view_3_array = [];
    $view_4_array = [];
    $view_5_array = [];
    $view_6_array = [];

    $view_1_array = [
      '#type'=>'view',
      '#name'=>'elenco_imprese_home',
      '#display_id' => 'block_1',
      //'#arguments' => ['foo', 'bar'],
      '#embed' => TRUE,
    ];

    $view_2_array = [
      '#type'=>'view',
      '#name'=>'gestione_bandi_new',
      '#display_id' => 'block_1',
      //'#arguments' => ['foo', 'bar'],
      '#embed' => TRUE,
    ];

    $view_3_array = [
      '#type'=>'view',
      '#name'=>'gestione_opportunity_',
      '#display_id' => 'block_1',
      //'#arguments' => ['foo', 'bar'],
      '#embed' => TRUE,
    ];

    $view_4_array = [
      '#type'=>'view',
      '#name'=>'stakehoder_spazi',
      '#display_id' => 'block_1',
      //'#arguments' => ['foo', 'bar'],
      '#embed' => TRUE,
    ];

    $view_5_array = [
      '#type'=>'view',
      '#name'=>'formazione_homepage',
      '#display_id' => 'block_1',
      //'#arguments' => ['foo', 'bar'],
      '#embed' => TRUE,
    ];

    $view_6_array = [
      '#type'=>'view',
      '#name'=>'gestione_news_homepage',
      '#display_id' => 'block_1',
      //'#arguments' => ['foo', 'bar'],
      '#embed' => TRUE,
    ];

    /**
     * @var \Drupal\Core\Block\BlockManager $block_manager
     */
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance('sezionePPT', $config);
    $block_ppt_array = $plugin_block->build();

    $build = [
      '#theme'=>'home_liguria_digitale',
      'vetrina_imprese' => [
        'titolo'=>[
          '#markup' => $this->t('vetrina imprese'),
        ],
        'sottotitolo'=>[
          '#markup' => $this->t('aderisci anche tu!'),
        ],
        'descrizione'=>[
          '#markup' => $this->t('SCOPRI L\'ECOSISTEMA DELL\'INNOVAZIONE!'),
        ],
        'imprese' => $view_1_array,
      ],
      'sezionePPT' => $block_ppt_array,
      'finanziamenti' => $view_2_array,
      'opportunita' => [
        'titolo'=>[
          '#markup' => $this->t('Opportunity Liguria'),
        ],
        'descrizione'=>[
          '#markup' => $this->t('Immobili ed aree pubbliche a disposizione delle imprese'),
        ],
        'elenco' => $view_3_array,
      ],
      'spazi' => [
        'titolo'=>[
          '#markup' => $this->t('spazi'),
        ],
        'descrizione'=>[
          '#markup' => $this->t('Trova lo spazio più adatto per la tua attività '),
        ],
        'elenco' => $view_4_array,
      ],
      'formazione' => [
        'titolo' => [
          '#markup' => $this->t('formazione'),
        ],
        'descrizione'=> [
          '#markup' => $this->t('Migliora le tue performance con le proposte formative in corso'),
        ],
        'elenco' => $view_5_array,
      ],
      'news' => [
        'titolo' => [
          '#markup' => $this->t('magazine'),
        ],
        'descrizione' => [
          '#markup' => $this->t('La sezione dedicata alle ultime notizie sulle imprese, per le imprese!'),
        ],
        'elenco' => $view_6_array,
      ],
    ];
    $build['#attached']['library'][] = 'home_liguria_digitale/home-liguria';

    /* 
    $build = [
      '#theme'=>'home_liguria_digitale',
      'vetrina_imprese' => [
        'titolo'=>[
          '#markup' => $this->t('vetrina imprese'),
        ],
        'sottotitolo'=>[
          '#markup' => $this->t('aderisci anche tu!'),
        ],
        'descrizione'=>[
          '#markup' => $this->t('Imprese innovative, PMI, START UP e SPIN OFF'),
        ],
        'imprese'=> [
          '#markup' => $view_1_array,
        ],
      ],
      'sezionePPT' => $block_ppt_array,
      'finanziamenti' => $view_2_array,
    ];
     */

    return $build;
  }

  public function findServizi(){
    return [
      '#markup' => ''
    ];
  }
}
