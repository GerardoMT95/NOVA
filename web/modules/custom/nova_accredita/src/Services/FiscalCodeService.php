<?php

namespace Drupal\nova_accredita\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Class FiscalCode
 * @package Drupal\nova_accredita\Services
 */
class FiscalCodeService {

  protected $currentUser;

  /**
   * FiscalCodeService constructor.
   * @param AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * @return string|bool
   */
  public function isPresent() {
    // Load the current user.
    $user = User::load($this->currentUser->id());
    // Get field data from that user.
    $fiscalcode = $user->get('field_codice_fiscale')->value;
    if ($fiscalcode && !is_empty($fiscalcode)) {
      return $fiscalcode;
    }

    return false;
  }

}
