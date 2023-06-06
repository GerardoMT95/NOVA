<?php

namespace Drupal\nova_workflow\Step;

use Drupal\nova_workflow\Button\StepOneNextButton;
use Drupal\nova_workflow\Validator\ValidatorRequired;
use Drupal\nova_workflow\Helper\TaxonomyQuery;

/**
 * Class StepOne.
 *
 * @package Drupal\nova_workflow\Step
 */
class StepOne extends BaseStep {

    private $tid;

    /**
     * {@inheritdoc}
     */
    protected function setStep() {
        return StepsEnum::STEP_ONE;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtons() {
        return [
            new StepOneNextButton($this->tid),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildStepFormElements($tid = NULL, $form_state = NULL) {
        $this->tid = $tid;
        $options = TaxonomyQuery::stepOptions(NOVA_VOCABULARY, $tid, 1);

        $form['area'] = [
            '#type' => 'checkboxes',
            '#id' => 'area',
            '#title' => t("<span>Step 1 di 3: </span>Scegli la tua area di interesse"),
            '#description' => "<p>Benvenuto!<br>Selezionando la tua Area di interesse, verrai accompagnato in un percorso per individuare i servizi più adatti alle tue esigenze. L'icona <span></span> ti supporterà nella navigazione.</p>"
            . "<p>Se invece sai già cosa cercare utilizza il campo di ricerca libera e scrivi il nome del servizio.</p>",
            '#required' => TRUE,
            '#required_error' => 'Seleziona un\'area',
            '#options' => $options,
            '#attributes' => [
                'class' => ['step-1'],
                'data-nova'  => 'novacheckbox' . $tid,
            ],
            '#default_value' => isset($this->getValues()['area']) ? $this->getValues()['area'] : [],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames() {
        return [
            'area',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsValidators() {
        return [
            'area' => [
                new ValidatorRequired("Per favore seleziona un'area"),
            ],
        ];
    }

}
