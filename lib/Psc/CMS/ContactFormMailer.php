<?php

namespace Psc\CMS;

use Psc\TPL\TPL;
use Swift_Message;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_SmtpTransport;
use Swift_NullTransport;
use Swift_Plugins_Loggers_EchoLogger;
use Swift_Plugins_Loggers_ArrayLogger;
use Swift_Plugins_LoggerPlugin;
use Psc\Code\Code;
use RuntimeException;
use Webforge\Configuration\Configuration as WebforgeConfiguration;

class ContactFormMailer extends \Psc\Object {
  
  const MODE_LOCAL_MAIL = 'local';
  const MODE_SMTP = 'smtp';
  const MODE_NULL = 'null';
  
  /**
   * @var ContactFormData
   */
  protected $data;
  
  /**
   * @var string
   */
  protected $recipient;
  
  /**
   * @var string
   */
  protected $debugRecipient;
  
  /**
   * Der Modus für welcher ein bestimmter Transport genutzt wird
   *
   * self::MODE_SMPT
   * dafür müssen mail.smtp.user und mail.smtp.password in der config gesetzt sein
   *
   * self::MODE_LOCAL_MAIL
   * dafür wird mail() zum versenden benutzt
   *
   * self::MODE_NULL
   * es wird keine echte E-mail versendet
   */
  protected $mode;

  /**
   * @var string
   */
  protected $template;
  protected $subject;
  
  protected $init = FALSE;
  
  /**
   * @var Swift_Message
   */
  protected $message;
  /**
   * @var Swift_Mailer
   */
  protected $mailer;
  protected $transport;
  protected $logger;
  
  protected $from;
  protected $envelope;

  protected $config;

  protected $autoReply = NULL;
  
  public function __construct(WebforgeConfiguration $config, ContactFormData $data, $mode = self::MODE_LOCAL_MAIL) {
    $this->config = $config;

    $this->data = $data;
    $this->setMode($mode);
    
    $this->from = $this->config->req('mail.from');
    $this->envelope = $this->config->req('mail.envelope');
  }
  
  public function init() {
    if (!isset($this->recipient)) {
      $this->recipient = $this->config->req('ContactForm.recipient');
    }
    
    if (!isset($this->template)) {
      throw new \Psc\Exception('Mailtemplate muss gesetzt sein. Sonst ist die Mail leer');
    }

    if (!isset($this->subject)) {
      throw new \Psc\Exception('Subject muss gesetzt sein.');
    }

    if ($this->mode === self::MODE_SMTP) {
      $this->transport = \Swift_SmtpTransport::newInstance('smtprelaypool.ispgateway.de',465, 'ssl')
        ->setUsername($this->config->req('mail.smtp.user'))
        ->setPassword($this->config->req('mail.smtp.password'));
      
      $this->mailer = Swift_Mailer::newInstance($this->transport);
    } elseif($this->mode === self::MODE_NULL) {
      $this->transport = Swift_NullTransport::newInstance();
      $this->mailer = Swift_Mailer::newInstance($this->transport);
      
      $this->logger = new Swift_Plugins_Loggers_ArrayLogger();
      $this->mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($this->logger));
    } elseif ($this->mode === self::MODE_LOCAL_MAIL) {
      $this->transport = Swift_Mailtransport::newInstance();
      $this->mailer = Swift_Mailer::newInstance($this->transport);
    } else {
      throw new RuntimeException('Mode is set to wrong constant'.Code::varInfo($this->mode));
    }
    
    $this->init = TRUE;
  }
  
  /**
   * @return int
   */
  public function send() {
    if ($this->init) {
      
      if ($this->debugRecipient) {
        $recipient = array($this->debugRecipient => 'DebugRecipient');
      } else {
        $recipient = $this->recipient;
      }

      $this->message = Swift_Message::newInstance()
        ->setSubject($this->getSubject())
        ->setFrom($this->from)
        ->setTo($recipient)
        ->setSender($this->envelope)
        ->setBody($this->getMailText())
        ->setContentType('text/plain')
      ;
      
      $success = $this->mailer->send($this->message);

      if (isset($this->autoReply) && $success > 0) {
        $this->sendAutoReplyMail();
      }

      return $success;
      
    } else {
      throw new \Psc\Exception('FormMailer noch nicht initialisiert');
    }
  }

  protected function sendAutoReplyMail() {
    if ($this->debugRecipient) {
      $recipient = array($this->debugRecipient => 'DebugRecipient');
    } else {
      $recipient = $this->data->getField($this->autoReply->emailField);
    }

    $reply = Swift_Message::newInstance()
      ->setSubject($this->autoReply->subject ?: $this->getSubject())
      ->setFrom($this->from)
      ->setTo($recipient)
      ->setSender($this->envelope)
      ->setBody(TPL::miniTemplate($this->autoReply->text, $this->data->getFields()->toArray()))
      ->setContentType('text/plain')
    ;
      
    return $this->mailer->send($reply);
  }
  
  public function getMailText() {
    $mailText = $this->template;
    $mailText = TPL::miniTemplate($mailText, $this->data->getFields()->toArray());
    
    return $mailText;
  }

  /**
   * You can use %xx% variables from your fields in $data 
   * for the template text
   */
  public function setAutoReply($emailFieldName, $text, $subject = NULL) {
    $this->autoReply = (object) array(
      'emailField'=>$emailFieldName,
      'subject'=>$subject,
      'text'=>$text
    );
  }
  
  /**
   * @param string $mode
   * @chainable
   */
  public function setMode($mode) {
    Code::value($mode, self::MODE_LOCAL_MAIL, self::MODE_NULL, self::MODE_SMTP);
    $this->mode = $mode;
    return $this;
  }

  /**
   * @return string
   */
  public function getMode() {
    return $this->mode;
  }

  /**
   * @return string
   */
  public function getDebugRecipient() {
    return $this->debugRecipient;
  }
  
  /**
   * @param string debugRecipient
   * @chainable
   */
  public function setDebugRecipient($debugRecipient) {
    $this->debugRecipient = $debugRecipient;
    return $this;
  }
}
