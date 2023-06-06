<?php

namespace Drupal\nova_workflow\Step;

use Drupal\nova_workflow\Button\StepTwoFinishButton;
use Drupal\nova_workflow\Button\StepTwoPreviousButton;
use Drupal\nova_workflow\Validator\ValidatorRequired;
use Drupal\nova_workflow\Helper\TaxonomyQuery;

/**
 * Class StepTwo.
 *
 * @package Drupal\nova_workflow\Step
 */
class StepTwo extends BaseStep {

  private $tid;

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return StepsEnum::STEP_TWO;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      new StepTwoPreviousButton(),
      new StepTwoFinishButton($this->tid),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements($tid = NULL, $form_state = NULL, $store = NULL) {
    if ($tid) {
    $this->tid = $tid;
      $store->set('tid', $this->tid);
    }
    else {
      $tid = $store->get('tid');
    }
    $options = [];
    $selected_text = [];
    $area_values = $store->get('1');

    if ($form_state->getValue('area')) {
      $selected = $form_state->getValue('area');
    }
    else {
      $selected = $area_values['area'];
    }

    if ($selected) {
      foreach ($selected as $subtid => $area) {
        if ($area != 0) {
          $options += TaxonomyQuery::stepOptions(NOVA_VOCABULARY, $subtid, 2);
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($subtid);
          $selected_text[] = $term->name->value;
        }
      }

      $form['areas'] = [
        '#type' => 'value',
        '#value' => $selected_text,
      ];
    }
    $form['selected'] = [
      '#markup' => 'Hai selezionato: <strong>' . join(', ', $selected_text) . '</strong>!<br><p>Scegli gli <strong>Ambiti</strong> che ritieni più interessanti e clicca su <strong>Continua</strong> in fondo per visualizzare i servizi associati.',
    ];

    $form['cluster'] = [
      '#type' => 'checkboxes',
      '#title' => t("<span>Step 2 di 3: </span> Scegli il tuo ambito"),
      // '#required' => TRUE,
      // '#required_error' => 'Seleziona un ambito',
      '#options' => $options,
      '#attributes' => [
        'class' => ['step-2'],
        'data-nova'  => 'novacheckbox' . $tid,
    ],
      '#default_value' => isset($this->getValues()['cluster']) ? $this->getValues()['cluster'] : [],
    ];
    $form['post_areas'] = [
      '#markup' => '<div class=""> Non hai trovato quello che ti serve?<br>Scegli altre <strong>Aree di interesse</strong>.</div>',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames() {
    return [
      'cluster',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators() {
    return [
      'cluster' => [
        new ValidatorRequired("Per favore, seleziona un ambito"),
      ],
    ];
  }

}

