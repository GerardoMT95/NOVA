<?php

namespace Drupal\nova_workflow\Button;

/**
 * Class BaseButton.
 *
 * @package Drupal\nova_workflow\Button
 */
abstract class BaseButton implements ButtonInterface {

  /**
   * {@inheritdoc}
   */
  public function ajaxify() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmitHandler() {
    return FALSE;
  }

}
