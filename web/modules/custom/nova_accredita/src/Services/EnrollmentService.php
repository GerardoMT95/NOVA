<?php

namespace Drupal\nova_accredita\Services;

use Drupal\file\Entity\File;

/**
 * Class FiscalCode
 * @package Drupal\nova_accredita\Services
 */
class EnrollmentService
{

    public $aris_xml;
    /**
    * @var array
    */
    public $aris_multi_xml;
    /**
     * @param $campo_identificativo
     * @return \$1|false|\SimpleXMLElement
     */
    public function getArisData($campo_identificativo){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apipub.comune.genova.it:443/aris_ulsearch',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:UlSearch"><soapenv:Header/><soapenv:Body><urn:getUL soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" ><partitaIVA_codiceFiscale xsi:type="q0:string">'.$campo_identificativo.'</partitaIVA_codiceFiscale></urn:getUL></soapenv:Body></soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                ': ',
                'Authorization: Bearer 44381433-ee0e-3b98-aef3-482ecc19ab97',
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', ':getULResponse'], '', $response);
        $xml = simplexml_load_string($clean_xml);

        $this->aris_xml = !empty($xml) ? $xml->Body->ns1->return[0] : '';

        return $xml;
    }

    /**
     * @param string $piva
     * @param string $cfiscale
     * @param string $surname
     * @return \$1|false|\SimpleXMLElement
     */
    public function getArisMultiData(string $piva='', string $cfiscale='', string $surname=''){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apipub.comune.genova.it:443/aris_rlmultisearch',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:RLSearch"><soapenv:Header/><soapenv:Body><urn:getMultiRL soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><rl_sn xsi:type="q0:string">si</rl_sn><partitaIVA_codiceFiscale xsi:type="q0:string">'.$piva.'</partitaIVA_codiceFiscale><cognomeRL xsi:type="q0:string">'.$surname.'</cognomeRL><cfRL xsi:type="q0:string">'.$cfiscale.'</cfRL></urn:getMultiRL></soapenv:Body></soapenv:Envelope>',
            CURLOPT_HTTPHEADER => array(
                ': ',
                'Authorization: Bearer 44381433-ee0e-3b98-aef3-482ecc19ab97',
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);

        $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', ':getULResponse', ':getMultiRLResponse'], '', $response);
        $xml = simplexml_load_string($clean_xml);

        if(!empty($xml)){
            foreach($xml->Body->ns1->return->resultRapprLegali->item as $item){
                $this->aris_multi_xml[] = $item;
            }
        }
//        $this->aris_multi_xml = !empty($xml) ? $xml->Body->ns1->return->resultRapprLegali->item : '';

        return $xml;
    }

    /**
     * @return bool
     */
    public function hasArisValue(){
        if(empty($this->aris_xml->partita_iva)){
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function hasMultiArisValue(){
        foreach($this->aris_multi_xml as $item){
            if(empty($item->c_fiscale_impresa)){
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $cf_utente
     * @return bool
     */
    public function isSameRichiedente(string $cf_utente){

//        dump($this->aris_multi_xml);
//        exit;
        foreach($this->aris_multi_xml as $item){
            if($item->codice_fiscale == $cf_utente){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $field_tipo_di_impresa
     * @param $partita_iva
     * @return bool
     */
    public function isImpresaInnovativa($codice_fiscale): bool
    {
        $config = \Drupal::configFactory()->get('nova_accredita.settings');
        $fid_pmi = (int)$config->get('nova_accredita_xls_pmi');
        $fid_sup = (int)$config->get('nova_accredita_xls_startup');
        $return_val = false;

        if(!empty($fid_pmi)) {
//            $inputFileName = './sites/default/files/nova_accredita/pminnovative.xls';
            $file = File::load($fid_pmi);
            $inputFileName = $file->getFileUri();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $listaPmiInnovative = $spreadsheet->getActiveSheet()->toArray();
            foreach ($listaPmiInnovative as $company) {
                if ($company[2] == $codice_fiscale) {
                    $return_val = true;
                    break;
                }
            }
        }

        if(!empty($fid_sup)) {
//            $inputFileName = './sites/default/files/nova_accredita/startup.xls';
            $file = File::load($fid_sup);
            $inputFileName = $file->getFileUri();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $listaStatup = $spreadsheet->getActiveSheet()->toArray();
            foreach ($listaStatup as $company) {
                if ($company[2] == $codice_fiscale) {
                    $return_val = true;
                    break;
                }
            }
        }

        return $return_val;
    }

    /**
     * @param string $partita_iva
     * @param string $codice_fiscale
     * @return \Drupal\Core\Entity\EntityInterface|false|string|void
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public function getImpresa(string $partita_iva, string $codice_fiscale){

        $azienda = '';
        if(!empty($partita_iva)){
            $azienda = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(
                [
                    'type' => 'impresa',
                    'field_partita_iva' => $partita_iva
                ]
            );
        }

        //se non ho trovato l'azienda con la PIVA provo con il CF
        if(empty($azienda) && !empty($codice_fiscale)){
            $azienda = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(
                [
                    'type' => 'impresa',
                    'field_codice_fiscale_impresa' => $codice_fiscale
                ]
            );
        }

        if(!empty($azienda)) {
            foreach ($azienda as $item) {
                return $item;
            }
        }else{
            return false;
        }
    }

    /**
     * @return string|bool
     */
    public function isPresent(string $fiscalcode)
    {
        $entities = [];
        // Load entities by their property values.
        $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'impresa', 'field_partita_iva' => $fiscalcode]);
        if (count($entities) > 0) {
            return $entities;
        }

        return false;
    }

}
