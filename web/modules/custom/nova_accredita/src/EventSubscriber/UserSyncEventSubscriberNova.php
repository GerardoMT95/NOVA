<?php
namespace Drupal\nova_accredita\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\sirac_sso\Event\SSOEvents;
use Drupal\sirac_sso\Event\SSOUserSyncEvent;
use Egulias\EmailValidator\EmailValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSyncEventSubscriberNova implements EventSubscriberInterface
{

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

//        $this->setStringTranslation($translation);
    }

    public static function getSubscribedEvents()
    {
        $events[SSOEvents::USER_SYNC][] = ['onUserSyncNova'];
        return $events;
    }

    /**
     * @param SSOUserSyncEvent $event
     * @return void
     */
    public function onUserSyncNova(SSOUserSyncEvent $event) {

        $account = $event->getAccount();
        $fatal_errors = [];

        $account_changed = false;
        if(!$account->hasRole('utente_nova') && !$account->hasRole('amministratore_portale') && !$account->hasRole('administrator') && !$account->hasRole('content_admin') && !$account->hasRole('stakholder')){
            $account->addRole('utente_nova');
            $account_changed = true;
        }

        $attributes = $event->getAttributes();
        //dump($account->get('field_nome'));
        //dump($attributes);
        // dump($account->field_nome->Value);
        
        if (empty($account->field_nome->value)) {
            $account->set('field_nome', $attributes['comge_nome']);
            $account->set('field_cognome', $attributes['comge_cognome']);
            $account_changed = true;
        }
        if (empty($account->field_codice_fiscale->value)) {
            $account->set('field_codice_fiscale', $attributes['comge_codicefiscale']);
            $account_changed = true;
        }

        if ($account_changed) $event->markAccountChanged();
        
        if ($account->isNew()) {
            nova_accredita_user_presave($account);
        }

        /**
         * @var Drupal\Core\Logger\LoggerChannel $logger
         */
        // \Drupal::service('logger.factory')->get("php")->error("Login/registrazione utente intercettato");
    }

}