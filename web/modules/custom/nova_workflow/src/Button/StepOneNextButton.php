<?php

namespace Drupal\nova_workflow\Button;

use Drupal\nova_workflow\Step\StepsEnum;

/**
 * Class StepOneNextButton.
 *
 * @package Drupal\nova_workflow\Button
 */
class StepOneNextButton extends BaseButton {

  private $tid;

  public function __construct($tid) {
    $this->tid = $tid;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'next';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => t('CONTINUA'),
      '#goto_step' => StepsEnum::STEP_TWO,
      '#states' => [
        'visible' => [':input[data-nova="novacheckbox' . $this->tid . '"]' => ['checked' => true],]
      ]
    ];
  }

}
