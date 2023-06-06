<?php

namespace Drupal\sirac_sso\Event;

/**
 * Defines events for the sirac_sso module.
 *
 * @see \Drupal\sirac_sso\Event\SSOUserSyncEvent
 */
final class SSOEvents {


  /**
   * Name of the event fired when a user is synchronized from SSO attributes.
   *
   * The event allows modules to synchronize user account values with SSO
   * attributes passed by the SSO providdr in the authentication response. Basic required
   * properties (username, email) are already synchronized. The event listener
   * method receives a \Drupal\sirac_sso\Event\SSOUserSyncEvent instance. If
   * it changes the account, it should call the event's markAccountChanged()
   * method rather than saving the account by itself.
   *
   * The event is fired after the SSO service validates the SSO provider
   * authentication response but before the Drupal user is logged in. An event
   * subscriber may throw an exception to prevent the login.
   *
   * @Event
   *
   * @see \Drupal\sirac_sso\Event\SSOUserSyncEvent
   *
   * @var string
   */
  const USER_SYNC = 'sirac_sso.user_sync';

}
