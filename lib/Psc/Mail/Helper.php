<?php

namespace Psc\Mail;

use \Swift_SmtpTransport,
    \Swift_MailTransport,
    \Swift_Message,
    \Swift_Mailer,
    \Psc\Code\Code,
    \Psc\PSC,
    \Psc\Config
  ;

/**
 * 
 * eine HTML Mail versenden:
 *
 * use Psc\Mail\Helper as email;
 * 
 * $sent = email::send(
 *   email::htmlMessage('GREG Import-Status', $html, $email, NULL, NULL, $bcc = Config::get('errorRecipient.mail'))
 * );
 * // ist Config[mail.smtp.user] und Config[mail.smtp.password] gesetzt wird SMTP benutzt, ansonsten der lokale Mailer
 * 
 */
class Helper {

  /**
   * 
   *
   * @return Swift_Message
   */
  public static function htmlMessage($subject, $html, $to, $from = NULL, $cc = NULL, $bcc = NULL) {
    $message = self::message($subject, $html, $to, $from, $cc, $bcc)
      ->setContentType('text/html')
    ;
    
    return $message;
  }
  
  /**
   * @return Swift_Message
   */
  public static function message($subject, $body, $to, $from = NULL, $cc = NULL, $bcc = NULL) {
    
    $message = Swift_Message::newInstance()
      ->setTo(self::recipient($to))
      ->setSubject($subject)
      ->setBody($body)
      ->setFrom(self::from($from));
    ;
    
    if (isset($cc)) {
      $message->setCc(self::recipient($cc));
    }

    if (isset($bcc)) {
      $message->setBCc(self::recipient($bcc));
    }
    
    return $message;
  }
  
  /**
   * $message->setTo(self::recipient('p.scheit@ps-webforge.com'));
   * 
   * @return korrektes Format für Message::set(From|To|Bcc|Cc)
   */
  public static function recipient($recipient) {
    if (is_string($recipient)) return $recipient;
    if (is_array($recipient)) return $recipient; // @TODO checks?
    
    throw new Exception('Unbekannter Parameter: '.Code::varInfo($recipient));
  }
  
  /**
   * Erstellt "from" für eine Message
   *
   * Der $sender hat ein Default von Config[mail.from]
   * wenn das nicht gestzt ist, wird www@host genommen
   */
  public static function from($sender = NULL) {
    if (!isset($sender) && ($sender = Config::get('mail.from')) == NULL) {
      $sender = 'www@'.PSC::getEnvironment()->getHostName();
    }
    return self::recipient($sender);
  }
  
  /**
   * Sendet die Message
   *
   * Wird Transport und Mailer nicht angegeben, werden diese erstellt
   * Ist ein Mailer angegeben wird transport nicht erstellt (wird ignoriert)
   *
   * wenn ein SMTP Zugang mit Config[mail.smtp.user] und Config[mail.smtp.password] gesetzt wurde,
   * wird dieser als Transport genutzt ansonsten der lokale Mailer
   * @return die Ausgabe von mailer->send() (int $sent)
   */
  public static function send(Swift_Message $message, Swift_SmtpTransport $transport = NULL, Swift_Mailer $mailer = NULL) {
    
    if (!isset($transport) && !isset($mailer)) {
      if (Config::req('mail.smtp.user') != NULL) {
        $transport = Swift_SmtpTransport::newInstance('smtprelaypool.ispgateway.de',465, 'ssl')
          ->setUsername(Config::req('mail.smtp.user'))
          ->setPassword(Config::req('mail.smtp.password'));
      } else {
        $transport = Swift_Mailtransport::newInstance(); // lokaler mailer
      }
    }
  
    if (!isset($mailer)) {  
      $mailer = \Swift_Mailer::newInstance($transport);
    }
    
    return $mailer->send($message);
  }
  
  /**
   * Lädt das Swift Modul
   *
   */
  public static function loadModule() {
    PSC::getProject()->getModule('Swift')->bootstrap();
  }
}
?>