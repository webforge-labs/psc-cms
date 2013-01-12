<?php

namespace Psc\URL\Service;

use \Psc\Object,
    Psc\Code\Code
;

/**
 * use \Psc\URl\Service\Request
 * new Request(Request::GET, '/servicename/identifier/subresource');
 */
class Request extends \Psc\Object {
  
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  
  /**
   * @var string
   */
  protected $method;
  
  /**
   * @var string ohne davor und keinem trailingslash (dann ist das Explode einfacher)
   */
  protected $resource;
  
  /**
   * Der Body des Requests
   * 
   * Dies ist beim Post-Request z. B. _POST bei infer()
   * @var stdClass
   */
  protected $body;
  
  /**
   * @var array
   */
  protected $parts;
  
  public function __construct($method, $resource) {
    $this->resource = trim($resource,'/');
    $this->setMethod($method);
    $this->init();
  }
  
  /**
   * Erstellt einen Request aus den Umgebungsvariablen
   *
   * wird eine Umgebungsvariable mit NULL übergeben (oder ausgelassen), wird die global Umgebungsvariable genommen
   * infer() ist also äquivalent mit:
   * infer($_GET, $_POST, $_COOKIE, $_SERVER)
   *
   * ist $_GET['mod_rewrite_request'] gesetzt wird dies als resource genommen
   */
  public static function infer($GET = NULL, $POST = NULL, $COOKIE = NULL, $SERVER = NULL) {
    $merged = array();
    $merged['GET'] = $GET !== NULL ? $GET : $_GET;
    $merged['POST'] = $POST !== NULL ? $POST : $_POST;
    $merged['COOKIE']= $COOKIE !== NULL ? $COOKIE : $_COOKIE;
    $merged['SERVER'] = $SERVER !== NULL ? $SERVER : $_SERVER;
    
    $input = new \Psc\Form\DataInput($merged);
    
    $method = NULL;
    switch ($input->get(array('SERVER','REQUEST_METHOD'))) {
      
      case 'POST':
        $method = Request::POST;
        break;
        
      case 'PUT':
        $method = Request::PUT;
        break;
        
      case 'DELETE':
        $method = Request::DELETE;
        break;
      
      case 'GET':
      default:
        $method = Request::GET;
        break;
    }
    
    /* wir haben mit mod-rewrite $_GET['mod_rewrite_request'] gesetzt oder es übergeben */
    if (($rewriteResource = $input->get('GET.mod_rewrite_request', NULL)) != '') {
      $request = new Request($method, $rewriteResource);
    } elseif(isset($_SERVER['REQUEST_URI'])) {
      $request = new Request($method, $_SERVER['REQUEST_URI']);
    } else {
      throw new Exception('Konnte keine vernünftige Resource inferren. (kann nur GET[mod_rewrite_request]');
    }
    
    if ($request->getMethod() === REQUEST::POST) {
      $request->setBody($input->get(array('POST'),array()));
    }
    
    if ($request->getMethod() === REQUEST::PUT) {
      throw new Exception('Keine Ahnung, wie ich hier an den Body komme');
    }
    
    return $request;
  }
  
  public function init() {
    $this->parts = array_filter(array_map('trim',explode('/',$this->resource)));
  }
  
  /**
   * @param int $num bei 1 anfangend (net 0)
   */
  public function getPart($num) {
    if ($num == 0) throw new \InvalidArgumentException('Num ist 1-basierend');
    if ($num > count($this->parts)) return NULL;
    
    return $this->parts[$num-1];
  }
  
  public function part($num) {
    return $this->getPart($num);
  }
  
  public function replacePart($num, $value) {
    if ($num == 0) throw new \InvalidArgumentException('Num ist 1-basierend');
    if ($num > count($this->parts)) return $this;

    $this->parts[$num-1] = trim($value);
    return $this;
  }
  
  public function slice($fromNum, $length) {
    return array_slice($this->parts, $fromNum-1, $length);
  }
  
  public function getPartsFrom($num) {
    return array_slice($this->parts,$num-1);
  }
  
  public function setMethod($method) {
    Code::value($method, self::GET, self::POST, self::PUT, self::DELETE);
    $this->method = $method;
  }
  
  public function getMethod() {
    return $this->method;
  }
  
  /**
   * 
   * Wird intern als stdClass umgewandelt
   */
  public function setBody(Array $body) {
    $this->body = (object) $body;
    return $this;
  }
  
  /**
   * @return \stdClass
   */
  public function getBody() {
    return $this->body;
  }
}
?>