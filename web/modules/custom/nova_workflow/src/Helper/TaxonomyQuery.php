<?php

namespace Drupal\nova_workflow\Helper;

/**
 *
 */
class TaxonomyQuery {

  /**
   * Get terms from vocabulary.
   *
   * @param string $vid
   * @param int $parent
   * @param int $level
   * @param bool $full
   * @return array
   */
  public static function getTaxonomyLevel(string $vid, int $parent, int $level = NULL, bool $full = TRUE) {
    // Load the taxonomy tree using values.
    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    return $manager->loadTree(
      $vid,       // The taxonomy term vocabulary machine name.
      $parent,    // The "tid" of parent using "0" to get all.
      $level,     // Get terms from 1st and 2nd levels.
      $full       // Get full load of taxonomy term entity.
    );
  }

  public static function stepOptions(string $vid, int $parent, $step = 0) {
    $results_terms = [];

    switch ($step) {
      case 1:
        $tree = self::getTaxonomyLevel($vid, $parent, 1, FALSE);
        foreach ($tree as $term) {
          $results_terms[$term->tid] = $term->name;
        }
        break;

      case 2:
        $tree = self::getTaxonomyLevel($vid, $parent, 1, FALSE);
        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($parent);

        foreach ($tree as $term) {
          $results_terms[$term->tid] = '<div class="field_tip">' . $parent->label() . '</div><p>' . $term->name . '</p>';
        }
        break;

      default:
        $tree = self::getTaxonomyLevel($vid, $parent, $level, TRUE);
        break;
    }

    return $results_terms;
  }

}
