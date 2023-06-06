<?php

namespace Drupal\ex81\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\Query;
use Drupal\Core\Entity\Query\QueryInterface;

/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';*/

/**
* RegistrationAgency controller.
*/
class AddCollaborator extends FormBase {

  /**
  * Returns a unique string identifying the form.
  *
  * The returned ID should be a unique string that can be a valid PHP function
  * name, since it's used in hook implementation names such as
  * hook_form_FORM_ID_alter().
  *
  * @return string
  *   The unique string identifying the form.
  */
  public function getFormId() {
    return 'add_collaborator';
  }

  /**
  * Form constructor.
  *
  * @param array $form
  *   An associative array containing the structure of the form.
  * @param \Drupal\Core\Form\FormStateInterface $form_state
  *   The current state of the form.
  *
  * @return array
  *   The form structure.
  */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $uid =  $user->get('uid')->value;

    $form['nome'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome *'),
      '#required' => TRUE,
    ];

    $form['cognome'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cognome *'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email *'),
      '#required' => TRUE,
    ];

    /*$form['referente_aziendale'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Referente aziendale'),
      '#description' => $this->t('Gestisce iscrizione dei corsi, libretti formativi e i dati aziendali')
    ];*/

    $form['collaboratore'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Collaboratore'),
      '#description' => $this->t('Gestisce iscrizione dei corsi, libretti formativi ma non può modificare i dati aziendali')
    ];

    $form['utente_registrato'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Utente Registrato'),
      '#description' => $this->t('Vede il proprio libretto formativo. Non può registrarsi ai corsi.')
    ];

    $form['id_user_logged'] = [
      '#type' => 'hidden',
      '#value' => $uid,
    ];


    // Add a submit button that handles the submission of the form.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Aggiungi partecipante'),
    ];

    return $form;

  }

  /**
  * Validate the title and the checkbox of the form.
  *
  * @param array $form
  *   The form.
  * @param \Drupal\Core\Form\FormStateInterface $form_state
  *   The form state.
  */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    /*if($form_state->getValue('referente_aziendale') == 1 && $form_state->getValue('collaboratore') == 1){
      $form_state->setErrorByName('referente_aziendale', $this->t('L\'utente può essere o referente aziendale o collaboratore.'));
    }*/

    //controllo che l'email del refernte sia univoca
    $query_get_equals_email = \Drupal::entityQuery('user')
    ->condition('status', 1)
    ->condition('mail', $form_state->getValue('email'))
    ->execute();
    $load_mail = User::loadMultiple($query_get_equals_email);
    if(!empty($load_mail)){
      $form_state->setErrorByName('email', $this->t('Questo indirizzo email esiste già.'));
    }

  }

  /**
  * Form submission handler.
  *
  * @param array $form
  *   An associative array containing the structure of the form.
  * @param \Drupal\Core\Form\FormStateInterface $form_state
  *   The current state of the form.
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /*echo $form_state->getValue('nome').'<br />';
    echo $form_state->getValue('cognome').'<br />';
    echo $form_state->getValue('email').'<br />';
    echo $form_state->getValue('referente_aziendale').'<br />';
    echo $form_state->getValue('collaboratore').'<br />';
    echo $form_state->getValue('id_user_logged').'<br />';*/

    $user_logged = \Drupal\user\Entity\User::load($form_state->getValue('id_user_logged'));
    $azienda_referente = $user_logged->get('field_azienda')->getValue()[0]['target_id'];

    $arr = explode("@", $form_state->getValue('email'), 2);
    $username = $arr[0];

    $user = User::create(array(
      'name' => $username,
      'mail' => $form_state->getValue('email'),
      'pass' => '',
      'field_nome' =>$form_state->getValue('nome'),
      'field_cognome' =>$form_state->getValue('cognome'),
      'field_azienda' =>$azienda_referente,
      'status' => 1,
    ));

    $user->save();
    if($form_state->getValue('collaboratore') == 1){
      //referente_1_livello
      $user->addRole('collaboratore');
      $user->save();
    }else if($form_state->getValue('utente_registrato') == 1 ){
      $user->addRole('partecipante_registrato');
      $user->save();
    }else{
      $user->addRole('authenticated');
      $user->save();
    }

    _user_mail_notify('register_admin_created',$user);

  }

}
