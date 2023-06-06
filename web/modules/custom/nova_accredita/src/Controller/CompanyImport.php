<?php
namespace Drupal\nova_accredita\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Drupal\nova_accredita\Services\EnrollmentService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CompanyImport extends ControllerBase {
    /**
     * Nome macchina del bundle usato per la richiesta di accreditamento.
     */
    const BUNDLE = 'impresa';
    const NEWLINE = "\n";

    /**
     * @var AccountInterface
     */
    protected $currentUser;
    /**
     * The mail manager.
     *
     * @var MailManagerInterface
     */
    protected $mailManager;
    /**
     * The language manager.
     *
     * @var LanguageManagerInterface
     */
    protected $languageManager;
    /**
     * The enrollment service.
     *
     * @var EnrollmentService
     */
    private $enrollmentService;

    /**
     * @var int
     */
    private int $max_log_entries = 90;
    /**
     * @var string
     */
    private $xlsStartup;
    /**
     * @var string
     */
    private $xlsPmi;
    /**
     * @var string
     */
    private $return_html;
    /**
     * @var Drupal\node\Entity\Node
     */
    private $nodo_impresa;

    //718 validata, 719 non validata, 720 attesa validazione, 721 validata in automatico
    //verificare che combacino con gli ID della tassonomia
    //TODO posso recuperarli in automatico?
    private $stati_accreditamento = [];

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
     * @param ContainerInterface $container
     * @return CompanyImport|static
     */
    public static function create(ContainerInterface $container) {
        $form = new static(
            $container->get('current_user'),
            $container->get('plugin.manager.mail'),
            $container->get('language_manager'),
            $container->get('nova_accredita.enrollment')
        );
        return $form;
    }

    /**
     * @return string[]
     */
    public function import(){

        $this->openXlsFile();

        //ciclo i file xls per inserire nuove imprese
        $this->cycleCompanies($this->xlsStartup, true);
        $this->cycleCompanies($this->xlsPmi, false);

        //ora devo ciclare le aziende esistenti per verificare se ci sono aziende NON più innovative
        $this->checkExistingCompaniesInnovative();


        $ret = [
            '#markup' => "<div id='risultati-esecuzione-batch'>" . $this->return_html . "</div>",
        ];
        return $ret;
    }

    /**
     * @param $companiesArr
     * @param $isStartup
     * @return void
     */
    private function cycleCompanies($companiesArr, $isStartup){

        $indice = 0;
        foreach($companiesArr as $company){
            //salto la riga di intestazione
            if($indice>0) {

                //verifico che non sia vuota la piva
                if(!empty($company[2])) {
                    if($this->isNewCompany($company) === true) {

//                        $this->return_html .= '<br>'.$indice.'Devo creare nuova azienda <b>'.$company[0].'</b> - '.$company[2].'<br>';
//                        $this->createNewCompany($company, $isStartup);

                        $this->return_html .= '<br>'.$indice.' l\'azienda <b>'.$company[0].'</b> - '.$company[2].' non esiste nel sistema e quindi la ignoro.<br>';

                    }else{
                        $this->return_html .= $indice.'L\'impresa <b>'.$company[0].'</b> esiste già nel sistema.<br><br>';

                        //stampo info sullo stato di accreditamento
                        $this->validateAccreditamento($this->nodo_impresa, $isStartup);
                        $this->updateCompany($company, $isStartup);

                    }
                }
            }

            //Interrompo dopo X cicli a fine di test
//            if($indice>2){
//                break;
//            }
            $indice++;
        }
    }

    /**
     * @param $impresa
     * @param string $new_log
     * @return string
     */
    private function addQueueLog($impresa, string $new_log){

        $old_log = $impresa->get('field_log_batch')->getString();
        //$count = substr_count($old_log, "\r\n");
        $arrLog = explode("\r\n", $old_log);
//        $this->return_html .= 'Conta log: '.count($arrLog).'<br>';
        $arrLog = array_splice($arrLog, $this->max_log_entries);

        $old_log = implode(self::NEWLINE, $arrLog);

        $new_log = $new_log. self::NEWLINE.$old_log;
        return $new_log;
    }

