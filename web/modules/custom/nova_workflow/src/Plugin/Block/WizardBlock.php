<?php

namespace Drupal\nova_workflow\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\nova_workflow\Form\MultiStepWorkflowForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\nova_workflow\Helper\TaxonomyQuery;

/**
 * Provides a 'Nova wizard: configurable wizard' block.
 *
 * @Block(
 *   id = "nova_wizard",
 *   admin_label = @Translation("Nova workflow: configurable wizard")
 * )
 */
class WizardBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\block\BlockBase::__construct()
   */
  public function defaultConfiguration() {
    return [
      'nova_workflow_tax_start_tid' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\block\BlockBase::buildConfigurationForm()
   * @see \Drupal\block\BlockFormController::form()
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $options = TaxonomyQuery::getTaxonomyLevel(NOVA_VOCABULARY, 0);
    $form['nova_workflow_tax_start_tid'] = [
      '#type' => 'select',
      '#title' => $this->t('Which term'),
      '#description' => $this->t('Selezionare il tipo di wizard.'),
      '#options' => $options,
      '#default_value' => $this->configuration['nova_workflow_tax_start_tid'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['nova_workflow_tax_start_tid']
      = $form_state->getValue('nova_workflow_tax_start_tid');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form_obj = \Drupal::service('class_resolver')->getInstanceFromDefinition('Drupal\nova_workflow\Form\MultiStepWorkflowForm');
    $form_obj->setLocation($this->configuration['nova_workflow_tax_start_tid']);
    $form_renderable = $this->formBuilder->getForm($form_obj, $this->configuration['nova_workflow_tax_start_tid']);
    return $form_renderable;
  }

}
