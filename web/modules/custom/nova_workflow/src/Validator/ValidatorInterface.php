<?php

namespace Drupal\nova_workflow\Validator;

/**
 * Interface ValidatorInterface.
 *
 * @package Drupal\nova_workflow\Validator
 */
interface ValidatorInterface {

  /**
   * Returns bool indicating if validation is ok.
   */
  public function validates($value);

  /**
   * Returns error message.
   */
  public function getErrorMessage();

}