    /**
     * @param $company
     * @param $isStartup
     * @return void
     */
    private function updateCompany($company, $isStartup){

        //il settore arriva dal file csv
        $settore_file = $this->getCompanySettore($company);
//        $settori_drupal = $this->nodo_impresa->get('field_settori_dell_impresa')->getValue();
        $impresa_non_ligure = $this->nodo_impresa->get('field_impresa_non_ligure')->getValue();
        $this->return_html .= 'Entrato in update company <b>'.$company[0].'</b><br>';

//        if((int)$settori_drupal[0]['target_id'] != $settore_file){
        /*if(!$this->impresaHasSettore($settori_drupal, $settore_file) && empty($impresa_non_ligure)){

            $this->return_html .= 'Aggiorno settore impresa <b>'.$company[0].'</b><br>';

            $new_log_batch = date('Y-m-d').' - 20. Aggiornato uno o più dei seguenti campi: Codice Ateco (da Aris), Requisiti di innovazione tecnologica (da sezione speciale), Settore d’impresa  (da sezione speciale)';
            $field_log_batch = $this->addQueueLog($this->nodo_impresa, $new_log_batch);

//            $this->nodo_impresa->set('field_settori_dell_impresa', $settore_file);
            $this->nodo_impresa->set('field_log_batch', $field_log_batch);

            //verifico se è in liquidazione
            $temp = strpos(strtolower($company[0]), 'liquidazione');
            if($temp !== false && $this->nodo_impresa->isPublished()) {

                $this->return_html .= 'L\'impresa <b>' . $company[0] . '</b> è in liquidazione ed è stata spubblicata.<br>';

                $this->nodo_impresa->setPublished(false);
            }

            $this->nodo_impresa->save();
        }else{*/
            //verifico se è in liquidazione
            // todo CODICE RIPETUTO..
            $temp = strpos(strtolower($company[0]), 'liquidazione');
            if($temp !== false && $this->nodo_impresa->isPublished()) {
                $this->return_html .= 'L\'impresa <b>' . $company[0] . '</b> è in liquidazione ed è stata spubblicata.<br>';

                $this->nodo_impresa->setPublished(false);
                $this->nodo_impresa->save();
            }
        //}
    }

