<?php

namespace Psc\CMS;

use Psc\Net\HTTP\Request;
use Psc\Code\Code;
use stdClass;

/**
 * RequestMeta
 *
 * überall wo definiert wird, wie ein Entity mit einem Service fungiert sollte dieses Meta-Objekt genutzt werden
 * ist ein alias von AjaxMeta weil ich AjaxMeta in den meisten Contexten etwas zu verwirrend fand (gesetzt den Fall wird machen irgendwann mal Requests auch ohne JS)
 */
class RequestMeta implements RequestMetaInterface {
  
  protected $method;
  
  protected $url;
  
  /**
   * @var object
   */
  protected $body;


  const QUERY = 'query';
  
  /**
   * Spezifikation der Parameter für getUrl() die gebraucht werden um die URL zu bauen
   * 
   * @var array
   */
  protected $inputMeta;
  
  /**
   * Parameter für getUrl() (vorausgefüllt)
   *
   * sind diese nicht gesetzt müssen diese zu getUrl() übergeben werden
   * macht nichts wenn count($inputMeta) == 0 ist
   * @var array
   */
  protected $concreteInput;
  
  /**
   *
   * $inputMeta z.b. der Identifier bei einer url wie /entities/sounds/$identifier/form
   * $inputMeta würde dann $identifier beschreiben
   *
   * jeder Eintrag in $inputMeta ist die Beschreibung eines Parameters
   * (KISS: im Moment sind dies einfach sprintf parameter namen also "%s" oder "%d" als Werte)
   * @param const $method Psc\Net\HTTP\Request::GET|POST|PUT|DELETE
   * @param array $inputMeta ein Array zur Definition von Parametern die benötigt werden um den Request ausführen zu können
   * @param array $concreteInput ein Array der schon der input für getUrl() ist. ist dieses angegeben und getUrl() wird leer aufgerufen wird dieses genommen
   */
  public function __construct($method, $url, Array $inputMeta = array(), Array $concreteInput = NULL) {
    $this->inputMeta = $inputMeta;
    $this->concreteInput = $concreteInput;
    $this->setMethod($method);
    $this->url = $url;
    $this->body = NULL;
  }
  
  public function export() {
    return (object) array(
      'method'=>$this->method,
      'url'=>$this->getUrl(),
      'body'=>$this->getBody()
    );
  }
  
  public function getUrl() {
    if (count($this->inputMeta) === 0) return $this->url;
    
    $input = func_get_args();
    if (count($input) == 0) {
      $input = (array) $this->concreteInput;
    }
    return $this->mergeInput($input, $this->url);
  }
  
  /**
   * @throws \Psc\Exception
   */
  protected function mergeInput(Array $input, $url) {
    if (count($this->inputMeta) > count($input)) {
      throw new \Psc\Exception('Fehler beim Erstellen der URL. Es fehlen Parameter!. Erwartet sind: '.implode(':',$this->inputMeta));
    }
    foreach ($this->inputMeta as $key => $meta) {
      if ($meta === self::QUERY) {
        if (count($input[$key]) > 0) {
          $input[$key] = '?'.http_build_query($input[$key]);
        } else {
          $input[$key] = '';
        }
      }
    }
    
    return vsprintf($url, $input);
  }
  
  /**
   * @param mixed $input1, ...
   * @chainable
   */
  public function setInput() {
    $this->concreteInput = func_get_args();
    return $this;
  }

  /**
   * @return array
   */
  public function getInput() {
    return $this->concreteInput;
  }
  

  public function addInputMeta($item) {
    if ($item === self::QUERY) {
      $this->url .= '%s';
    }
    $this->inputMeta[] = $item;
    return $this;
  }
  
  
  public function addInput($item) {
    $this->concreteInput[] = $item;
    return $this;
  }
  
  /**
   * @param string $url
   */
  public function setUrl($url) {
    $this->url = $url;
    return $this;
  }
  
  public function appendUrl($part) {
    $this->url .= $part;
    return $this;
  }
  
  /**
   * @param const $method
   * @chainable
   */
  protected function setMethod($method) {
    Code::value($method, self::GET, self::POST, self::PUT, self::DELETE);
    $this->method = $method;
    return $this;
  }
  
  /**
   * @return const
   */
  public function getMethod() {
    return $this->method;
  }
  
  /**
   * @param object $body
   */
  public function setBody($body = NULL) {
    $this->body = $body;
    return $this;
  }
  
  /**
   * @return object
   */
  public function getBody() {
    return $this->body;
  }}
?>