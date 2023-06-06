<?php

namespace Drupal\nova_workflow\Button;

use Drupal\nova_workflow\Step\StepsEnum;

/**
 * Class StepTwoFinishButton.
 *
 * @package Drupal\nova_workflow\Button
 */
class StepTwoFinishButton extends BaseButton {

  private $tid;

  public function __construct($tid) {
    $this->tid = $tid;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'finish';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => t('CONTINUA'),
      '#goto_step' => StepsEnum::STEP_FINALIZE,
      '#submit_handler' => 'submitValues',
      '#states' => [
        'visible' => [':input[data-nova="novacheckbox' . $this->tid . '"]' => ['checked' => true],]
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmitHandler() {
    return 'submitIntake';
  }

}