    /**
     * @return void
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    private function checkExistingCompaniesInnovative(){
        $values = [
            'type' => 'impresa',
        ];
        $lista_imprese = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties($values);
        $indice = 0;
        foreach($lista_imprese AS $impresa) {

            $tipi_impresa = $impresa->get('field_tipo_di_impresa')->getValue();
            $partita_iva_impresa = str_pad($impresa->get('field_partita_iva')->getString(), 11, '0', STR_PAD_LEFT);
            $impresa_non_ligure = $impresa->get('field_impresa_non_ligure')->getString();
            $ragione_sociale_impresa = $impresa->get('field_ragione_sociale_')->getString();
            $codice_fiscale_impresa = str_pad($impresa->get('field_codice_fiscale_impresa')->getString(), 11, '0', STR_PAD_LEFT);
            $nome_impresa = $impresa->get('title')->getString();
            $impresa_is_innovativa = $this->hasTipoInnovativo($tipi_impresa);

            //verifico che esista un tipo,
            // che esista la PIVA e che l'impresa sia ligure
            if (!empty($tipi_impresa) && !empty($partita_iva_impresa) && !$impresa_non_ligure && $impresa_is_innovativa) {

                $is_not_on_excel = true;
                //verifico la presenza nei file excel SOLO nel caso che il tipo sia innovativo
                foreach ($this->xlsStartup as $item) {
                    if ($partita_iva_impresa == trim($item[2])) {
                        $is_not_on_excel = false;
                        break;
                    }
                }

                if ($is_not_on_excel) {
                    foreach ($this->xlsPmi as $item) {
                        if ($partita_iva_impresa == trim($item[2])) {
                            $is_not_on_excel = false;
                            break;
                        }
                    }
                }

                //entro in questo IF se l'azienda non è in nessuno dei due excel
                if ($is_not_on_excel) {
                    $this->return_html .= $indice . ' L\'impresa <b>' . $nome_impresa . '</b> è innovativa ma non è presente nei file excel.<br>';

                    //ora verifico se esiste su aris
                    $this->enrollmentService->getArisData($partita_iva_impresa);
                    if ($this->enrollmentService->hasArisValue()) {
                        //esiste si ARIS
                        if($this->enrollmentService->aris_xml->i_stato_attivita == 'A'){
                            //azienda attiva su ARIS
                            $new_log_batch = date('Y-m-d') . ' - 8. Impresa non più iscritta alla sezione speciale: non più presenti i requisiti di innovazione';
                            $field_log_batch = $this->addQueueLog($this->nodo_impresa, $new_log_batch);

                            $mail_msg = "L'impresa denominata $ragione_sociale_impresa con codice fiscale $codice_fiscale_impresa in data " . date('d/m/Y') . " non risulta più iscritta alla sezione speciale startup innovative / PMI innovative";

                            $this->return_html .= $indice . 'Aggiorno il tipo impresa per <b>' . $nome_impresa . '</b>.<br>';

                            //aggiorno i dati impresa
                            $nuovo_tipo = 668;
                            $impresa->set('field_tipo_di_impresa', $nuovo_tipo);
                            $impresa->set('field_log_batch', $field_log_batch);
                        }else{
                            //azienda inattiva su ARIS
                            $new_log_batch = date('Y-m-d') . ' - 6. Impresa inattiva su Registro Imprese, può essere cancellata logicamente dall’utente amministratore';
                            $field_log_batch = $this->addQueueLog($this->nodo_impresa, $new_log_batch);

                            $mail_msg = "L'impresa denominata $ragione_sociale_impresa con codice fiscale $codice_fiscale_impresa in data " . date('d/m/Y') . " non risulta più attiva banca dati delle imprese – ARIS";

                            $this->return_html .= $indice . ' L\'impresa <b>' . $nome_impresa . '</b>  è presente su ARIS, ma risulta inattiva.<br>';

                            $impresa->set('field_log_batch', $field_log_batch);
                            //disattivo
                            $impresa->setPublished(false);
                        }
                    } else {
                        //non esiste su ARIS
                        $new_log_batch = date('Y-m-d') . ' - 11. Impresa non presente su Registro Imprese, può essere cancellata logicamente dall’utente amministratore';
                        $field_log_batch = $this->addQueueLog($this->nodo_impresa, $new_log_batch);

                        $mail_msg = "L'impresa denominata $ragione_sociale_impresa con codice fiscale $codice_fiscale_impresa in data " . date('d/m/Y') . " non risulta iscritta alla banca dati delle imprese – ARIS";

                        $this->return_html .= $indice . ' L\'impresa <b>' . $nome_impresa . '</b> non è presente su ARIS, procedo con la disattivazione.<br>';

                        $impresa->set('field_log_batch', $field_log_batch);
                        //disattivo
                        $impresa->setPublished(false);
                        
                    }

                    //invio la mail e salvo l'impresa
//                    $this->sendMail($mail_msg);
                    //LIMITAZIONE AD USO TEST
                    // if($nome_impresa == 'ECO2LOGIC SRL'){
                    //     $this->return_html .= $indice . ' Ho salvato l\'azienda <b>' . $nome_impresa. '</b><br>';
                    //     $impresa->save();
                    // }
                    //TODO DA SPUBBLICARE IN PRODUZIONE
                    $this->return_html .= $indice . ' Ho salvato l\'azienda <b>' . $nome_impresa. '</b><br>';
                    $impresa->save();

                    $indice++;
                }
            }else{
                $this->return_html .= ' L\'impresa <b>' . $nome_impresa . '</b> non è innovativa, non è ligure o non dispone di PIVA.<br>';
            }
        }
    }

    /**
     * @param $company
     * @param $isStartup
     * @return void
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    private function createNewCompany($company, $isStartup){
        $dati_nodo = [];
        $msg_innovative = $user_mail = '';

        $id_utente_drupal = 1;
        $this->enrollmentService->getArisData($company[2]);
        $this->enrollmentService->getArisMultiData($company[2]);

        if($this->enrollmentService->hasMultiArisValue()) {
            /*
             * NOTA BENE AL MOMENTO NON ABBIAMO UN RICHIEDENTE E RECUPERIAMO I DATI DIRETTAMENTE DA ARIS DI CONSEGUENZA COMBACIA SICURAMENTE
             */
            $crea_nuovo_utente = true;
            foreach($this->enrollmentService->aris_multi_xml as $item){
                if(empty($item->codice_fiscale)){
                    $codice_fiscale_utente_aris = $item->codice_fiscale;
                    $utente_drupal = $this->drupalUserExist($codice_fiscale_utente_aris);
                    if (!empty($utente_drupal)) {
                        $id_utente_drupal = (int)$utente_drupal->get('uid')->value;
                        $crea_nuovo_utente = false;
                    }
                }
            }

