<?php

namespace Drupal\nova_accredita\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\nova_accredita\Services\EnrollmentService;

/**
 * Implements the RequestForm form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class EnrollmentForm extends FormBase
{

    /**
     * Nome macchina del bundle usato per la richiesta di accreditamento.
     */
    const BUNDLE = 'impresa';
    const ID_REGIONE_LIGURIA = 25939;
    const ID_NAZIONE_ITALIA = 25054;

    /**
     * @var \Drupal\Core\Session\AccountInterface
     */
    private $currentUser;

    /**
     * The mail manager.
     *
     * @var \Drupal\Core\Mail\MailManagerInterface
     */
    protected $mailManager;

    /**
     * The language manager.
     *
     * @var \Drupal\Core\Language\LanguageManagerInterface
     */
    protected $languageManager;

    /**
     * The enrollment service.
     *
     * @var \Drupal\nova_accredita\Services\EnrollmentService
     */
    private $enrollmentService;

    private $fields = [
        'field_partita_iva',
        'field_codice_fiscale_impresa',
        'field_tipo_di_impresa',
        'field_codice_fiscale_legale_rapp',
    ];

    //718 validata, 719 non validata, 720 attesa validazione, 721 validata in automatico
    //verificare che combacino con gli ID della tassonomia
    private $stati_accreditamento = [];

