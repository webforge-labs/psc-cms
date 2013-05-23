<?php

namespace Psc\URL;

class Response extends \Psc\Object {
  
  /**
   * @var Psc\URL\HTTP\Header
   */
  protected $header;
  
  /**
   * @var string
   */
  protected $raw;
  
  public function __construct($raw, HTTP\Header $header) {
    $this->header = $header;
    $this->setRaw($raw);
  }
  
  /**
   * @return string
   */
  public function getContentType() {
    return $this->header->getField('Content-Type');
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
   * @return int
   */
  public function getCode() {
    return $this->header->getCode();
  }

  public function getDate() {
    return $this->header->getDate();
  }
  
  /**
   * @throws HTTP\HeaderFieldNotDefinedException, HTTP\HeaderFieldParsingException
   */
  public function getAttachmentFilename() {
    return $this->header->getField('Content-Disposition', HTTP\Header::PARSE)->filename;
  }

  /**
   * @return string
   */
  public function debug() {
    $text  = "== Psc\URL\Response =============================\n";
    //$text .= $this->getCode().' : '.$this->getReason()."\n";
    $text .= $this->getHeader()->debug()."\n";
    $text .= "== Response-RAW =================================\n";
    $text .= $this->raw;
    $text .= "=================================================\n";
    return $text;
  }
  
  /**
   * @param string $raw
   * @chainable
   */
  public function setRaw($raw) {
    $this->raw = $raw;
    return $this;
  }

  /**
   * @return string
   */
  public function getRaw() {
    return $this->raw;
  }
  
  public function getDecodedRaw() {
    //if ($this->hasHeaderField('Content-Type') && mb_stripos($this->getHeaderField('Content-Type'), 'charset=utf-8') === FALSE) {
      //throw new \Psc\Exception('@TODO convert encoding here'); // @TODO fixme
      //mb_convert_encoding($raw, $parsedContentType) // @TODO parse
    //}
    
    return $this->raw;
  }
}
?>