            if($crea_nuovo_utente){
                $this->return_html .= '<b>CREAZIONE NUOVO UTENTE</b> .<br>';
            }
        }

        $comune = $indirizzo = $civico = '';
        $denominazione_impresa = trim($company[0]);
        $natura_giuridica = trim($company[1]);
        $codice_fiscale_impresa = $partita_iva_impresa = trim($company[2]);
        $comune = trim($company[4]).' ('.trim($company[3]).')';
        $impresaNonLigure = !$this->isCompanyLigure($company);

        $field_sito_web = trim($company[14]);

        $field_settori_dell_impresa = '';
        //se è una statup devo recuperare campi extra
        //inoltre le colonne sono sfasate e devo fare una serie di eccezioni
        $field_alto_valore_tecnologico = false;
        if($isStartup){
            //è una StatUp innovativa
            $field_tipo_di_impresa[] = 664;
            //valorizzo per le imprese a vocazione sociale
            if(!empty($company[13]) && strtoupper(trim($company[13])) == 'SI'){
                $field_tipo_di_impresa[] = 665;
            }
            //imprese ad alto valore tecnologico
            if(!empty($company[14]) && strtoupper(trim($company[14])) == 'SI'){
                $field_alto_valore_tecnologico = true;
            }

            $field_sito_web = trim($company[16]);
        }else{
            //è una PMI innovativa
            $field_tipo_di_impresa[] = 666;
        }

        $this->return_html .= '<b>'.$denominazione_impresa.'</b> esiste in ARIS.<br>';

        //È INNOVATIVA (lo recupero proprio dai files delle innovative)
        $msg_innovative = 'L\'impresa '.$partita_iva_impresa.' Risulta iscritta alla Sezione speciale della Camera di Commercio. La tua richiesta di accreditamento alla vetrina è stata accettata. Puoi accedere alla: Sezione NEWS della Vetrina se desideri mettere in evidenza le tue NEWS 
