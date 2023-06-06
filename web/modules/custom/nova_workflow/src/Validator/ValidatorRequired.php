<?php

namespace Drupal\nova_workflow\Validator;

/**
 * Class ValidatorRequired.
 *
 * @package Drupal\nova_workflow\Validator
 */
class ValidatorRequired extends BaseValidator {

  /**
   * {@inheritdoc}
   */
  public function validates($value) {
    return is_array($value) ? !empty(array_filter($value)) : !empty($value);
  }

}