//'validato' => 718,
//'non_validato' => 719,
//'in_validazione' => 720,
//'validato_automaticamente' => 721,

    /**
     * Constructs a \Drupal\nova_accredita\Form\EnrollmentForm.
     *
     * @param \Drupal\Core\Session\AccountInterface $current_user
     */
    public function __construct(AccountInterface $current_user, MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager, EnrollmentService $enrollmentService){
        $this->currentUser = $current_user;
        $this->mailManager = $mail_manager;
        $this->languageManager = $language_manager;
        $this->enrollmentService = $enrollmentService;


        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('stati_richiesta_accreditamento');
        foreach ($terms as $term) {
            $nome_pulito = strtolower(str_replace(' ', '_', $term->name));
            $this->stati_accreditamento[$nome_pulito] = (int)$term->tid;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        $form = new static(
            $container->get('current_user'),
            $container->get('plugin.manager.mail'),
            $container->get('language_manager'),
            $container->get('nova_accredita.enrollment')
        );
        return $form;
    }

    /**
     * Build the form.
     *
     * @param array $form
     *   Default form array structure.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Object containing current form state.
     *
     * @return array
     *   The render array defining the elements of the form.
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
//        dump($this->enrollmentService);
//        die;

        $uid = \Drupal::currentUser()->id();
        $current_user = User::load($uid);

        //Get the EntityFormDisplay (i.e. the default Form Display) of this content type
        $node = \Drupal::service('entity_type.manager')->getStorage('node')->create(array('type' => 'impresa'));
        $entity_form_display = \Drupal::service('entity_type.manager')->getStorage('entity_form_display')->load('node.impresa.default');

        $form['#parents'] = [];

        $form['field_privacy_policy'] = [
            '#type' => 'textarea',
            '#title' => '',
            '#attributes' => array('readonly' => 'readonly'),
            '#resizable ' => false,
            '#default_value' => 'Informativa privacy
Informativa ex art. 13 Regolamento UE 2016/679 per il Trattamento dei Dati Personali ottenuti presso l\'interessato

Titolare del trattamento
La informiamo che, ai sensi del Regolamento UE 2016/679 (infra: "Regolamento"),i Dati Personali, se da Lei forniti e raccolti ai fini dell\'inserimento di un nuovo Progetto/Prodotto, saranno trattati in qualità di Titolare deltrattamento ("Titolare") dal Comune di Genova, legale rappresentantesindaco pro tempore, con sede in Via Garibaldi 9, Genova 16124. Dati dicontatto: email: urpgenova@comune.genova.it; pec: comunegenova@postemailcertificata.it


Responsabile del trattamento
L\'Ente ha designato il Responsabile del trattamento dei dati personali(Data protection officer), contattabile nei seguenti modi: tel. 0105572665; mail:DPO@comune.genova.it; pec: DPO@comge.postecert.it


Tipi di dati oggetto del trattamento e fonte dalla quale hanno origine gli stessi
Il Titolare tratterà i dati personali raccolti (nome, cognome, codicefiscale, email) per l\'accreditamento dell\'Impresa all\'interno del servizio VetrinaImprese.
I dati personali in questione (nome e cognome del legale rappresentante) sonoottenuti anche attraverso altri sistemi, quale quello della Camera di Commercio.

Finalità e base giuridica e del trattamento
I Dati Personali saranno trattati sulla base di quanto previsto dall\'art.6, paragrafo 1, lett. e) del Regolamento (il trattamento è necessario per l\'esecuzione di un compito di interesse pubblico o connesso all\'esercizio di pubblici poteri di cui è investito il titolare del trattamento) per la seguente finalità:
- Accreditamento Impresa all\'interno del servizio Vetrina Imprese.

Destinatari dei dati personali
I Dati Personali non saranno condivisi con nessun destinatario.
Il Titolare non trasferisce i Dati Personali al di fuori dello Spazio Economico Europeo.

Conservazione dei dati personali
I Dati Personali sono conservati per il periodo necessario per il raggiungimento delle finalità per le quali sono stati raccolti.

I suoi diritti privacy (ex artt. 15 e ss. del Regolamento)
Lei ha il diritto di chiedere al Titolare, in qualunque momento, l\'accesso ai suoi Dati Personali, la rettifica o la cancellazione degli stessi o di opporsi al loro trattamento, ha diritto di richiedere la limitazione del trattamento nei casi previsti dall\'art. 18 del Regolamento, nonché di ottenere in un formato strutturato, di uso comune e leggibile da dispositivo automatico i dati che la riguardano, nei casi previsti dall\'art. 20 del Regolamento.

    Le richieste vanno rivolte al DPO (Data Protection Officer)

In ogni caso lei ha sempre diritto di proporre reclamo al Garante per la Protezione dei Dati Personali, ai sensi dell\'art. 77 del Regolamento, qualora ritenga che il trattamento dei suoi dati sia contrario alla normativa in vigore.'
        ];

        $form['field_certificazione_dati'] = [
            '#type' => 'textarea',
            '#resizable ' => false,
            '#attributes' => array(
                'readonly' => 'readonly',
                'class' => 'smallArea',
            ),
            '#default_value' => 'Dichiaro l\'autenticità dei dati di seguito inseriti.',
        ];
        
        $form['field_privacy_policy_acceptance'] = [
            '#type' => 'checkboxes',
//            '#title' => $this->t('L\'accettazione è obbligatoria'),
            '#required' => true,
            '#options' => array(1 => t('SÌ ACCETTO <span class="redMandatory">*</span>')),
            '#default_value' => !empty($form_data['field_privacy_policy_acceptance']) ? 1 : '',
            '#attributes' => [
                'required' => 'required',
                'aria-required' => 'true',
            ]
        ];

        $form['field_certificazione_dati_acceptance'] = [
            '#type' => 'checkboxes',
//            '#title' => $this->t('L\'accettazione è obbligatoria'),
            '#required' => true,
            '#options' => array(1 => t('Confermo <span class="redMandatory">*</span>')),
            '#default_value' => !empty($form_data['field_certificazione_dati_acceptance']) ? 1 : '',
            '#attributes' => [
                'required' => 'required',
                'aria-required' => 'true',
            ]
        ];


        $form['field_user_nome'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nome'),
            '#attributes' => array('readonly' => 'readonly'),
            '#default_value' => $current_user->get('field_nome')->getValue()[0]['value'],
        ];

        $form['field_user_cognome'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Cognome'),
            '#attributes' => array('readonly' => 'readonly'),
            '#default_value' => $current_user->get('field_cognome')->getValue()[0]['value'],
        ];

        $form['field_user_cf'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Codice fiscale'),
            '#attributes' => array('readonly' => 'readonly'),
            '#default_value' => $current_user->get('field_codice_fiscale')->getValue()[0]['value'],
        ];
        $form['field_user_legale_rappresentante'] = [
            '#type' => 'radios',
            '#title' => 'Sono legale rappresentante',
            '#options' => array('0' => 'No', '1' => 'Si'),
            '#required' => true,
            '#default_value' => $form_data['field_user_legale_rappresentante'],
            '#attributes' => [
                'required' => 'required',
                'aria-required' => 'true',
            ]
        ];

        $form['field_user_mail'] = [
            '#type' => 'email',
            '#title' => $this->t('Email'),
            '#attributes' => array('readonly' => 'readonly'),
            '#default_value' => $current_user->getEmail(),
        ];

        $form['field_user_telefono'] = [
            '#type' => 'tel',
            '#title' => $this->t('Telefono'),
            '#default_value' => $current_user->get('field_numero_di_telefono')->getValue()[0]['value'],
        ];

        if ($widget = $entity_form_display->getRenderer('field_ragione_sociale_')) { //Returns the widget class
            $items = $node->get('field_ragione_sociale_'); //Returns the FieldItemsList interface
            $items->filterEmptyItems();

            $form['field_ragione_sociale_'] = $widget->form($items, $form, $form_state); //Builds the widget form and attach it to your form
            $form['field_ragione_sociale_']['#access'] = $items->access('edit');
            $form['field_ragione_sociale_']['#required'] = true;
        }

        if ($widget = $entity_form_display->getRenderer('field_partita_iva')) { //Returns the widget class
            $items = $node->get('field_partita_iva'); //Returns the FieldItemsList interface
            $items->filterEmptyItems();

            $form['field_partita_iva'] = $widget->form($items, $form, $form_state); //Builds the widget form and attach it to your form
            $form['field_partita_iva']['#access'] = $items->access('edit');
            $form['field_partita_iva']['#required'] = true;
        }

        if ($widget = $entity_form_display->getRenderer('field_codice_fiscale_impresa')) { //Returns the widget class
            $items = $node->get('field_codice_fiscale_impresa'); //Returns the FieldItemsList interface
            $items->filterEmptyItems();

            $form['field_codice_fiscale_impresa'] = $widget->form($items, $form, $form_state); //Builds the widget form and attach it to your form
            $form['field_codice_fiscale_impresa']['#access'] = $items->access('edit');
        }

        if ($widget = $entity_form_display->getRenderer('field_tipo_di_impresa')) { //Returns the widget class
            $items = $node->get('field_tipo_di_impresa'); //Returns the FieldItemsList interface
            $items->filterEmptyItems();

            $form['field_tipo_di_impresa'] = $widget->form($items, $form, $form_state); //Builds the widget form and attach it to your form
            $form['field_tipo_di_impresa']['#access'] = $items->access('edit');
            $form['field_tipo_di_impresa']['#required'] = true;
//            $form['field_tipo_di_impresa']['#attributes'] = [
//                'required' => 'required',
//                'aria-required' => 'true',
//            ];
            //$form['field_tipo_di_impresa']['#description'] = 'È possibile selezionare un solo valore fatta eccezione per SPIN OFF';
        }

//        $form['field_no_sede_liguria'] = [
//            '#type' => 'checkboxes',
//            '#title' => 'Comune',
//            '#options' => array(true => t('Nessuna sede/unità locale in Liguria')),
//            '#default_value' => !empty($form_data['field_no_sede_liguria']) ? $form_data['field_no_sede_liguria'] : '',
//        ];

        $lista_stati = [];
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('nazioni');
        foreach ($terms as $item) {
            $lista_stati[$item->tid] = $item->name;
        }

        $form['field_nazione'] = [
            '#type' => 'select',
            '#title' => 'Stato',
            "#empty_option"=>t('- Select -'),
            '#options' => $lista_stati,
            '#default_value' => !empty($form_data['field_nazione']) ? $form_data['field_nazione'] : self::ID_NAZIONE_ITALIA,
        ];

        $lista_regioni = $lista_comuni = [];
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('regioni_comuni');
        foreach ($terms as $item) {
            if(empty($item->parents[0])){
                $lista_regioni[$item->tid] = $item->name;
            }else{
                $lista_comuni[$item->tid] = $item->name;
            }
        }

//        $form['field_regione'] = [
//            '#type' => 'select',
//            '#title' => 'Regioni',
//            "#empty_option"=>t('- Select -'),
//            '#options' => $lista_regioni,
//            '#default_value' => !empty($form_data['field_regione']) ? $form_data['field_regione'] : '',
//            '#ajax' => [
//                'callback' => '::salva_regione_sessione',
//                'event' => 'change',
//                'wrapper' => 'data-wrapper'
//            ],
//        ];

//        $form['field_regione_sel'] = [
//            '#type' => 'hidden',
//            '#title' => '',
//            '#attributes' => array('id' => 'data-wrapper'),
//        ];

        $form['field_comune'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Comune'),
            '#autocomplete_route_name' => 'nova_accredita.autocomplete_comuni',
        ];

        // Group submit handlers in an actions element with a key of "actions" so
        // that it gets styled correctly, and so that other modules may add actions
        // to the form. This is not required, but is convention.
        $form['actions'] = [
            '#type' => 'actions',
        ];

        // Add a submit button that handles the submission of the form.
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Invia la richiesta'),
        ];

        return $form;
    }

    // Ajax callback.
    public function salva_regione_sessione(array &$form, FormStateInterface $form_state) {
        $field_regione = $form_state->getValue('field_regione');
        $_SESSION['regione_corrente'] = $field_regione;
//        return $form_state['regione_corrente'];
        return [$field_regione];
    }
    /**
     * Getter method for Form ID.
     *
     * The form ID is used in implementations of hook_form_alter() to allow other
     * modules to alter the render array built by this form controller. It must be
     * unique site wide. It normally starts with the providing module's name.
     *
     * @return string
     *   The unique ID of the form defined by this class.
     */
    public function getFormId()
    {
        return 'nova_accredita_enrollment_form';
    }

    /**
     * Implements form validation.
     *
     * The validateForm method is the default method called to validate input on
     * a form.
     *
     * @param array $form
     *   The render array of the currently built form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Object describing the current state of the form.
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $field_ragione_sociale_ = $form_state->getValue('field_ragione_sociale_');
        $partita_iva = $form_state->getValue('field_partita_iva');
        $codice_fiscale_impresa = $form_state->getValue('field_codice_fiscale_impresa');
        $field_tipo_di_impresa = $form_state->getValue('field_tipo_di_impresa');
        $privacy_policy_acceptance = $form_state->getValue('field_privacy_policy_acceptance');
        $certificazione_dati_acceptance = $form_state->getValue('field_certificazione_dati_acceptance');
        $comune = $form_state->getValue('field_comune');
        $regione = '';

        if(!empty($comune)){
            $properties['name'] = $comune;
            $properties['vid'] = 'regioni_comuni';
            $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);

//            dump($terms);
            if(!empty($terms)){
                $term = reset($terms);
                $term_id = (int) $term->id();
                if( !empty($term->id()) && is_numeric($term_id) ){
                    $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term_id);
                    $parent = reset($parent);

                    if(!empty($parent) && $parent->id()){
                        $regione = (int)$parent->id();
                    }
                }
            }
        }

        if(empty($privacy_policy_acceptance[1])) {
            $form_state->setErrorByName('field_privacy_policy_acceptance', $this->t('L\'accettazione della privacy policy è obbligatoria.'));
        }

        if(empty($certificazione_dati_acceptance[1])) {
            $form_state->setErrorByName('field_certificazione_dati_acceptance', $this->t('L\'accettazione della certificazione è obbligatoria.'));
        }

        if(empty($partita_iva[0]['value']) && empty($codice_fiscale_impresa[0]['value'])){
            $form_state->setErrorByName('field_partita_iva', $this->t('La partita IVA o il codice fiscale sono obbligatori.'));
            $form_state->setErrorByName('field_codice_fiscale_impresa', $this->t('La partita IVA o il codice fiscale sono obbligatori.'));
        }

        if(empty($field_tipo_di_impresa)) {
            $form_state->setErrorByName('field_tipo_di_impresa', $this->t('Il campo tipo impresa è obbligatorio.'));
        }

        // validazioni
        $impresa = $this->enrollmentService->getImpresa($partita_iva[0]['value'], $codice_fiscale_impresa[0]['value']);

        if(empty($impresa)) {
            //si tratta di una nuova azienda
            //verifico se è definita come ligure o meno
            if($regione == self::ID_REGIONE_LIGURIA){

                $campo_identificativo = $partita_iva[0]['value'];
                //popolo eventuali dati ARIS
                $this->enrollmentService->getArisData($campo_identificativo);
//                $this->enrollmentService->getArisMultiData($partita_iva[0]['value'], $codice_fiscale_impresa[0]['value']);
                $this->enrollmentService->getArisMultiData($campo_identificativo);

                if(!$this->enrollmentService->hasArisValue()){

                    $has_error = false;
                    if(empty($field_ragione_sociale_[0]['value'])){
                        $form_state->setErrorByName('field_ragione_sociale_', $this->t('Stai inserendo una nuova impresa, e la ragione sociale è obbligatoria.'));
                        $has_error = true;
                    }

                    if(empty($codice_fiscale_impresa[0]['value'])){
                        $form_state->setErrorByName('codice_fiscale_impresa', $this->t('Stai inserendo una nuova impresa, il codice fiscale è obbligatorio.'));
                        $has_error = true;
                    }

                    if(!$has_error) {
                        \Drupal::messenger()->addWarning('La tua impresa non risulta iscritta al Registro imprese della Camera di commercio. Riceverai una email dall’utente amministratore.');
                    }
                }
            }
        }else{
            $this->validateAccreditamento($impresa, $form_state);
        }

    }

    /**
     * @param $impresa
     * @param $form_state
     * @return void
     */
    private function validateAccreditamento($impresa, $form_state){
        $stato_accreditamento = (int)$impresa->get('field_stato_accreditamento')->getString();
        $id_nodo = (int)$impresa->id();
        $data_accreditamento = $impresa->get('field_data_richiesta_accreditame')->getString();
        $parere_accreditamento = $impresa->get('field_parere_accreditamento')->getString();

        if($stato_accreditamento == $this->stati_accreditamento['richiesta_in_attesa_di_validazione']){
            //in attesa di accreditamento
            $form_state->setErrorByName('fields_partita_iva', $this->t('E’ già stata fatta richiesta di accreditamento per questa azienda in '.$data_accreditamento.'. La richiesta è in attesa di validazione. Riceverai una email quando la tua impresa risulterà accreditata') );

        }elseif($stato_accreditamento == $this->stati_accreditamento['richiesta_non_validata']){
            //non validata
            $form_state->setErrorByName('fields_partita_iva', $this->t('È già stata fatta richiesta di accreditamento per questa azienda. La tua richiesta non è stata accettata per il seguente motivo:"').$parere_accreditamento.'"');

        }elseif($stato_accreditamento==$this->stati_accreditamento['richiesta_validata'] || $stato_accreditamento==$this->stati_accreditamento['richiesta_validata_in_automatico_dal_sistema']){
            //già validata
            \Drupal::messenger()->addStatus(['#markup' => 'È stata già validata la richiesta di accreditamento per questa azienda. 
Accedi con SPID <a href="/node/'.$id_nodo.'/edit">Modifica impresa</a> per aggiornare i tuoi dati.']);

            $form_state->setErrorByName('fiels_partita_iva', 'Azienda esistente.');
        }else{
            //Definisco un errore generico
//            $form_state->setErrorByName('field_codice_fiscale_impresa', 'L\'impresa esiste già nel sistema.');
            $form_state->setErrorByName('fields_partita_iva', 'Non risulta alcuna richiesta di accreditamento per questa azienda.');
        }


    }

    /**
     * Implements a form submit handler.
     *
     * The submitForm method is the default method called for any submit elements.
     *
     * @param array $form
     *   The render array of the currently built form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Object describing the current state of the form.
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->createRequest($form_state);
//        \Drupal::messenger()->addMessage('L\'impresa è stata registrata al sistema.');
    }

    /**
     * Si crea il nodo IMPRESA.
     */
    private function createRequest(\Drupal\Core\Form\FormStateInterface $form_state)
    {
        $uid = \Drupal::currentUser()->id();
        $current_user = User::load($uid);

        $field_ragione_sociale_ = $form_state->getValue('field_ragione_sociale_');
        $partita_iva = $form_state->getValue('field_partita_iva');
        $user_mail = $form_state->getValue('field_user_mail');
        $codice_fiscale_impresa = $form_state->getValue('field_codice_fiscale_impresa');
        $field_tipo_di_impresa = $form_state->getValue('field_tipo_di_impresa');
        $field_user_legale_rappresentante = $form_state->getValue('field_user_legale_rappresentante');
        $field_nazione = $form_state->getValue('field_nazione');
//        if(!empty($form_state->getValue('field_nazione'))){
//            $field_nazione[] = [
//                'target_id' => $form_state->getValue('field_nazione')
//            ];
//        }
        $comune = $form_state->getValue('field_comune');

//        dump($field_nazione);
//        die;
        // definisco il comune come quello inserito da form
        //eventualmente verrà sovrascritto da aris
//        $comune = trim(preg_replace("/\([^)]+\)/","", $field_comune_form));

        $nome_utente = $current_user->get('field_nome')->getValue()[0]['value'];
        $cognome_utente = $current_user->get('field_cognome')->getValue()[0]['value'];
        $cf_utente = $current_user->get('field_codice_fiscale')->getValue()[0]['value'];
        $mail_utente = $current_user->getEmail();
        $telefono_utente = $current_user->get('field_numero_di_telefono')->getValue()[0]['value'];

        $indirizzo = $civico = $mission = $attivita = $field_nome_legale_rappresentante = $field_cognome_legale_rappresenta = $field_codice_fiscale_legale_rapp = '.';
        if($field_user_legale_rappresentante){
            $field_nome_legale_rappresentante = $nome_utente;
            $field_cognome_legale_rappresenta = $cognome_utente;
            $field_codice_fiscale_legale_rapp = $cf_utente;
        }

        $dati_nodo = $new_cf_abilitati = [];
        $nota_accreditamento = $msg_innovative ='';

        if($this->enrollmentService->hasArisValue()) {

            $isActive = true;
            if(strpos($this->enrollmentService->aris_xml->i_stato_attivita, 'I') !== false){
                $isActive = false;
            }

            $codice_fiscale_aris = (string)$this->enrollmentService->aris_xml->c_fiscale_impresa;
            $partita_iva_aris = (string)$this->enrollmentService->aris_xml->partita_iva;
            $denominazione_impresa = (string)$this->enrollmentService->aris_xml->denominazione_sede;
            $add_link = false;
            $mail_message = '';

            //esiste in ARIS
//            dump($this->enrollmentService->isSameRichiedente($cf_utente));
//            exit;
            if($this->enrollmentService->isSameRichiedente($cf_utente)){

                //IL RICHIEDENTE COMBACIA
                foreach ($this->enrollmentService->aris_xml->resultUnitaLocali as $ul) {
                    //identifico la SEDE
                    if (!empty($ul->item[0]) && $ul->item[0]->t_localizzazione == 'SE') {
                        $comune = (string)$this->enrollmentService->aris_xml->resultUnitaLocali->item[0]->comune;
                        $indirizzo = $this->enrollmentService->aris_xml->resultUnitaLocali->item[0]->c_via . ' ' . $this->enrollmentService->aris_xml->resultUnitaLocali->item[0]->via;
                        $civico = (string)$this->enrollmentService->aris_xml->resultUnitaLocali->item[0]->n_civico;

                        break;
                    }
                }


                if($this->enrollmentService->isImpresaInnovativa($codice_fiscale_aris) && $isActive) {
                    //È INNOVATIVA E ATTIVA SU ARIS
                    $add_link = true;

                    $new_cf_abilitati[] = array(
                        "value" => $current_user->get('field_codice_fiscale')->getString()
                    );
                    //il richiedente combacia
                    $stato_accreditamento = $this->stati_accreditamento['richiesta_validata_in_automatico_dal_sistema'];
                    $nota_accreditamento = 'Impresa presente su Sezione Speciale Camera di commercio';


                    $admin_mail_message = 'A seguito della richiesta di accreditamento fatta da '.$nome_utente.', '.$cognome_utente.' '.$cf_utente.' per l\'impresa ('.$denominazione_impresa.', '.$partita_iva_aris.'/'.$codice_fiscale_aris.') la richiesta di accreditamento è stata accettata in automatico';

                    $mail_message = 'La tua richiesta di accreditamento fatta per l\'impresa '.$denominazione_impresa.' è stata accettata.
                    <br><br>
                    Accedi con SPID e completa l\'inserimento dati per l\'impresa '.$denominazione_impresa.' a questo {{LINK_IMPRESA}}.<br>
                    Puoi aggiungere ulteriori soggetti abilitati all\'inserimento dati.
                    N.B. Anche tali soggetti dovranno essere muniti di credenziali SPID.
                    <br><br>
                    Cordiali saluti,
                    il team di Vetrina imprese';

                    $msg_innovative = '"La tua impresa è stata accreditata. Riceverai conferma al tuo indirizzo email.                    
                    Prosegui con l\'inserimento dei tuoi dati {{LINK_IMPRESA}}"';

                }else if($this->enrollmentService->isImpresaInnovativa($codice_fiscale_aris) && !$isActive) {
                    //È INNOVATIVA MA INATTIVA SU ARIS
                    $add_link = true;

                    $new_cf_abilitati[] = array(
                        "value" => $current_user->get('field_codice_fiscale')->getString()
                    );
                    //il richiedente combacia
                    $stato_accreditamento = $this->stati_accreditamento['richiesta_in_attesa_di_validazione'];
                    $nota_accreditamento = 'Impresa presente su Sezione Speciale Camera di commercio. Azienda inattiva su ARIS';


                    $admin_mail_message = 'È stata presentata una nuova richiesta di accreditamento, è in attesa di validazione.';
                    $mail_message = 'La tua richiesta è stata presa in carico dal team di Vetrina Imprese. A breve riceverai l\'esito dell\'accreditamento al tuo indirizzo email.';

                    $msg_innovative = 'L\'impresa '.$denominazione_impresa.' è presente sul Registro imprese, risulta iscritta alla Sezione Speciale della Camera di Commercio ma è inattiva.
                    Riceverai l’esito dell’operazione di accreditamento.';

                }else{
                    //NON È INNOVATIVA
                    $stato_accreditamento = $this->stati_accreditamento['richiesta_in_attesa_di_validazione'];
                    $nota_accreditamento = 'Impresa presente su Registro Imprese';
                    if(!$isActive){
                        $nota_accreditamento .= '. Azienda inattiva su ARIS';
                    }

                    $admin_mail_message = 'È stata presentata una nuova richiesta di accreditamento, è in attesa di validazione.';
                    $mail_message = 'La tua richiesta è stata presa in carico dal team di Vetrina Imprese. A breve riceverai l\'esito dell\'accreditamento al tuo indirizzo email.';

                    $msg_innovative = 'L\'impresa '.$denominazione_impresa.' è presente sul Registro imprese ma non risulta iscritta alla Sezione Speciale della Camera di Commercio.
                    Riceverai l’esito dell’operazione di accreditamento.';
                }
            }else {
                //il richiedente NON COMBACIA
                $stato_accreditamento = $this->stati_accreditamento['richiesta_in_attesa_di_validazione'];

                $nota_accreditamento = 'Impresa presente su Registro Imprese ma associata ad altro titolare';
                if(!$isActive){
                    $nota_accreditamento .= '. Azienda inattiva su ARIS';
                }

                $mail_message = 'È stata presentata una nuova richiesta di accreditamento da parte di un azienda iscritta al Registro imprese delle camere di commercio liguri, ma risulta associata ad altri titolari.<br>Cordiali saluti il team di Vetrina Imprese';

                $msg_innovative = 'La tua impresa è iscritta al Registro imprese delle Camere di Commercio Liguri, ma risulta associata ad altri titolari. Riprova ad accedere con lo SPID del legale rappresentante dell\'azienda '.$denominazione_impresa.'. Se non riesci comunque ad accreditarti contatta bluedistrict@job-centre-srl.it';

                $admin_mail_message = 'È stata presentata una nuova richiesta di accreditamento da parte di un azienda iscritta al Registro imprese delle Camere di Commercio Liguri, ma risulta associata ad altri titolari.';

                if($this->enrollmentService->isImpresaInnovativa($codice_fiscale_aris)){
                    $nota_accreditamento = 'Impresa presente su Sezione Speciale Camera di Commercio, ma associata ad altro titolare';
                    if(!$isActive){
                        $nota_accreditamento .= '. Azienda inattiva su ARIS';
                    }

                    $mail_message = 'È stata presentata una nuova richiesta di accreditamento da parte di un impresa iscritta alla Sezione Speciale della Camera di Commercio, ma risulta associata ad altri titolari.<br>Cordiali saluti il team di Vetrina Imprese';

                    $msg_innovative = 'La tua impresa è iscritta alla Sezione Speciale della Camera di Commercio, ma risulta associata ad altri titolari. Riprova ad accedere con lo SPID del legale rappresentante dell\'azienda '.$denominazione_impresa.'. Se non riesci comunque ad accreditarti contatta bluedistrict@job-centre-srl.it';

                    $admin_mail_message = 'È stata presentata una nuova richiesta di accreditamento da parte di un impresa iscritta alla Sezione Speciale della Camera di Commercio, ma risulta associata ad altri titolari.';
                }
            }

            $dati_nodo = [
                'type' => self::BUNDLE,
                'title' => $denominazione_impresa, // definire come creare il titolo
                'field_ragione_sociale_' => $denominazione_impresa,
                'field_partita_iva' => $partita_iva_aris,
                'field_codice_fiscale_impresa' => $codice_fiscale_aris,
                'field_email_contatto_riferimento' => $user_mail,
                'field_stato_accreditamento' => $stato_accreditamento,
                'field_nota_accreditamento' => $nota_accreditamento,
                'field_codice_comune' => $comune,
                'field_stato' => $field_nazione,
                'field_indirizzo_della_sede' => $indirizzo,
                'field_numero_civico' => $civico,
                'field_tipo_di_impresa' => $field_tipo_di_impresa,
                'field_cognome_legale_rappresenta' => $field_cognome_legale_rappresenta,
                'field_nome_legale_rappresentante' => $field_nome_legale_rappresentante,
                'field_codice_fiscale_legale_rapp' => $field_codice_fiscale_legale_rapp,
                'field_data_richiesta_accreditame' => date('Y-m-d'),
                'field_nome_richiedente' => $nome_utente,
                'field_cognome_richiedente' => $cognome_utente,
                'field_codice_fiscale_richiedente' => $cf_utente,
                'field_email_richiedente' => $mail_utente,
                'field_telefono_richiedente' => $telefono_utente,
                'field_codici_fiscali_abilitati' => $new_cf_abilitati,
                'field_mission' => $mission,
                'field_descrizione_delle_attivita' => $attivita,
            ];

        }else{
            //NON esiste in ARIS
            $stato_accreditamento = $this->stati_accreditamento['richiesta_in_attesa_di_validazione'];
            $nota_accreditamento = 'La tua impresa non risulta iscritta al Registro imprese delle camere di commercio liguri. Verrai contattato dall’utente amministratore.';

            $mail_message = 'È stata presentata una nuova richiesta di accreditamento da parte di un azienda non iscritta alle camere di commercio liguri.<br>Cordiali saluti il team di Vetrina Imprese';

            $msg_innovative = 'La tua impresa non risulta iscritta al Registro Imprese delle Camere di Commercio Liguri. 
Verrai contattato dall’utente amministratore.';

            $admin_mail_message = 'È stata presentata una nuova richiesta di accreditamento da parte di un azienda non iscritta alle Camere di Commercio Liguri.';

            // creo nodo richiesta
            $dati_nodo = [
                'type' => self::BUNDLE,
                'title' => $field_ragione_sociale_[0]['value'], // definire come creare il titolo
                'field_ragione_sociale_' => $field_ragione_sociale_[0]['value'], // definire come creare il titolo
                'field_partita_iva' => $partita_iva[0]['value'],
                'field_codice_fiscale_impresa' => $codice_fiscale_impresa[0]['value'],
                'field_email_contatto_riferimento' => $user_mail,
                'field_stato_accreditamento' => $stato_accreditamento,
                'field_nota_accreditamento' =>  $nota_accreditamento,
                'field_codice_comune' => $comune,
                'field_stato' => $field_nazione,
                'field_indirizzo_della_sede' => $indirizzo,
                'field_numero_civico' => $civico,
                'field_tipo_di_impresa' => $field_tipo_di_impresa,
                'field_cognome_legale_rappresenta' => $field_cognome_legale_rappresenta,
                'field_nome_legale_rappresentante' => $field_nome_legale_rappresentante,
                'field_codice_fiscale_legale_rapp' => $field_codice_fiscale_legale_rapp,
                'field_data_richiesta_accreditame' => date('Y-m-d'),
                'field_nome_richiedente' => $nome_utente,
                'field_cognome_richiedente' => $cognome_utente,
                'field_codice_fiscale_richiedente' => $cf_utente,
                'field_email_richiedente' => $mail_utente,
                'field_telefono_richiedente' => $telefono_utente,
                'field_mission' => $mission,
                'field_descrizione_delle_attivita' => $attivita,
            ];
        }

        //salvo il nodo azienda
        $node = Node::create($dati_nodo);
        $id_nodo = $this->saveNode($node);

        if($add_link){

            //$mail_message .= 'Accedi con spid e completa l’inserimento dei dati per l\'impresa ('.$denominazione_impresa.') a questo <a href="/node/'.$id_nodo.'/edit">link</a> <br>Cordiali saluti il team di Vetrina Imprese';

            if(strpos($msg_innovative, '{{LINK_IMPRESA}}') !== false){
                $msg_innovative = str_replace('{{LINK_IMPRESA}}', '<a href="/node/'.$id_nodo.'/edit">Modifica impresa</a>', $msg_innovative);
            }

            if(strpos($mail_message, '{{LINK_IMPRESA}}') !== false){
                $mail_message = str_replace('{{LINK_IMPRESA}}', '<a href="/node/'.$id_nodo.'/edit">Modifica impresa</a>', $mail_message);
            }
        }

        if(!empty($msg_innovative)){
            \Drupal::messenger()->addStatus(['#markup' => $msg_innovative]);
        }else{
            //messaggio di default
            \Drupal::messenger()->addStatus(['#markup' => 'La tua richiesta è stata presa in carico dal team di Vetrina Imprese. A breve riceverai l\'esito dell\'accreditamento al tuo indirizzo email.']);
        }

//        if($stato_accreditamento == $this->stati_accreditamento['richiesta_validata_in_automatico_dal_sistema']){
            //mando la mail solo quando mi trovo un accreditamento automatico
        if(!empty($mail_message)){
            $this->sendMail($form_state, $mail_message);
        }
        if(!empty($admin_mail_message)){
            $this->sendMail($form_state, $admin_mail_message, true);
        }

        //carico utente corrente
        $uid = \Drupal::currentUser()->id();
        $current_user = User::load($uid);
        //devo salvare l'informazione nel profilo utente e dentro a sè stessa...
//        $azienda = \Drupal::entityTypeManager()->getStorage('node')->load($id_nodo);
//        $new_cf_abilitati = $azienda->get('field_codici_fiscali_abilitati')->getValue();
//        $new_cf_abilitati[] = array(
//            "value" => $current_user->get('field_codice_fiscale')->getString()
//        );
//
//        $azienda->set('field_codici_fiscali_abilitati', $new_cf_abilitati);
//        $azienda->set('field_id_impresa', $id_nodo);
//        $azienda->save();

        //nel caso non lo abbia aggiungo un ruolo specifico all'utente
        if(!$current_user->hasRole('impresa')){
            $current_user->addRole('impresa');
            $current_user->save();
        }

        //...e dentro al profilo utente
        //Lascio il codice come storico, tuttavia questo salvataggio di informazioni sul profilo utente è effettuato nell'hook "nova_accredita_entity_update".
//        $new_field_impresa = $current_user->get('field_impresa')->getValue();
//        $new_field_id_impresa = $current_user->get('field_id_impresa')->getValue();
//        $new_field_impresa[] = array(
//            "target_id" => $id_nodo
//        );
//        $new_field_id_impresa[] = array(
//            "value" => $id_nodo
//        );
//
//        $current_user->set('field_impresa', $new_field_impresa);
//        $current_user->set('field_id_impresa', $new_field_id_impresa);
//        $current_user->save();

        return $id_nodo;
    }

    /**
     * Save node.
     *
     * @param Node $node
     * @return integer
     */
    private function saveNode(Node $node)
    {
        $node->uid = $this->currentUser()->id();
        $node->langcode = 'it';
        $node->setUnpublished();
        $node->save();

        return $node->id();
    }

    /**
     * Send mail.
     *
     * @return void
     */
    private function sendMail(\Drupal\Core\Form\FormStateInterface $form_state, $message, $toAdmin=false)
    {
        $field_ragione_sociale_ = $form_state->getValue('field_ragione_sociale_');
        $cf_utente = $form_state->getValue('field_user_cf');
        $codice_fiscale_impresa = $form_state->getValue('field_codice_fiscale_impresa');
        $nome = $form_state->getValue('field_user_nome');
        $cognome = $form_state->getValue('field_user_cognome');

        // All system mails need to specify the module and template key (mirrored
        // from hook_mail()) that the message they want to send comes from.
        $module = 'nova_accredita';
        $key = 'enrollment_message';

        if($toAdmin){
            //toDO sarà l'utente UNO?
            $destinatario = User::load(1);
        }else{
            //carico utente corrente
            $uid = \Drupal::currentUser()->id();
            $destinatario = User::load($uid);
        }

        $destinatario->hasRole('stakholder');

        // Specify 'to' and 'from' addresses.
        $to = $destinatario->getEmail();
        $from = $this->config('system.site')->get('mail');

        // "params" loads in additional context for email content completion in
        // hook_mail(). In this case, we want to pass in the values the user entered
        // into the form, which include the message body in $form_values['message'].
//        $params = $form_values;
        $params['message'] = $message;

        // The language of the e-mail. This will one of three values:
        // - $account->getPreferredLangcode(): Used for sending mail to a particular
        //   website user, so that the mail appears in their preferred language.
        // - \Drupal::currentUser()->getPreferredLangcode(): Used when sending a
        //   mail back to the user currently viewing the site. This will send it in
        //   the language they're currently using.
        // - \Drupal::languageManager()->getDefaultLanguage()->getId: Used when
        //   sending mail to a pre-existing, 'neutral' address, such as the system
        //   e-mail address, or when you're unsure of the language preferences of
        //   the intended recipient.
        $language_code = $this->languageManager->getDefaultLanguage()->getId();

        // Whether or not to automatically send the mail when we call mail() on the
        // mail manager. This defaults to TRUE, and is normally what you want unless
        // you need to do additional processing before the mail manager sends the
        // message.
        $send_now = TRUE;
        // Send the mail, and check for success. Note that this does not guarantee
        // message delivery; only that there were no PHP-related issues encountered
        // while sending.
        $result = $this->mailManager->mail($module, $key, $to, $language_code, $params, $from, $send_now);
        if ($result['result'] == TRUE) {
            $this->messenger()->addMessage($this->t('Your message has been sent.'));
        } else {
            $this->messenger()->addMessage($this->t('There was a problem sending your message and it was not sent.'), 'error');
        }
    }
}