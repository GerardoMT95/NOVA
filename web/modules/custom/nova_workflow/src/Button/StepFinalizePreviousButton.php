<?php

namespace Drupal\nova_workflow\Button;

use Drupal\nova_workflow\Step\StepsEnum;

/**
 * Class StepFinalizePreviousButton.
 *
 * @package Drupal\nova_workflow\Button
 */
class StepFinalizePreviousButton extends BaseButton {

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
      '#value' => t('Torna indietro'),
      '#goto_step' => StepsEnum::STEP_TWO,
      '#skip_validation' => TRUE,
      '#limit_validation_errors' => [],
      '#suffix' => '<div class="button-contact"><a href="/node/add/richiesta_nuovi_servizi?display=azienda" class="btn btn-contact">CONTATTACI</a></div>'
    ];
  }

}
