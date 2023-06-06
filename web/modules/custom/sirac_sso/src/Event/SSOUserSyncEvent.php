<?php

namespace Drupal\sirac_sso\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a sso user sync event for event listeners.
 */
class SSOUserSyncEvent extends Event {

  /**
   * The Drupal user account.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * The sirac attributes received from the sirac reverse proxy.
   *
   * Single values are typically represented as one-element arrays.
   *
   * @var array
   */
  protected $attributes;

  /**
   * A flag indicating that the account was changed.
   *
   * @var bool
   */
  protected $accountChanged;

  /**
   * Constructs a sirac user sync event object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The Drupal user account.
   * @param array $attributes
   *   The sirac attributes received from the sirac reverse proxy.
   */
  public function __construct(UserInterface $account, array $attributes) {
    $this->account = $account;
    $this->attributes = $attributes;
  }

  /**
   * Gets the Drupal user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The Drupal user account.
   */
  public function getAccount() {
    return $this->account;
  }

  /**
   * Sets the altered Drupal user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The Drupal user account.
   */
  public function setAccount(UserInterface $account) {
    $this->account = $account;
  }

  /**
   * Gets the sirac attributes.
   *
   * @return array
   *   The sirac attributes received from the reverse proxy.
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * Marks the user account as changed.
   *
   * This is the way for event subscribers to make sure user account gets saved.
   * This method exists because subscribers must never save new accounts by
   * themselves. (Non-new accounts could be saved by the event subscribers but
   * just calling markAccountChanged() will keep the account from being saved
   * multiple times by multiple subscribers.)
   */
  public function markAccountChanged() {
    $this->accountChanged = TRUE;
  }

  /**
   * Checks whether the user account was marked as changed.
   *
   * This is typically done afterwards by the code that dispatches this.
   *
   * @return bool
   *   TRUE if the user account was marked as changed.
   */
  public function isAccountChanged() {
    return $this->accountChanged;
  }

}
