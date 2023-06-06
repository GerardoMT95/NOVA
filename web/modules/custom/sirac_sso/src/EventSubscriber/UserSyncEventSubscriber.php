<?php

namespace Drupal\sirac_sso\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\sirac_sso\Event\SSOEvents;
use Drupal\sirac_sso\Event\SSOUserSyncEvent;
use Drupal\sirac_sso\SiracService;
use Drupal\user\UserInterface;
use Egulias\EmailValidator\EmailValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that synchronizes user properties on a user_sync event.
 *
 * This is basic module functionality, partially driven by config options. It's
 * split out into an event subscriber so that the logic is easier to tweak for
 * individual sites. (Set message or not? Completely break off login if an
 * account with the same name is found, or continue with a non-renamed account?
 * etc.)
 */
class UserSyncEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * A configuration object containing module settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * Construct a new SpidUserSyncSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   * @param \Egulias\EmailValidator\EmailValidator $email_validator
   *   The email validator.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The Translation Manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, TypedDataManagerInterface $typed_data_manager, EmailValidator $email_validator, LoggerInterface $logger, MessengerInterface $messenger, TranslationInterface $translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->emailValidator = $email_validator;
    $this->logger = $logger;
    $this->typedDataManager = $typed_data_manager;
    $this->config = $config_factory->get('sirac_sso.settings');
    $this->messenger = $messenger;

    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SSOEvents::USER_SYNC][] = ['onUserSync'];
    return $events;
  }

  /**
   * Performs actions to synchronize users with Factory data on login.
   *
   * @param \Drupal\sirac_sso\Event\SSOUserSyncEvent $event
   *   The event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function onUserSync(SSOUserSyncEvent $event) {
    // If the account is new, we are in the middle of a user save operation;
    // the current user name is '<authProvider>_AUTHNAME' (as set by externalauth) and
    // e-mail is not set yet.
    $account = $event->getAccount();
    $fatal_errors = [];

    if ($account->isNew()) {
      // Get value from the sirac attribute whose name is configured in the
      // module.
      $name = $this->getAttribute(SiracService::SIRAC_ID_ATTRIBUTE, $event);
      if ($name && $name != $account->getAccountName()) {
        $account->setUsername($name);
        $event->markAccountChanged();
      }
    }

    // Synchronize e-mail.
    if ($account->isNew()) {
      $mail = $this->getAttribute(SiracService::SIRAC_EMAIL_ATTRIBUTE, $event);
      if ($mail) {
        if ($mail != $account->getEmail()) {
          // Invalid e-mail cancels the login / account creation just like name.
          if ($this->emailValidator->isValid($mail)) {

            $account->setEmail($mail);
            $account->set('init', $mail);
            $event->markAccountChanged();
          }
          else {
            $fatal_errors[] = $this->t('Invalid e-mail address @mail', ['@mail' => $mail]);
          }
        }
      }
    }




    if ($fatal_errors) {
      // Cancel the whole login process and/or account creation.
      throw new \RuntimeException('Error(s) encountered during sirac attribute synchronization: ' . implode(' // ', $fatal_errors));
    }

  }

  /**
   * Returns the value of a SSO attribute from a SSOUserSyncEvent.
   *
   * @param string $attribute
   *   The sirac attribute to extract.
   * @param \Drupal\sirac_sso\Event\SSOUserSyncEvent $event
   *   A SSOUserSyncEvent.
   *
   * @return string
   *   The sirac attribute value.
   */
  public function getAttribute($attribute, SSOUserSyncEvent $event) {
    $attributes = $event->getAttributes();

    return $attributes[$attribute];
  }

  /**
   * Sets the value of a user field to the value of a sirac attribute.
   *
   * @param \Drupal\sirac_sso\Event\SSOUserSyncEvent $event
   *   A SpidUserSyncEvent.
   * @param \Drupal\user\UserInterface $account
   *   A user account.
   * @param string $fieldName
   *   The name of the field in the user entity.
   * @param string $attribute
   *   The SSO attribute.
   */
  protected function setFieldValue(SSOUserSyncEvent $event, UserInterface &$account, $fieldName, $attribute) {
    if (($field = $this->config->get($fieldName)) != 'none' && $account->hasField($field)) {
      $account->set($field, $this->getAttribute($attribute, $event));
    }
  }

}
