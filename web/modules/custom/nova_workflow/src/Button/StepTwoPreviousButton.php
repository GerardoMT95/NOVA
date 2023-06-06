<?php

namespace Drupal\nova_workflow\Button;

use Drupal\nova_workflow\Step\StepsEnum;

/**
 * Class StepTwoPreviousButton.
 *
 * @package Drupal\nova_workflow\Button
 */
class StepTwoPreviousButton extends BaseButton {

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'previous';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => t('TORNA INDIETRO'),
      '#goto_step' => StepsEnum::STEP_ONE,
      '#skip_validation' => TRUE,
    ];
  }

}
