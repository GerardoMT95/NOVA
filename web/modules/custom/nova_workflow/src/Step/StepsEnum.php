<?php

namespace Drupal\nova_workflow\Step;

/**
 * Class StepsEnum.
 *
 * @package Drupal\nova_workflow\Step
 */
abstract class StepsEnum {

  /**
   * Steps used in form.
   */
  const STEP_ONE = 1;
  const STEP_TWO = 2;
  const STEP_FINALIZE = 6;

  /**
   * Return steps associative array.
   *
   * @return array
   *   Associative array of steps.
   */
  public static function toArray() {
    return [
      self::STEP_ONE => 'step-one',
      self::STEP_TWO => 'step-two',
      self::STEP_FINALIZE => 'step-finalize',
    ];
  }

  /**
   * Map steps to it's class.
   *
   * @param int $step
   *   Step number.
   *
   * @return bool
   *   Return true if exist.
   */
  public static function map($step) {
    $map = [
      self::STEP_ONE => 'Drupal\\nova_workflow\\Step\\StepOne',
      self::STEP_TWO => 'Drupal\\nova_workflow\\Step\\StepTwo',
      self::STEP_FINALIZE => 'Drupal\\nova_workflow\\Step\\StepFinalize',
    ];

    return isset($map[$step]) ? $map[$step] : FALSE;
  }

}
