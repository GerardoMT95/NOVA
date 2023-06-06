<?php


namespace Drupal\sirac_sso;


interface SiracServiceInterface {

  /**
   * @param $attributes
   *
   * @return mixed
   * exception in caso di login non possibile
   */
  public function siracSSO($attributes);

  /**
   * @return null
   * processa l'effettivo logout da sso sirac
   *
   */
  public function logout();

  /**
   * @return boolean
   * true se l'utente si è autenticato con sirac
   */
  public function isAuthenticated();

  public function getAttributes();

  /**
   * Returns an array of available SSO attributes.
   *
   * @return array
   *   An array of available SSO attributes.
   */
  public static function getSSOAttributes();
}
