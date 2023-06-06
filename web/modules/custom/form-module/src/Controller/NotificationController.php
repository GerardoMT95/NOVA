<?php

namespace Drupal\ex81\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

use Drupal\Core\Entity\Query;
use Drupal\Core\Entity\Query\QueryInterface;

use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';

require 'PHPMailer/src/PHPMailer.php';

require 'PHPMailer/src/SMTP.php';


class NotificationController extends \Twig_Extension {

  public function getName() {
    return 'ex81.NotificationController';
  }

  public function nuova_richiesta_cliente($email_user, $nid){
    $node = Node::load($nid);
    $title = $node->get('title')->getValue()[0]['value'];
    $testo ='
    <body style="background-color: #fff; padding: 30px">
    <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
    <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png" height="150" /><br />
    Buongiorno,<br /><br />
    hai creato una nuova richiesta per '.$title.'.<br />
    Verrai informato sull\'evoluzione dello stato della tua richiesta via email.<br/><br/> Anche dalla tua dashboard personale <a href="http://lavorobet.comune.genova.it/user">portale lavoro</a> puoi monitorare le richieste effettuate.<br />
      Per qualsiasi informazione o chiarimento contattaci. <br /><br />
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_user
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function servizio_nonerogabile_mail($email_user, $nid){
      $node = Node::load($nid);
      if(!empty($node->get('field_servizio')->getValue()[0]['target_id'])){
        $servizio = Node::load($node->get('field_servizio')->getValue()[0]['target_id']);
        $nome_servizio = $servizio->get('title')->getValue()[0]['value'];
      }else{
        $nome_servizio = '';
      }
      if(!empty($node->get('field_azienda_proprietaria')->getValue()[0]['target_id'])){
        $stakeholder = Node::load($node->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
        $nome_stakeholder = $stakeholder->get('title')->getValue()[0]['value'];
        $email = $stakeholder->get('field_email_contatto_riferimento')->getValue()[0]['value'];
      }else{
        $nome_stakeholder = '';
        $email   = '';
      }

      $title = $node->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png" height="150" /><br />
      Buongiorno,<br /><br />
      Purtroppo il servizio '.$nome_servizio.' che hai richiesto allo stakeholder '.$nome_stakeholder.' non è erogabile.<br />
      Contatta lo stakeholder per maggiori informazioni: '.$email.'.<br/>
      Per qualsiasi informazione o chiarimento contattaci. <br /><br />
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_user
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function nuova_richiesta_stakeholder_service($email_stakeholder, $nid, $id_richiesta, $type){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      $node_richiesta = Node::load($id_richiesta);
      $id_user_richiedente = User::load($node_richiesta->get('field_user_richiedente')->getValue()[0]['target_id']);
      $nome_cognome = $id_user_richiedente->get('field_nome')->getValue()[0]['value'].' '.$id_user_richiedente->get('field_cognome')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      avete ricevuto una nuova richiesta per '.$title.' dall\'utente:'.$nome_cognome.'<br />
      Il vostro '.$type.' è: <br /><br />
      <a href="http://lavorobet.comune.genova.it/servizio_erogabile/'.$id_richiesta.'" style="background: #3f275d; padding: 5px 15px; border-radius: 10px; color: white; text-transform: uppercase; font-weight: 600; margin-right: 10px; font-size: 12px;    text-decoration: none;"> Erogabile </a>
      <a href="http://lavorobet.comune.genova.it/servizio_nonerogabile/'.$id_richiesta.'" style="background: #3f275d; padding: 5px 15px; border-radius: 10px; color: white; text-transform: uppercase; font-weight: 600; margin-right: 10px; font-size: 12px;    text-decoration: none;"> Non Erogabile </a> <br /><br/>
      Gestisci la richista dalla tua Dashboard. <br /><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_stakeholder
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function invia_sollecito($id_richiesta){

      $richiesta = Node::load($id_richiesta);
      if(!empty($richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id'])){

        $query = \Drupal::entityQuery('user')
        ->condition('field_azienda', $richiesta->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
        $users = $query->execute();
        if(isset($users)){
          foreach ($users as  $value) {
            $user = User::load($value);
            $email_stakeholder = $user->get('mail')->getValue()[0]['value'];

            $title = $richiesta->get('title')->getValue()[0]['value'];
            $testo ='
            <body style="background-color: #fff; padding: 30px">
            <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
            <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
            Buongiorno,<br /><br />
            avete ricevuto un sollecito per la richiesta '.$title.'<br /><br />
            <br />
            <a href="http://lavorobet.comune.genova.it/servizio_erogabile/'.$id_richiesta.'" style="background: #3f275d; padding: 5px 15px; border-radius: 10px; color: white; text-transform: uppercase; font-weight: 600; margin-right: 10px; font-size: 12px;    text-decoration: none;"> Erogabile </a>
            <a href="http://lavorobet.comune.genova.it/servizio_nonerogabile/'.$id_richiesta.'" style="background: #3f275d; padding: 5px 15px; border-radius: 10px; color: white; text-transform: uppercase; font-weight: 600; margin-right: 10px; font-size: 12px;    text-decoration: none;"> Non Erogabile </a> <br /><br/>
            Gestisci la richista dalla tua Dashboard. <br /><br/>
            Il Team di Portale Lavoro<br/><br/>

            </div>
            <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
            Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
            </div>
            </body>
            ';


            $mail1 = new PHPMailer(TRUE);
            try {
              $mail1->isSMTP();
              $mail1->SMTPDebug = 0;
              $mail1->Host = 'smtpmail.comune.genova.it';
              $mail1->Port = 25;
              $mail1->From = "noreply@portalelavoro.it";
              $mail1->FromName = "Portale Lavoro";
              $mail1->addAddress('assistenza@gmgnet.com'); //$email_stakeholder
              $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
              $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
              //$mail1->addBCC("gmgnetsrl@gmail.com");
              //Content
              $mail1->CharSet = 'utf-8';
              $mail1->isHTML(true);
              $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
              $mail1->Body = $testo;
              $mail1->send();
            }catch (Exception $e){
              echo $e->errorMessage();
            }
            //echo 'NUM solleciti'.$richiesta->get('field_sollecito')->getValue()[0]['value']+1;
            $solleciti = $richiesta->get('field_sollecito')->getValue()[0]['value']+1 ;

            $richiesta->set('field_sollecito',$solleciti);
            $richiesta->save();


          }
        }

      }

      return new RedirectResponse(URL::fromUserInput('/elenco-richieste')->toString());
    }

    public function nuova_richiesta_stakeholder_spazio($email_stakeholder, $nid, $id_richiesta, $type, $id_spazio, $id_user_richiedente){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      if(!empty($id_spazio)){
        $spazio = Node::load($id_spazio);
        if(!empty($spazio->get('field_indirizzo')->getValue[0]['value'])){
          $indirizzo = $spazio->get('field_indirizzo')->getValue[0]['value'];
        }else{
          $indirizzo = '';
        }

      }

      if(!empty($id_user_richiedente)){
        $user = User::load($id_user_richiedente);
        if(!empty($user->get('field_nome')->getValue()[0]['value'])){
          $nome = $user->get('field_nome')->getValue()[0]['value'];
        }else{
          $nome = '';
        }

        if(!empty($user->get('field_cognome')->getValue()[0]['value'])){
          $cognome = $user->get('field_cognome')->getValue()[0]['value'];
        }else{
          $cognome = '';
        }

        if(!empty($user->get('field_azienda')->getValue()[0]['target_id'])){
          $azienda = Node::load($user->get('field_azienda')->getValue()[0]['target_id']);
          if(!empty($azienda->get('title')->getValue()[0]['value'])){
            $nome_azienda = $azienda->get('title')->getValue()[0]['value'];
          }else{
            $nome_azienda = '';
          }
        }else{
          $nome_azienda = '';
        }
      }

      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      avete ricevuto una nuova richiesta  per lo Spazio di lavoro: <br/>
      '.$title.'<br />
      Localizzato a Genova in '.$indirizzo.' <br/>
      da parte dell\'utente '.$nome.' '.$cognome.' dell\'azienda '.$nome_azienda.' <br/><br/>
      Il vostro '.$type.' è: <br /><br />
      <a href="http://lavorobet.comune.genova.it/servizio_erogabile/'.$id_richiesta.'" style="background: #3f275d; padding: 5px 15px; border-radius: 10px; color: white; text-transform: uppercase; font-weight: 600; margin-right: 10px; font-size: 12px;    text-decoration: none;"> Disponibile </a>
      <a href="http://lavorobet.comune.genova.it/servizio_nonerogabile/'.$id_richiesta.'" style="background: #3f275d; padding: 5px 15px; border-radius: 10px; color: white; text-transform: uppercase; font-weight: 600; margin-right: 10px; font-size: 12px;    text-decoration: none;"> Non Disponibile </a> <br /><br/>
      Gestisci la richiesta dalla tua Dashboard. <br /><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_stakeholder
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function cambio_stato_richiesta($email_azienda, $nome_stato, $nid){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      if(!empty($node->get('field_servizio')->getValue()[0]['target_id'])){
        $servizio = Node::load($node->get('field_servizio')->getValue()[0]['target_id']);
        $titolo_ser = $servizio->get('title')->getValue()[0]['value'];
      }else if(!empty($node->get('field_spazio')->getValue()[0]['target_id'])){
        $spazio = Node::load($node->get('field_spazio')->getValue()[0]['target_id']);
        $titolo_ser = $spazio->get('title')->getValue()[0]['value'];
      }else{
        $titolo_ser = '';
      }

      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      la vostra richiesta per il servizio <strong>'.$titolo_ser.'</strong> ha cambiato stato.<br />
      Il nuovo stato è: <strong>'.$nome_stato.'</strong> <br /><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");


        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }


    public function cambio_stato_richiesta_nonerogabile($email_azienda, $nome_stato, $nid){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      if(!empty($node->get('field_servizio')->getValue()[0]['target_id'])){
        $servizio = Node::load($node->get('field_servizio')->getValue()[0]['target_id']);
        $titolo_ser = $servizio->get('title')->getValue()[0]['value'];
      }else if(!empty($node->get('field_spazio')->getValue()[0]['target_id'])){
        $spazio = Node::load($node->get('field_spazio')->getValue()[0]['target_id']);
        $titolo_ser = $spazio->get('title')->getValue()[0]['value'];
      }else{
        $titolo_ser = '';
      }
      $stakeholder = Node::load($node->get('field_azienda_proprietaria')->getValue()[0]['target_id']);
      $nome_stakeholder = $stakeholder->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      Purtroppo  il servizio '.$titolo_ser.' che hai richiesto allo stakeholder '.$nome_stakeholder.' non è erogabile. <br/>
      Contatta lo stakeholder per maggiori informazioni. <br/>

      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");


        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function richiesta_peronalizzata($email_azienda, $nid){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      la vostra richiesta <strong>'.$title.'</strong> è stata creata.<br />
      Verrai contattato dall\'amministratore del portale.<br /><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //$mail1->addBCC("gmgnetsrl@gmail.com");


        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function conferma_pubblicazione($email_azienda, $nid){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      la vostra richiesta <strong>'.$title.'</strong> è stata pubblicata.<br />

      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user


        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function cambio_stato_richiesta_conclusione($mail, $obiettivi, $id_richiesta){

      $node = Node::load($id_richiesta);
      $title = $node->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      la vostra richiesta <strong>'.$title.'</strong> è stato conclusa.<br />
      Commenti conclusivi: <br />
      '.$obiettivi.'<br/>

      <a href="http://lavorobet.comune.genova.it/node/'.$id_richiesta.'">Per maggiori dettagli andate nel dettaglio della richiesta</a><br/><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        //  $mail1->addBCC("gmgnetsrl@gmail.com");
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user


        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }
    }

    public function cambio_stato_richiesta_x($email_azienda, $nid){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />
      la vostra richiesta <strong>'.$title.'</strong> non è stata accettata dallo stakeholder.<br />
      Andate sulla vostra dashboard per visualizzare i dettagli e nel caso contattare lo stakeholder.<br /><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user


        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function cron_alert_stato_attesa($email_azienda, $nid_richiesta, $title, $data_creazione){

      $node = Node::load($nid);
      $title = $node->get('title')->getValue()[0]['value'];
      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />

      vi ricordiamo che questa richiesta:<a href="/node/'.$nid_richiesta.'"'.$title.'></a> è in stato <strong>ATTESA</strong> dal: '.$data_creazione.'.
      <br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        //$mail1->addBCC("gmgnetsrl@gmail.com");
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function send_email_nuovo_commento($mail_user,$titolo_richiesta, $id_richiesta,$body, $uid_cretore){

      $richiesta = Node::load($id_richiesta);

      if(!empty($richiesta->get('field_servizio')->getValue()[0]['target_id'])){
        $servizio = Node::load($richiesta->get('field_servizio')->getValue()[0]['target_id']);
        $titolo_ser = $servizio->get('title')->getValue()[0]['value'];
      }else if(!empty($richiesta->get('field_spazio')->getValue()[0]['target_id'])){
        $spazio = Node::load($richiesta->get('field_spazio')->getValue()[0]['target_id']);
        $titolo_ser = $spazio->get('title')->getValue()[0]['value'];
      }else{
        $titolo_ser = '';
      }

      if(!empty($richiesta->get('field_utente')->getValue()[0]['target_id'])){
        $azienda = Node::load($richiesta->get('field_utente')->getValue()[0]['target_id']);
        $nome_azienda = $azienda->get('title')->getValue()[0]['value'];
      }else{
        $nome_azienda = '';
      }

      if(!empty($uid_cretore)){
        $user_create = User::load($uid_cretore);

        if(!empty($user_create->get('field_nome')->getValue()[0]['value']) && !empty($user_create->get('field_cognome')->getValue()[0]['value'])){
          $nome_cognome_mittente = $user_create->get('field_nome')->getValue()[0]['value'].' '.$user_create->get('field_cognome')->getValue()[0]['value'];
        }else{
          $nome_cognome_mittente = '';
        }
      }

      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />

      è stato aggiunto un nuovo messaggio alla richiesta inviata per il servizio <strong>'.$titolo_ser.'</strong>, è stato inviato dall\'utente <strong>'.$nome_cognome_mittente.'</strong>.<br/>
      Il contenuto del messaggio è il seguente:<br/><br/>
      "<strong>'.$body.'</strong>"
      <br/>

      <a href="http://lavorobet.comune.genova.it/node/'.$id_richiesta.'">Vai alla pagina della richiesta per visualizzare ulteriori dettagli. </a><br/><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        //  $mail1->addBCC("gmgnetsrl@gmail.com");
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }

    public function send_email_nuovo_commento_creatore($mail_user,$titolo_richiesta, $id_richiesta,$body){

      $richiesta = Node::load($id_richiesta);

      if(!empty($richiesta->get('field_servizio')->getValue()[0]['target_id'])){
        $servizio = Node::load($richiesta->get('field_servizio')->getValue()[0]['target_id']);
        $titolo_ser = $servizio->get('title')->getValue()[0]['value'];
      }else if(!empty($richiesta->get('field_spazio')->getValue()[0]['target_id'])){
        $spazio = Node::load($richiesta->get('field_spazio')->getValue()[0]['target_id']);
        $titolo_ser = $spazio->get('title')->getValue()[0]['value'];
      }else{
        $titolo_ser = '';
      }

      if(!empty($richiesta->get('field_utente')->getValue()[0]['target_id'])){
        $azienda = Node::load($richiesta->get('field_utente')->getValue()[0]['target_id']);
        $nome_azienda = $azienda->get('title')->getValue()[0]['value'];
      }else{
        $nome_azienda = '';
      }

      if(!empty($richiesta->get('field_user_richiedente')->getValue()[0]['target_id'])){
        $user_create = User::load($richiesta->get('field_user_richiedente')->getValue()[0]['target_id']);

        if(!empty($user_create->get('field_nome')->getValue()[0]['value']) && !empty($user_create->get('field_cognome')->getValue()[0]['value'])){
          $nome_cognome_mittente = $user_create->get('field_nome')->getValue()[0]['value'].' '.$user_create->get('field_cognome')->getValue()[0]['value'];
        }else{
          $nome_cognome_mittente = '';
        }
      }

      $testo ='
      <body style="background-color: #fff; padding: 30px">
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#333;" >
      <img src="http://lavorobet.comune.genova.it/sites/default/files/logo_portalelavoro.png"  height="150"/><br />
      Buongiorno,<br /><br />

      il messaggio è stato inviato con successo, resta in attesa di una risposta.<br/>
      <br/>

      <a href="http://lavorobet.comune.genova.it/node/'.$id_richiesta.'">Vai alla pagina della richiesta per visualizzare ulteriori dettagli. </a><br/><br/>
      Il Team di Portale Lavoro<br/><br/>

      </div>
      <div style="width:600px; background-color:white; padding:30px; font-family: sans-serif; color:#aaa; font-size:10px;" >
      Questo messaggio di posta elettronica contiene informazioni di carattere confidenziale rivolte esclusivamente al destinatario sopra indicato. È vietato l\'uso, la diffusione, distribuzione o riproduzione da parte di ogni altra persona. Nel caso aveste ricevuto questo messaggio di posta elettronica per errore, siete pregati di segnalarlo immediatamente al mittente e distruggere quanto ricevuto (compresi i file allegati) senza farne copia. Qualsivoglia utilizzo non autorizzato del contenuto di questo messaggio costituisce violazione dell\'obbligo di non prendere cognizione della corrispondenza tra altri soggetti, salvo più grave illecito, ed espone il responsabile alle relative conseguenze.
      </div>
      </body>
      ';

      $mail1 = new PHPMailer(TRUE);
      try {
        $mail1->isSMTP();
        $mail1->SMTPDebug = 0;
        $mail1->Host = 'smtpmail.comune.genova.it';
        $mail1->Port = 25;
        $mail1->From = "noreply@portalelavoro.it";
        $mail1->FromName = "Portale Lavoro";
        $mail1->addAddress('assistenza@gmgnet.com'); //$email_azienda
        //  $mail1->addBCC("gmgnetsrl@gmail.com");
        $mail1->addAddress('F.Campello@liguriadigitale.it'); //$email_user
        $mail1->addAddress('A.Iacopi@liguriadigitale.it'); //$email_user
        //Content
        $mail1->CharSet = 'utf-8';
        $mail1->isHTML(true);
        $mail1->Subject = 'Portale lavoro Lavoro - Nuova richiesta per '.$title.'';
        $mail1->Body = $testo;
        $mail1->send();
      }catch (Exception $e){
        echo $e->errorMessage();
      }

    }



  }
