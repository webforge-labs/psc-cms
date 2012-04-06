<?php

use \Psc\CMS\ContactFormMailer,
    \Psc\CMS\ContactFormData
;

class ContactFormMailerTest extends PHPUnit_Framework_TestCase {
  
  protected $c = '\Psc\CMS\ContactFormMailer';
  
  protected $data;
  
  public function setUp() {
    $this->data = new ContactFormData();
    //
    //$sendm = 'php -f D:\stuff\Webseiten\Mailer\mail_logger.php';
    //if (ini_get('sendmail_from') != $sendm) {
    //  
    //  if (ini_get('sendmail_from') != $sendm) 
    //    throw new \Exception('Setzen sie bitte ihren Sendmail Path auf: "'.$sendm.'" momentan: "'.ini_get('sendmail_from').'"');
    //}
    
    
    foreach (array('vorname'=>'Philipp',
                   'nachnachme'=>'Scheit',
                   'email'=>'techno@scfclan.de',
                   'message'=>'Hallo, ich glaube das einfach das Kontaktformular auf dieser
Seite irgendwie eine zu kleine Eingabebox hat.

Ich denke auch das man mit (\') bananen nicht so viele Sachen kaufen kann, wie man das eigentlich möchte.

Und Umbrüche sollten schon gehen, auch ß und weitere.

Mit freundlichen Gürßen
Philipp

P.S. Ich weiß, dass man Gürßen anders schreibt. !Depp!') as $field => $value) {
      $this->data->setField($field,$value);
    }
    
    $this->template =
'Nachricht
%message%

Vorname
%vorname%

Name
%name%

E-mail
%email%

Firma
%firma%

Telefon
%telefon%

Mobil
%mobil%';
}
  
  public function testConstructInitSend() {
    
    $mailer = new $this->c($this->data);
    $mailer->setProduction(TRUE);
    $mailer->setDebugRecipient('p.scheit@ps-webforge.com');
    $mailer->setTemplate($this->template);
    $mailer->setSubject('Erhaltene Nachricht auf green-group.de');
    
    $mailer->init();
    $mailer->setTransport(\Swift_SmtpTransport::newInstance('mail.acebusters.com',25)
       ->setUsername('www@acebusters.com')
       ->setPassword('laSUeeEYiHDwfdDHPUSd')
      );
    
    $mailer->send();
    
    $logger = $mailer->getLogger();
    
    $logger->dump();
  }
}

?>