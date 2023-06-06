<?php

namespace Drupal\nova_accredita\Form;

use Drupal\Core\Form\FormStateInterface;

class ConfigurationForm extends \Drupal\Core\Form\FormBase
{
    private $config;

    public function __construct()
    {
        $this->config = \Drupal::configFactory()->get('nova_accredita.settings');
    }


    /**
     * @inheritDoc
     */
    public function getFormId()
    {
        return 'configuration_form';
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

//        $file =  \Drupal\file\Entity\File::load(409);
//        dump($file->getFilename());
//        dump($file->delete());
//        exit;
        $form['launch_import']   =   [
            '#markup'   =>  '<h1>Configurazione batch accreditamento</h1><a href="/nova/company-import" target="_blank" class="button">Processa i file</a><br><br>'
        ];

//        $media = \Drupal\media_entity\Entity\Media::load($id);
//        'nova_accredita_pmi_media'

        $form["nova_accredita_xls_pmi"] = array(
            '#type' => 'managed_file',
            '#title' => "File XLS PMI innovative",
            '#default_value' => [$this->config->get('nova_accredita_xls_pmi')],
            //'#format' => $bg_attestato01["format"],
            '#required' => false,
            '#upload_location' => 'public://nova_accredita/',
            '#description' => 'File excel contenente le PMI innovative ',
            '#upload_validators'  => array(
                'file_validate_extensions' => array('xls'),
                'file_validate_size' => array(25600000),
            ),
//            '#process' => array('nova_accredita_my_file_element_process')
        );

        $form["nova_accredita_xls_startup"] = array(
            '#type' => 'managed_file',
            '#title' => "File XLS startUp innovative",
            '#default_value' => [$this->config->get('nova_accredita_xls_startup')],
            //'#format' => $bg_attestato01["format"],
            '#required' => false,
            '#upload_location' => 'public://nova_accredita/',
            '#description' => 'File excel contenente le startUp innovative ',
            '#upload_validators'  => array(
                'file_validate_extensions' => array('xls'),
                'file_validate_size' => array(25600000),
            ),
        );

        $form['submit'] = [
            '#type' => 'submit',
            '#weight' => '12',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }
//
//    function nova_accredita_my_file_element_process($element, &$form_state, $form) {
//        $element = file_managed_file_process($element, $form_state, $form);
//        $element['upload_button']['#access'] = FALSE;
//        return $element;
//    }
    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // TODO: Implement submitForm() method.
        $xls_pmi = $form_state->getValue('nova_accredita_xls_pmi');
        $xls_startup = $form_state->getValue('nova_accredita_xls_startup');
        $config = \Drupal::configFactory()->getEditable('nova_accredita.settings');

//        dump($xls_pmi[0]);
//        dump($config->get('nova_accredita_xls_pmi'));
//        exit;

        $file_pmi =  \Drupal\file\Entity\File::load($xls_pmi[0]);
        if(gettype($file_pmi) == 'object') {
            $file_pmi->setPermanent(); //FILE_STATUS_PERMANENT
            $file_pmi->save();
            $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
            $file_pmi->setOwner($user);


            $config->set('nova_accredita_xls_pmi', $xls_pmi[0])->save();

//            $config->set('nova_accredita_xls_pmi', $file->getFilename())->save();
        }else{
            $config->set('nova_accredita_xls_pmi', '')->save();
        }

        $file_startup =  \Drupal\file\Entity\File::load($xls_startup[0]);
        if(gettype($file_startup) == 'object') {
            $file_startup->setPermanent(); //FILE_STATUS_PERMANENT
            $file_startup->save();
            $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
            $file_startup->setOwner($user);

            $config->set('nova_accredita_xls_startup', $xls_startup[0])->save();
        }else{
            $config->set('nova_accredita_xls_startup', '')->save();
        }

        \Drupal::messenger()->addMessage('Le tue preferenze sono state salvate.');
    }
}