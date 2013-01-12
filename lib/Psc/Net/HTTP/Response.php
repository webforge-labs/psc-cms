<?php

namespace Psc\Net\HTTP;

use Psc\Code\Code;

class Response extends \Psc\Object {
  
  const CONTENT_TYPE_JSON = 'application/json';
  const CONTENT_TYPE_HTML = 'text/html';
  const CONTENT_TYPE_PLAIN = 'text/plain';
  const CONTENT_TYPE_IMAGE_PNG = 'image/png';
  
  const OUTPUT_HEADER           = 0x000001;
  const OUTPUT_BODY             = 0x000002;
  const OUTPUT_CLOSURE          = 0x000004;
  
  /**
   * @var Psc\Net\HTTP\Header
   */
  protected $header;
  
  /**
   * @var string
   */
  protected $body;
  
  /**
   * Wenn gesetzt wird dies statt print $this->body benutzt
   * 
   * @var Closure
   */
  protected $outputClosure;
  
  public function __construct($statusCode, $body = NULL, ResponseHeader $header = NULL) {
    $this->header = $header ?: new ResponseHeader();
    $this->body = $body;
    $this->setCode($statusCode);
  }
  
  /**
   * @param ResponseHeader|array|string $headers
   * @return Psc\Net\HTTP\Response
   */
  public static function create($code, $body = NULL, $header = NULL) {
    if ($header === NULL) {
      $header = new ResponseHeader($code);
    } elseif (is_array($header)) {
      $header = new ResponseHeader($code,$header);
    } elseif ($header instanceof ResponseHeader) {
      
    } elseif (is_string($header)) {
      $header = ResponseHeader::parse($header);
    } else {
      throw new \InvalidArgumentException('Header hat ein Falsches Format. '.Code::varInfo($headers));
    }
    
    return new static($code, $body, $header);
  }
  
  /**
   * @param string $body
   * @chainable
   */
  public function setBody($body) {
    $this->body = $body;
    return $this;
  }

  /**
   * @return string
   */
  public function getBody() {
    return $this->body;
  }
  
  /**
   * @param Psc\Net\HTTP\ResponseHeader $header
   * @chainable
   */
  public function setHeader(ResponseHeader $header) {
    $this->header = $header;
    return $this;
  }

  /**
   * @return Psc\Net\HTTP\ResponseHeader
   */
  public function getHeader() {
    return $this->header;
  }
  
  /**
   * @return mixed
   */
  public function getHeaderField($name) {
    return $this->header->getField($name);
  }

  /**
   * @return bool
   */
  public function hasHeaderField($name) {
    return $this->header->hasField($name);
  }
  
  /**
   * @chainable
   */
  public function setHeaderField($name, $value) {
    $this->header->setField($name,$value);
    return $this;
  }
  
  /**
   * @param const $type CONTENT_TYPE_*
   */
  public function setContentType($type) {
    $this->header->setField('Content-Type',$type);
    return $this;
  }
  
  public function getContentType() {
    return $this->header->getField('Content-Type',Header::PARSE);
  }
  
  public function getVersion() {
    return $this->header->getVersion();
  }

  public function getReason() {
    return $this->header->getReason();
  }
  
  public function getCode() {
    return $this->header->getCode();
  }
  
  
  /**
   * Sendet die Headers und gibt den Body aus
   *
   * @param bitmap $flags Default: (self::OUTPUT_HEADER | self::OUTPUT_BODY)
   */
  public function output($flags = 0x00003) {
    if (($flags & self::OUTPUT_HEADER) == self::OUTPUT_HEADER) {
      $this->sendHeaders();
    }
    
    if ($this->outputClosure instanceof \Closure) {
      $c = $this->outputClosure;
      $c();
      return;
    }
    
    if (($flags & self::OUTPUT_BODY) == self::OUTPUT_BODY) {
      print $this->body;
    }
  }
  
  /**
   * @chainable
   */
  public function sendHeaders() {
    $this->header->send();
    return $this;
  }
  
  /**
   * @chainable
   */
  public function setCode($code, $reason = NULL) {
    $this->header->setCode($code,$reason);
    return $this;
  }
  
  /**
   * @chainable
   */
  public function setVersion($version) {
    $this->header->setVersion($version);
    return $this;
  }

  public function debug() {
    $text  = "== Psc\Net\HTTP\Response =========================\n";
    //$text .= $this->getCode().' : '.$this->getReason()."\n";
    $text .= $this->getHeader()."\n";
    $text .= "== Response-Body =================================\n";
    $text .= \Psc\Doctrine\Helper::getDump((array) $this->body, 4);
    $text .= "=================================================\n";
/*
    $text .= "\n";
    $text .= "== Response =====================================\n";
    $text .= $this->getResponseHeader()."\n";
    $text .= "== Body =========================================\n";
    $text .= $this->response->getRaw()."\n";
    $text .= "=================================================\n";
*/
    return $text;
  }
  
  /**
   * @param Closure $outputClosure
   * @chainable
   */
  public function setOutputClosure(\Closure $outputClosure) {
    $this->outputClosure = $outputClosure;
    return $this;
  }

  /**
   * @return Closure
   */
  public function getOutputClosure() {
    return $this->outputClosure;
  }


}
?>