Sezione PROGETTI / PRODOTTI / TECNOLOGIE della Vetrina se desideri mettere in evidenza i tuoi Progetti, Prodotti, Tecnologie';
        //il richiedente combacia
        $stato_accreditamento = $this->stati_accreditamento['richiesta_validata_in_automatico_dal_sistema'];
        $nota_accreditamento = 'Impresa presente su Sezione Speciale Camera di commercio';
        $field_log_batch = date('Y-m-d').' - 5. Nuova Impresa proveniente dalla sezione speciale';
        if($impresaNonLigure){
            $field_log_batch = '13. Impresa non aggiornata perché non ligure';
        }

        $dati_nodo = [
            'type' => self::BUNDLE,
            'title' => $denominazione_impresa, // definire come creare il titolo
            'field_ragione_sociale_' => $denominazione_impresa,
            'field_partita_iva' => $partita_iva_impresa,
            'field_codice_fiscale_impresa' => $codice_fiscale_impresa,
            'field_email_contatto_riferimento' => $user_mail,
            'field_stato_accreditamento' => $stato_accreditamento,
            'field_nota_accreditamento' => $nota_accreditamento,
            'field_codice_comune' => $comune,
            'field_indirizzo_della_sede' => $indirizzo,
            'field_numero_civico' => $civico,
            'field_tipo_di_impresa' => $field_tipo_di_impresa,
            'field_natura_giuridica' => $natura_giuridica,
            'field_impresa_non_ligure' => $impresaNonLigure,
            'field_alto_valore_tecnologico' => $field_alto_valore_tecnologico,
            'field_sito_web' => $field_sito_web,
//            'field_settori_dell_impresa' => $field_settori_dell_impresa,
            'field_log_batch' => $field_log_batch,
//                'field_data_richiesta_accreditame' => date('Y-m-d'),
        ];


        if(!empty($dati_nodo)) {
            $node = Node::create($dati_nodo);
            $this->saveNode($node, $id_utente_drupal);

            if (!empty($msg_innovative)) {
                $this->return_html .= $msg_innovative;
            }
        }
    }

    /**
     * @param Node $node
     * @param int $id_utente
     * @return int|mixed|string|null
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    private function saveNode(Node $node, int $id_utente)
    {
        $node->uid = $id_utente;
        $node->langcode = 'it';
        $node->setPublished(false);
        $node->save();

        return $node->id();
    }

    /**
     * @param $codice_fiscale_utente
     * @return \Drupal\Core\Entity\EntityInterface|false
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    private function drupalUserExist($codice_fiscale_utente){

        $utente_drupal = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(
            [
                'field_codice_fiscale' => $codice_fiscale_utente
            ]
        );

        if(!empty($utente_drupal)){
            foreach ($utente_drupal as $item) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param $company
     * @return bool
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    private function isNewCompany($company): bool
    {
        $codice_fiscale_impresa = $company[2];
        $partita_iva = $company[2]; //TODO assumo sia uguale a CF??

        $impresa = $this->enrollmentService->getImpresa($partita_iva, $codice_fiscale_impresa);
        if(empty($impresa)) {
            //in questo caso devo creare la nuova azienda
            return true;
        }else{
            $this->nodo_impresa = $impresa;
            //azienda esiste e devo modificarla
            return false;
        }
    }

    /**
     * @param $company
     * @return int
     */
    private function getCompanySettore($company): int
    {

        $id_settore_drupal = '';
        $settore = strtoupper(trim($company[9]));
        if(!empty($settore)){
            switch ($settore){
                case 'SERVIZI';
                    $id_settore_drupal = 431;
                break;
                case 'INDUSTRIA/ARTIGIANATO';
                    $id_settore_drupal = 432;
                break;
                case 'COMMERCIO';
                    $id_settore_drupal = 433;
                break;
                case 'TURISMO';
                    $id_settore_drupal = 434;
                break;
                case 'AGRICOLTURA/PESCA';
                    $id_settore_drupal = 722;
                break;
                default:
                    //come default definiamo SERVIZI
                    $id_settore_drupal = 431;
                break;
            }
        }else{
            $id_settore_drupal = 431;
        }
        return $id_settore_drupal;
    }

    /**
     * @param $field_settore
     * @param $settore_file
     * @return bool
     */
    private function impresaHasSettore($field_settore, $settore_file): bool
    {
        foreach ($field_settore as $settore){
            if((int)$settore['target_id'] == $settore_file){
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $tipi_azienda
     * @return bool
     */
    private function hasTipoInnovativo(array $tipi_azienda): bool
    {
        foreach ($tipi_azienda as $item){
            if($item['target_id']==664){
                return true;
            }

            if($item['target_id']==666){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $company
     * @return bool
     */
    private function isCompanyLigure($company): bool
    {
        $arrProvinceLiguri = ['GE', 'SP', 'SV', 'IM'];
        if(in_array(strtoupper($company[3]), $arrProvinceLiguri)){
            return true;
        }
        return false;
    }

    /**
     * @param $impresa
     * @return void
     * Questa funzione è replicata nel file della FORM e sarebe meglio
     * averne un'unica versione
     */
    
    private function validateAccreditamento($impresa, $isStartup){
        $stato_accreditamento = $impresa->get('field_stato_accreditamento')->getString();
        $ragione_sociale = $impresa->get('field_ragione_sociale_')->getString();
        $piva_impresa = str_pad($impresa->get('field_partita_iva')->getString(), 11, '0', STR_PAD_LEFT);
        $cf_impresa = str_pad($impresa->get('field_codice_fiscale_impresa')->getString(), 11, '0', STR_PAD_LEFT);
        $field_tipo_di_impresa = $impresa->get('field_tipo_di_impresa')->getValue();

        if($stato_accreditamento == $this->stati_accreditamento['richiesta_in_attesa_di_validazione']){
            $this->return_html .= '<b>'.$impresa->get('title')->getString().'</b> '.$this->t('È già stata fatta richiesta di accreditamento per questa azienda. La richiesta è in attesa di validazione. Riceverai una email quando la tua impresa risulterà accreditata<br>');

            $toUpdate = false;
            foreach($field_tipo_di_impresa as $item){
                if(($isStartup && $item['target_id'] == 664) || (!$isStartup && $item['target_id'] == 666)){
                    $toUpdate = true;
                    break;
                }
            }
            if($toUpdate){
                //metto cablato lo stato ACCREDITATO IN AUTOMATICO DAL SISTEMA (721)
                $impresa->set('field_stato_accreditamento', 721);
                $impresa->save();
//                dump($email);
//                dump($impresa->id());
//                die;

                $admin_mail_message = 'A seguito del batch la richiesta di accreditamento per  l\'impresa ('.$ragione_sociale.', '.$piva_impresa.'/'.$cf_impresa.')  è stata accettata in automatico';

                $mail_message = 'La tua richiesta di accreditamento fatta per l\'impresa '.$ragione_sociale.' è stata accettata.
                    <br><br>
                    Accedi con SPID e completa l\'inserimento dati per l\'impresa '.$ragione_sociale.' a questo <a href="http://dev.liguriadigitale.bbsitalia.com:60080/node/'.$impresa->id.'/edit">Modifica impresa</a>.<br>
                    Puoi aggiungere ulteriori soggetti abilitati all\'inserimento dati.
                    N.B. Anche tali soggetti dovranno essere muniti di credenziali SPID.
                    <br><br>
                    Cordiali saluti,
                    il team di Vetrina imprese';

                //                $email = $impresa->uid->entity->mail->value;
                $this->sendMail($mail_message);
                $this->sendMail($admin_mail_message, true);
            }

        }elseif($stato_accreditamento == $this->stati_accreditamento['richiesta_non_validata']){
            $this->return_html .= '<b>'.$impresa->get('title')->getString().'</b> '.$this->t('È già stata fatta richiesta di accreditamento per questa azienda. La tua richiesta non è stata accettata per il seguente motivo: "').$impresa->get('field_parere_accreditamento')->getString(). $this->t('" Per maggiori informazioni invia una email all’ indirizzo: bluedistrict@job-centre-srl.it<br>');
        }elseif($stato_accreditamento==$this->stati_accreditamento['richiesta_validata'] || $stato_accreditamento==$this->stati_accreditamento['richiesta_validata_in_automatico_dal_sistema']){
            $this->return_html .= '<b>'.$impresa->get('title')->getString().'</b> È già stata fatta richiesta di accreditamento per questa azienda. Se non hai ricevuto una email di conferma o non riesci ad accedere alla vetrina invia una email all’ indirizzo: bluedistrict@job-centre-srl.it<br>';
        }elseif (empty($stato_accreditamento)){
            $this->return_html .= '<b>'.$impresa->get('title')->getString().'</b> esiste ma non ha nessuno stato di accreditamento definito.<br>';
        }
    }

    private function openXlsFile(){

        $xls_pmi = (int)\Drupal::configFactory()->get('nova_accredita.settings')->get('nova_accredita_xls_pmi');
        $xls_startup = (int)\Drupal::configFactory()->get('nova_accredita.settings')->get('nova_accredita_xls_startup');

        if(!empty($xls_pmi)){
            $file =  \Drupal\file\Entity\File::load($xls_pmi);
            if(gettype($file) == 'object' && !empty($file->getFileUri()) ){
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getFileUri());
                $spreadsheet->getSheet(1); //seleziono il secondo foglio
                $this->xlsPmi = $spreadsheet->getActiveSheet()->toArray();
            }
        }

        if(!empty($xls_startup)){
            $file =  \Drupal\file\Entity\File::load($xls_startup);
            if(gettype($file) == 'object' && !empty($file->getFileUri()) ) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getFileUri());
                $spreadsheet->getSheet(1); //seleziono il secondo foglio
                $this->xlsStartup = $spreadsheet->getActiveSheet()->toArray();
            }
        }

    }

    /**
     * Send mail.
     *
     * @return void
     */
    private function sendMail($message, $toAdmin=false)
    {
        $module = 'nova_accredita';
        $key = 'enrollment_message';

        //admin user
        if($toAdmin){
            //toDO sarà l'utente UNO?
            $destinatario = User::load(1);
        }else{
            //carico utente corrente
            //todo deve recuperare l'utente iscritto
            $uid = \Drupal::currentUser()->id();
            $destinatario = User::load($uid);
//            $destinatario = User::load(1);
        }

        $to = $destinatario->getEmail();
        $from = $this->config('system.site')->get('mail');
        $params['message'] = $message;

        $language_code = $this->languageManager->getDefaultLanguage()->getId();
        $send_now = TRUE;
        $result = $this->mailManager->mail($module, $key, $to, $language_code, $params, $from, $send_now);
    }
}