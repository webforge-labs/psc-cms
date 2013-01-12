<?php

namespace Psc\Net\HTTP;

use Psc\Code\Code;
use Psc\Preg;

class ResponseHeader extends \Psc\Net\HTTP\Header {

  protected $code;
  
  protected $reason;
  
  public function __construct($code = 200, Array $values = array()) {
    $this->setCode($code);
    $this->values = $values;
    
    $this->setDefaultValues();
  }
  
  public function setDefaultValues() {
    /* Eigentlich brauchen wir gar keine DefaultHeader Values, weil der Server schon echt viel für uns macht
      nicht ganz sicher hier */
    $this->values = array_replace(Array(
      'Pragma' => 'no-cache',
      'Cache-Control' => 'private, no-cache',
      'Vary' => 'Accept'
    ), $this->values);
  
    return $this;
  }
  
  public function setCode($code, $reason = NULL) {
    if (!is_integer($code)) throw new \InvalidArgumentException('Code muss ein Integer sein: '.Code::varInfo($code));
    $this->code = $code;
    if (!array_key_exists($this->code, self::$reasons)) {
      throw new \InvalidArgumentException('Response Code: '.$code.' ist nicht bekannt');
    }
    $this->reason = $reason ?: self::$reasons[$this->code];
    return $this;
  }

  public function getCode() {
    return $this->code;
  }

  protected function sendStatusLine() {
    $this->sendPHPHeader(NULL,'HTTP/'.$this->getVersion().' '.$this->getCode().' '.$this->getReason(), TRUE, $this->getCode());
    return $this;
  }
  
  public function parseFrom($string) {
    $fields = explode("\r\n", Preg::replace($string, '/\x0D\x0A[\x09\x20]+/', ' '));
    
    //Status-Line = HTTP-Version SP Status-Code SP Reason-Phrase CRLF
    //HTTP-Version   = "HTTP" "/" 1*DIGIT "." 1*DIGIT
    if (!Preg::match($statusLine = array_shift($fields), '|HTTP/([0-9]+\.[0-9]+)\s+([0-5][0-9]+)\s+(.*)|', $match)) {
      throw new \Psc\Exception('Kann Header Status-Line nicht parsen: "'.$statusLine.'"');
    }
    list($NULL,$this->version,$this->code,$this->reason) = $match;
  
    parent::parseFrom($string);    
  }

  public static $reasons = array(
	100 => 'Continue',
	101 => 'Switching Protocols',
	200 => 'OK',
	201 => 'Created',
	202 => 'Accepted',
	203 => 'Non-Authoritative Information',
	204 => 'No Content',
	205 => 'Reset Content',
	206 => 'Partial Content',
	300 => 'Multiple Choices',
	301 => 'Moved Permanently',
	302 => 'Found',
	303 => 'See Other',
	304 => 'Not Modified',
	305 => 'Use Proxy',
	307 => 'Temporary Redirect',
	400 => 'Bad Request',
	401 => 'Unauthorized',
	402 => 'Payment Required',
	403 => 'Forbidden',
	404 => 'Not Found',
	405 => 'Method Not Allowed',
	406 => 'Not Acceptable',
	407 => 'Proxy Authentication Required',
	408 => 'Request Timeout',
	409 => 'Conflict',
	410 => 'Gone',
	411 => 'Length Required',
	412 => 'Precondition Failed',
	413 => 'Request Entity Too Large',
	414 => 'Request-URI Too Long',
	415 => 'Unsupported Media Type',
	416 => 'Requested Range Not Satisfiable',
	417 => 'Expectation Failed',
	500 => 'Internal Server Error',
	501 => 'Not Implemented',
	502 => 'Bad Gateway',
	503 => 'Service Unavailable',
	504 => 'Gateway Timeout',
	505 => 'HTTP Version Not Supported',
   );
  
  /**
   * Gibt die Reason als String zurück
   *
   * ist code falsch wird NULL Zurückgegeben
   * @return string|NULL
   */
  public static function getReasonFromCode($code) {
    $code = (int) $code;
    return array_key_exists($code, self::$reasons) ? self::$reasons[$code] : NULL;
  }
}
?>