<?php

namespace Psc\URL\HTTP;

use Psc\Preg;
use Psc\DateTime\DateTime;

class Header extends \Psc\Object {
  
  const PARSE = 'parse';
  const RETURN_VALUE = 'return_value';
  
  protected $values;
  
  protected $version;
  
  protected $code;
  
  protected $reason;

  public static $statusTexts = array(
	'100' => 'Continue',
	'101' => 'Switching Protocols',
	'200' => 'OK',
	'201' => 'Created',
	'202' => 'Accepted',
	'203' => 'Non-Authoritative Information',
	'204' => 'No Content',
	'205' => 'Reset Content',
	'206' => 'Partial Content',
	'300' => 'Multiple Choices',
	'301' => 'Moved Permanently',
	'302' => 'Found',
	'303' => 'See Other',
	'304' => 'Not Modified',
	'305' => 'Use Proxy',
	'307' => 'Temporary Redirect',
	'400' => 'Bad Request',
	'401' => 'Unauthorized',
	'402' => 'Payment Required',
	'403' => 'Forbidden',
	'404' => 'Not Found',
	'405' => 'Method Not Allowed',
	'406' => 'Not Acceptable',
	'407' => 'Proxy Authentication Required',
	'408' => 'Request Timeout',
	'409' => 'Conflict',
	'410' => 'Gone',
	'411' => 'Length Required',
	'412' => 'Precondition Failed',
	'413' => 'Request Entity Too Large',
	'414' => 'Request-URI Too Long',
	'415' => 'Unsupported Media Type',
	'416' => 'Requested Range Not Satisfiable',
	'417' => 'Expectation Failed',
	'500' => 'Internal Server Error',
	'501' => 'Not Implemented',
	'502' => 'Bad Gateway',
	'503' => 'Service Unavailable',
	'504' => 'Gateway Timeout',
	'505' => 'HTTP Version Not Supported',
   );
  
  public function __construct(Array $values = array()) {
		$this->values = $values;
  }
  
  public function getField($name, $do = self::RETURN_VALUE) {
    $value = array_key_exists($name,$this->values) ? $this->values[$name] : NULL;
    
    if ($do === self::PARSE) {
      $value = $this->parseField($name, $value);
    }
    
    return $value;
  }
  
  /**
   * @return bool
   */
  public function hasField($name) {
    return $this->getField($name) !== NULL;
  }
  
  protected function parseField($name, $value) {
    switch ($name) {
      case 'Content-Disposition':
        $this->assertNotEmpty($value, $name);
        
        // @TODO das ist ganz schön stümperhaft aber geht erstmal
        if (Preg::match($value, '/^(.*?);\s+filename="(.*)"$/', $match)) {
          $value = (object) array('type'=>$match[1],'filename'=>$match[2]);
        } else {
          throw new HeaderFieldParsingException('Content-Disposition: "'.$value.'" kann nicht geparsed werden');
        }
        break;
    }
    return $value;
  }
  
  protected function assertNotEmpty($value, $name) {
    if (empty($value))
      throw new HeaderFieldNotDefinedException('Der Header: '.$name.' ist nicht gesetzt');
  }
  
  public function parseFrom($string) {
    $fields = explode("\r\n", Preg::replace($string, '/\x0D\x0A[\x09\x20]+/', ' '));
    $fields = explode("\r\n", $string);
    
    //Status-Line = HTTP-Version SP Status-Code SP Reason-Phrase CRLF
    //HTTP-Version   = "HTTP" "/" 1*DIGIT "." 1*DIGIT
    if (!Preg::match($statusLine = array_shift($fields), '|HTTP/([0-9]+\.[0-9]+)\s+([0-5][0-9]+)\s+(.*)|', $match)) {
      throw new \Psc\Exception('Kann Header Status-Line nicht parsen: "'.$statusLine.'"');
    }
    list($NULL,$this->version,$this->code,$this->reason) = $match;
    
    /*
       message-header = field-name ":" [ field-value ]
       field-name     = token
       field-value    = *( field-content | LWS )
       field-content  = <the OCTETs making up the field-value
                        and consisting of either *TEXT or combinations
                        of token, separators, and quoted-string>
    */
    
    
    foreach($fields as $field)  {
      try {
        if (Preg::match($field, '/([^:]+): (.+)/m', $match)) {
          list($NULL, $name, $value) = $match;
          $name = Preg::replace(mb_strtolower(trim($name)), '/(?<=^|[\x09\x20\x2D])./e', 'mb_strtoupper("\0")');
          if (isset($this->values[$name])) {
            if (is_array($this->values[$name])) {
              $this->values[$name][] = $value;
            } else {
              $this->values[$name] = array($this->values[$name], $value);
            }
          } else {
            $this->values[$name] = trim($value);
          }
        }
      } catch (\Psc\Exception $e) {
        if (mb_strpos($e->getMessage(), 'Bad UTF8') === FALSE) {
          throw $e;
        }
      }
    }
  }
  
  /**
   * @return NULL|Psc\DateTime\DateTime
   */
  public function getDate() {
    $str = $this->getField('Date');
    if ($str !== NULL) {
      //DateTime::COOKIE
    /*
     @TODO parseDate in HTTP:: Klasse 
     HTTP applications have historically allowed three different formats for the representation of date/time stamps:

      Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
      Sunday, 06-Nov-94 08:49:37 GMT ; RFC 850, obsoleted by RFC 1036
      Sun Nov  6 08:49:37 1994       ; ANSI C's asctime() format
    */

      return DateTime::parse(DateTime::RFC1123 , $str);
    }
    return NULL;
  }
  
  public static function parse($rawHeader) {
    $header = new static();
    $header->parseFrom($rawHeader);
      
    return $header;
  }
  
  public function __toString() {
    return \Psc\A::join($this->values, '%2$s: %1$s'."\n");
  }
}
?>