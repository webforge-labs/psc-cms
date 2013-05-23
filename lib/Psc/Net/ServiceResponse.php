<?php

namespace Psc\Net;

use Psc\Code\Code;
use Psc\CMS\Service\MetadataGenerator;

class ServiceResponse extends \Psc\SimpleObject {

  const HTML = 'html';
  const TEXT = 'text';
  const JSON = 'json';
  const JSON_UPLOAD_RESPONSE = 'json_upload_response'; // sonderfall für sonderbehandlung mit IE bei iframe transfers
  const XML = 'xml';
  const IMAGE = 'image';
  const XLSX = 'xlsx';
  const XLS = 'xls';
  const SOME_FILE = 'some_file';
  const ICAL = 'ical';
  
  /**
   * \Psc\Net\Service::OK
   */
  protected $status;
  
  /**
   * Der Content der Response
   *
   * dies ist kein RAW-String sondern kann ein komplexes Objekt sein, ein Entity, eine Collection, etc
   * @var mixed
   */
  protected $body;
  
  /**
   * Das (gewünschte) Format der Response
   *
   * @var self::HTML,self::JSON,self::XML,self::IMAGE
   */
  protected $format;
  
  /**
   * @var MetadataGenerator
   */
  protected $metadata;
  
  public function __construct($status = Service::OK, $body = NULL, $format = NULL) {
    $this->setStatus($status);
    $this->setBody($body);
    if (isset($format))
      $this->setFormat($format);
  }

  public static function create($body = NULL, $format = NULL) {
    return new static(Service::OK, $body, $format);
  }
  
  /**
   * @param const $status Service::OK|Service::ERROR usw
   * @chainable
   */
  public function setStatus($status) {
    Code::value($status, Service::OK, Service::ERROR);
    $this->status = $status;
    return $this;
  }

  /**
   * @return const Service::OK|Service::ERROR
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * @param mixed $body
   * @chainable
   */
  public function setBody($body) {
    $this->body = $body;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Wird nichts zurückgegeben wird die das Format automatisch ermittelt (Abhängig von Request z. B.)
   *
   * @return const|NULL
   */
  public function getFormat() {
    return $this->format;
  }
  
  /**
   * @param const $format
   */
  public function setFormat($format) {
    $this->format = $format;
    return $this;
  }
  
  /**
   * @param MetadataGenerator $metadata
   * @chainable
   */
  public function setMetadata(MetadataGenerator $metadata) {
    $this->metadata = $metadata;
    return $this;
  }

  /**
   * @return MetadataGenerator
   */
  public function getMetadata() {
    return $this->metadata;
  }
}
?>