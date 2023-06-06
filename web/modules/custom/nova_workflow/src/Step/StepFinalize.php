<?php

namespace Drupal\nova_workflow\Step;

use Drupal\nova_workflow\Button\StepFinalizePreviousButton;
use Drupal\views\Views;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StepFinalize.
 *
 * @package Drupal\nova_workflow\Step
 */
class StepFinalize extends BaseStep {

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return StepsEnum::STEP_FINALIZE;
  }

  /**
   * {@inheritdoc}
  */
  public function getButtons() {
    return [
   new StepFinalizePreviousButton(),
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements($tid = NULL, $form_state = NULL) {
    $output = '';
    $tids = [];
    $selected_text = [];
    $areas = $form_state->getValue('areas');

//    $form['areas'] = [
//      '#markup' => '<div class="selected_area">' . join('</div><div class="selected_area">', $areas) . '</div>',
//    ];

    if ($selected = $form_state->getValue('cluster')) {
      foreach ($selected as $value) {
        if ($value !=0) {
          $tids[] = $value;
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($value);
          // $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($value);
          $selected_text[] = $term->name->value;
        }
      }
      $args = implode('+', $tids);

      $view = Views::getView(NOVA_VIEW_RESULT);
      $view->setDisplay(NOVA_VIEW_RESULT_DISPLAY);
      // contextual relationship filter
      $view->setArguments([$args]);
      $view->preExecute();
      $view->execute();
      $rendered = $view->render();
      $output = \Drupal::service('renderer')->render($rendered);
    }

    $form['selected_pretext'] = [
      '#markup' => '<p class="mt-4">
    Questi sono i Servizi che hai selezionato!<br>
    Approfondisci i contenuti.</p>',
    ];

//    foreach ($selected_text as $key => $text) {
//      $form['selected_' . $key] = [
//        '#markup' => '<div class="box-select">' . $text . '</div>',
//       ];
//    }

    $form['service_typology'] = [
      '#title' => '<div class="title-workflow"><span>Step 3 di 3: </span> FILTRA I TUOI SERVIZI</div>',
      '#type' => 'radios',
      '#options' => ['none'=> 'Mostra tutti',634 => 'Gratuito', 635 => 'A pagamento'],
    ];

    $form['#attached']['library'][] = 'nova_workflow/nova_workflow';

    $form['views_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'views-wrapper'],
    ];

    $form['views_wrapper']['completed'] = [
      '#markup' => $output,
    ];
// $form['post_contact'] = [
//      '#markup' => '<div class="button-contact"><a href="/node/add/richiesta_nuovi_servizi?display=azienda" class="btn btn-contact">CONTATTACI</a></div>',
//   '#weight' => 35,
//     ];
    return $form;
  }

  /**
   * Ajax callback for the color dropdown.
   */
  public function updateTipology(array $form, FormStateInterface $form_state) {
    return $form['views_wrapper'];
  }
}
