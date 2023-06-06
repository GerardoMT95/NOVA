<?php


namespace Drupal\sirac_sso;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\externalauth\ExternalAuth;
use Drupal\sirac_sso\Event\SSOEvents;
use Drupal\sirac_sso\Event\SSOUserSyncEvent;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class SiracService implements SiracServiceInterface {

  public const SIRAC_ID_ATTRIBUTE = 'comge_codicefiscale';
  public const SIRAC_EMAIL_ATTRIBUTE = 'comge_emailAddressPersonale';
  public const SIRAC_NOME_ATTRIBUTE = 'comge_nome';
  public const SIRAC_COGNOME_ATTRIBUTE = 'comge_cognome';

  /**
   * The ExternalAuth service.
   *
   * @var \Drupal\externalauth\ExternalAuth
   */
  protected $externalAuth;

  /**
   * A configuration object containing module settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  private $session;

  private $settings;

  public function getAuthProvider(){
    return "sirac_sso";
  }

  public function siracSSO($sirac_headers) {
    try {
      if ($this->processAuthentication($sirac_headers)) {
        $attributes = $this->getAttributes();

        $nameAttribute = self::SIRAC_ID_ATTRIBUTE;
        $mailAttribute = self::SIRAC_EMAIL_ATTRIBUTE;
        $nomeAttribute = self::SIRAC_NOME_ATTRIBUTE;
        $cognomeAttribute = self::SIRAC_COGNOME_ATTRIBUTE;

        $authName = $attributes[$nameAttribute];
        $email = $attributes[$mailAttribute];

        //MODIFICATO DAVIDE 2022-08-02 PER EVITARE CHE VENGA REPERITA L'INFO SUL MATCH DELL'ACCOUNT DALLA TBL AuthMap, 
        //ma venga invece sempre verificato il matching in base ai dati secondo la logica applicativa
        //$account = $this->externalAuth->load($authName, $this->getAuthProvider());
        $account = null;
        
        if (!$account) {
          $this->logger->debug('No matching local users found for unique '.$this->getAuthProvider().' ID @auth_id.', ['@auth_id' => $authName]);

          // Try to link an existing user: first through a custom event handler,
          // then by name, then by e-mail.
//          $event = new SSOUserLinkEvent($attributes);
//          $this->eventDispatcher->dispatch(SSOEvents::USER_LINK, $event);
//          $account = $event->getLinkedAccount();
//
//          if (!$account) {
//
//          }
          // The linking by name / e-mail cannot be bypassed at this point
          // because it makes no sense to create a new account from the SSO
          // attributes if one of these two basic properties is already in
          // use. (In this case a newly created and logged-in account would
          // get a cryptic machine name because  synchronizeUserAttributes()
          // cannot assign the proper name while saving.)

          $query = \Drupal::entityQuery('user');
          $uids_stakeholder = $query->condition('field_cf_abilitati_stakeholder', $authName)->execute();
          $uids = $query->condition('field_codice_fiscale', $authName)->execute();
          if($uids_stakeholder){
            $uid = reset($uids_stakeholder);
            $account = User::load($uid);
            $this->logger->info('Matching local user @uid found for field_cf_abilitati_stakeholder @name (as provided in a sirac attribute); associating user and logging in.', [
              '@name' => $authName,
              '@uid' => $account->id(),
            ]);
          }
          else if($uids){
            $uid = reset($uids);
            $account = User::load($uid);
            $this->logger->info('Matching local user @uid found for field_codice_fiscale @name (as provided in a sirac attribute); associating user and logging in.', [
              '@name' => $authName,
              '@uid' => $account->id(),
            ]);
          }
          else if ($account_search = $this->entityTypeManager->getStorage('user')
            ->loadByProperties(['name' => $authName])) {
            $account = reset($account_search);
            $this->logger->info('Matching local user @uid found for name @name (as provided in a sirac attribute); associating user and logging in.', [
              '@name' => $authName,
              '@uid' => $account->id(),
            ]);
          }
          // else { //COMMENTO IL MATCH PER EMAIL PERCHÈ DEVE ESSERE ESEGUITO ESCLUSIVAMENTE SUL CODICE FISCALE
          //   if ($account_search = $this->entityTypeManager->getStorage('user')
          //     ->loadByProperties(['mail' => $email])) {
          //     $account = reset($account_search);
          //     $this->logger->info('Matching local user @uid found for e-mail @mail (as provided in a sirac attribute); associating user and logging in.', [
          //       '@mail' => $email,
          //       '@uid' => $account->id(),
          //     ]);
          //   }
          // }

          if ($account) {
            // There is a chance that the following call will not actually link
            // the account (if a mapping to this account already exists from
            // another unique ID). If that happens, it does not matter much to
            // us; we will just log the account in anyway. Next time the same
            // not-yet-linked user logs in, we will again try to link the
            // account in the same way and (falsely) log that we are associating
            // the user.
            //COMMENTATO DAVIDE 2022-08-02
            //$this->externalAuth->linkExistingAccount($authName, $this->getAuthProvider(), $account);
          }
        }

        // If we haven't found an account to link, create one from the SSO
        // attributes.
        $user_created = false;
        if (!$account) {
          // The register() call will save the account. We want to:
          // - add values from the SSO response into the user account;
          // - not save the account twice (because if the second save fails we
          //   do not want to end up with a user account in an undetermined
          //   state);
          // - reuse code (i.e. call synchronizeUserAttributes() with its
          //   current signature, which is also done when an existing user logs
          //   in).
          // Because of the third point, we are not passing the necessary SSO
          // attributes into register()'s $account_data parameter, but we want
          // to hook into the save operation of the user account object that is
          // created by register(). It seems we can only do this by implementing
          // hook_user_presave() - which calls our synchronizeUserAttributes().
          $account = $this->externalAuth->register($authName, $this->getAuthProvider());

          $this->externalAuth->userLoginFinalize($account, $authName, $this->getAuthProvider());
          $user_created = true;
        }
        elseif ($account->isBlocked()) {
          throw new \RuntimeException('Requested account is blocked.');
        }
        else {
          // Synchronize the user account with sirac attributes if needed.
          // Sincronizziamo gli attributi solo se l'utente è stato creato, se esisteva già allora no.
          //if ($user_created) 
          $this->synchronizeUserAttributes($account);

          $this->externalAuth->userLoginFinalize($account, $authName, $this->getAuthProvider());
        }
      }
      else {
        throw new \RuntimeException('Could not authenticate.');
      }


    }
    catch (\Exception $e) {
      $this->setAttributes([]);
      \drupal::logger('sirac_sso')->error($e->getMessage());
      throw new \RuntimeException('Could not authenticate.');
    }
  }

  /**
   * Synchronizes user data with attributes in the sirac request.
   *
   * @param \Drupal\user\UserInterface $account
   *   The Drupal user to synchronize attributes into.
   * @param bool $skip_save
   *   (optional) If TRUE, skip saving the user account.
   */
  public function synchronizeUserAttributes(UserInterface $account, $skip_save = FALSE) {
    // Dispatch a user_sync event.
    $event = new SSOUserSyncEvent($account, $this->getAttributes());
    $this->eventDispatcher->dispatch(SSOEvents::USER_SYNC, $event);

    if (!$skip_save && $event->isAccountChanged()) {
      $account->save();
    }

  }

  /**
   * @param $token
   * verifica gli attributi di autenticazione e salva gli attributi utente
   *
   * @return bool
   */
  private function processAuthentication($attributes){

    $attrs = [];

    foreach ($attributes as $k => $v){

      if(array_key_exists($k, self::getSSOAttributes())){
        $attrs[$k] = $v;
      }
    }
    if(array_key_exists(self::SIRAC_ID_ATTRIBUTE, $attrs)){
      $this->setAttributes($attrs);
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   *
   */
  public function logout() {
    if($this->isAuthenticated()){
      $this->setAttributes([]);

      $base_url = \Drupal::request()->getSchemeAndHttpHost();

      //https://novatest.comune.genova.it/samllogin/Shibboleth.sso/Logout?return=https://novatest.comune.genova.it
      $redirUrl = $base_url."/samllogin/Shibboleth.sso/Logout?return=".$base_url;
      $url = Url::fromUri($redirUrl, [])->toString();

      $response = new RedirectResponse($url);
      $request = \Drupal::request();
      $response->prepare($request);
      // Make sure to trigger kernel events.
      \Drupal::service('kernel')->terminate($request, $response);
      $response->send();
    }
  }

  private function setAttributes(array $attributes){
    return $this->session->set('sirac_attributes', $attributes);
  }
  public function isAuthenticated(){
    return !empty($this->getAttributes());
  }
  public function getAttributes() {
    return $this->session->get('sirac_attributes');
  }

  public function __construct(ExternalAuth $external_auth, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, EventDispatcherInterface $event_dispatcher, Session $session) {
    $this->externalAuth = $external_auth;
    $this->config = $config_factory->get('sirac_sso.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->eventDispatcher = $event_dispatcher;
    $this->session = $session;

    $base_url = \Drupal::request()->getSchemeAndHttpHost();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSSOAttributes() {
    $t = \Drupal::translation();
    return [
      'comge_codicefiscale' => $t->translate('Fiscal number'),
      'comge_nome' => $t->translate('Name'),
      'comge_cognome' => $t->translate('Family name'),
      'comge_sesso' => $t->translate('Gender'),
      'comge_documentoIdentita' => $t->translate('Id card'),
      'comge_dataNascita' => $t->translate('Date of birth'),
      'comge_placeofbirth' => $t->translate('Place of birth'),
      'comge_emailAddress'=> $t->translate('Email'),
      'comge_emailAddressPersonale' => $t->translate('Personal Email'),
      'comge_cellulare' => $t->translate('Mobile phone'),
      'comge_spidcode' => $t->translate('SPID code'),
      'comge_ragionesociale' => $t->translate('Coapny name'),
      'comge_sedelegale' => $t->translate('Company address'),
      'comge_companyfiscalnumber' => $t->translate('Company fiscal number'),
      'comge_partitaIva' => $t->translate('Company VAT number'),
      'comge_indirizzoResidenza' => $t->translate('Registered residence'),
      'comge_indirizzoDomicilio' => $t->translate('Domicile'),
      'comge_capDomicilio' => $t->translate('Domicile CAP'),
      'comge_cittaDomicilio' => $t->translate('Domicile city'),
      'comge_provinciaDomicilio' => $t->translate('Domicile state'),
      'comge_statoDomicilio' => $t->translate('Domicile country'),
    ];
  }

}
