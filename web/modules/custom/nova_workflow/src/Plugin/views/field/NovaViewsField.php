<?php

namespace Drupal\nova_workflow\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nova_views_field")
 */
class NovaViewsField extends FieldPluginBase {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->currentDisplay = $view->current_display;
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // First check whether the field should be hidden if the value(hide_alter_empty = TRUE) /the rewrite is empty (hide_alter_empty = FALSE).
    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $arg = $this->view->args;
    $tids = explode('+', $arg[0]);
    $node = $values->_entity;
    $node_tids = [];
    $node_multi_tids = $node->field_categorie_del_servizio->getValue();
    foreach ($node_multi_tids as $node_term_id) {
      $node_tids[] = $node_term_id['target_id'];
    }
    // $term_objects = $node->field_categorie_del_servizio->referencedEntities();
    $text = '';

    foreach ($tids as $tid) {
      // cerchiamo quale fra i valori del param della view è presente nel nodo
      $search = in_array($tid, $node_tids);
      if ($search) {
        $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($tid);
        $parents = array_reverse($parents);
        array_shift($parents);
        // foreach ($parents as $parent) {
        //   $text .= $parent->label() . ' > ';
        // }
      }
    }

    $first_step = isset($parents[0]) ? $parents[0]->label() : null;
    $second_step = isset($parents[1]) ? $parents[1]->label() : null;
    // save for node
    $session = \Drupal::request()->getSession();
    $session->set('nova.workflow.first_step', $first_step);
    $session->set('nova.workflow.second_step', $second_step);

    return  [
      '#theme' => 'nova_workflow_pseudofield',
      '#first_step' => $first_step,
      '#second_step' => $second_step,
    ];
  }

  /**
   * Recursive array search
   *
   * @param mixed $needle
   * @param array $haystack
   * @return void
   */
  private function recursiveArraySearch($needle, $haystack) {
    foreach($haystack as $key => $value) {
      $current_key = $key;
      if($needle === $value || (is_array($value) && $this->recursiveArraySearch($needle, $value) !== false)) {
        return $current_key;
      }
    }
    return false;
  }

}
