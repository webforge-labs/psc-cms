<?php

namespace Psc\Net\HTTP;

use Psc\Object;
use Psc\Code\Code;
use Symfony\Component\HttpFoundation\Request AS SfRequest;
use Webforge\Common\JS\JSONConverter;

/**
 * use \Psc\Net\HTTP\Request
 * new Request(Request::GET, '/servicename/subresource1/subresource2/...?queryPart=value&queryPart2=value2#fragment');
 */
class Request extends \Psc\Object {
  
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  const PATCH = 'PATCH';
  
  protected $header;
  
  /**
   * Die Methode des Requests (Get/Post/Put/Delete)
   * @var string eine der constants
   */
  protected $method;
  
  /**
   * @var string ohne / davor und keinem trailingslash (dann ist das Explode einfacher)
   */
  protected $resource;
  
  /**
   * Der Query-String (GET) des Requests
   * 
   * @var array
   */
  protected $query;
  
  /**
   * Der Body des Requests
   * 
   * Dies ist beim Post-Request z. B. _POST bei infer()
   * @var stdClass
   */
  protected $body;
  
  /**
   * Die URL-Parts nach dem Base URI
   * @var array
   */
  protected $parts;
  
  /**
   * @var array
   */
  protected $files;
  
  /**
   * @var string
   */
  protected $referrer;
  
  /**
   * The languages in order of preference
   * 
   * @var array
   */
  protected $preferredLanguages;
  
  public function __construct($method, $resource, RequestHeader $header = NULL, Array $files = array()) {
    $this->resource = trim($resource,'/');
    $this->setMethod($method);
    $this->header = $header ?: new RequestHeader($this->method, $resource);
    $this->init();
    $this->setFiles($files);
  }
  
  /**
   * Erstellt einen Request aus den Umgebungsvariablen
   * 
   * wird eine Umgebungsvariable mit NULL übergeben (oder ausgelassen), wird die global Umgebungsvariable genommen
   * infer() ist also äquivalent mit:
   * infer($_GET, $_POST, $_COOKIE, $_SERVER)
   * 
   * ist $_GET['mod_rewrite_request'] gesetzt wird dies als resource genommen
   * 
   * @TODO Symfony hierfür nehmen (am besten ganz ersetzen)
   * 
   * @deprecated das übergeben von Variablen ist strongly discouraged!
   */
  public static function infer(SfRequest $sfRequest = NULL) {
    if (!isset($sfRequest)) {
      $sfRequest = SfRequest::createFromGlobals();
    }
    
    // alternativ könnten wir den code aus sf kopieren oder sf mal patchen..
    
    $method = NULL;
    switch ($sfRequest->getMethod()) { // wertet schon X-HTTP-METHOD-OVERRIDE aus
      
      case 'POST':
        $method = Request::POST;
        break;
        
      case 'PUT':
        $method = Request::PUT;
        break;
        
      case 'DELETE':
        $method = Request::DELETE;
        break;

      case 'PATCH':
        $method = Request::PATCH;
        break;
      
      case 'GET':
      default:
        $method = Request::GET;
        break;
    }
    
    
    $request = new Request($method, rawurldecode($sfRequest->getPathInfo()));
    $request->setQuery($sfRequest->query->all());
    $request->setReferer($sfRequest->server->get('HTTP_REFERER'));
    $request->setPreferredLanguages($sfRequest->getLanguages());
    
    $header = $request->getHeader();
    foreach ($sfRequest->headers->all() as $key => $value) {
      // wir verschönern hier z.B. X_REQUESTED_WITH zu X-Requested-With
      $key = mb_strtolower($key);
      $key = \Psc\Preg::replace_callback($key, '/(^|-)([a-z]{1})/', function ($m) {
          return ($m[1] === '' ? NULL : '-').mb_strtoupper($m[2]);
        });
      
      // das ist voll doof, aber aus legacy gründen müssen wir das machen
      // schöner wäre auch die sf Requests / Header zu benutzen, dann wären wir durch
      if (count($value) === 1) { // unwrap arrays mit nur einem eintrag
        $value = current($value);
      }
      
      $header->setField($key, $value);
    }
    
    /* Body */
    if (mb_strpos($request->getHeaderField('Content-Type'), 'application/x-www-form-urlencoded') === 0) {
      $request->setBody($sfRequest->request->all());
    } elseif(mb_strpos($request->getHeaderField('Content-Type'), 'multipart/form-data') === 0) {
      $request->setBody($sfRequest->request->all());
      
      $files = array();
      foreach ($sfRequest->files->all() as $key => $sfFile) {
        if ($sfFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
          if (!$sfFile->isValid()) {
            throw new \Psc\Exception('Cannot Upload File: '.$sfFile->getClientOriginalName().' Error Code: '.$sfFile->getErorr().' size: '.$sfFile->getClientSize());
          } else {
          
            $files[$key] = $f = new \Psc\System\UploadedFile($sfFile->getPathName());
            $f->setOriginalName($sfFile->getClientOriginalName());
          }
          
          $request->setFiles($files);
        } // FIXME else: kann auch ein array von files sein oder ein array von array ...
          // aber wie machen wir das in den files array rein?
        
      }
    } else {
      $request->setBody($sfRequest->getContent()); // really raw
    }
    
    return $request;
  }
  
  /**
   * 
   * @param array|stdClass
   * @param RequestHeader|array $header
   * @return Psc\Net\HTTP\Response
   */
  public static function create($method, $resource, $body = NULL, $header = NULL) {
    if ($header === NULL) {
    
    } elseif (is_array($header)) {
      $fields = $header;
      $header = new RequestHeader($method, $resource);
      foreach ($fields as $key => $value) {
        $header->setField($key, $value);
      }
      
    } elseif ($header instanceof RequestHeader) {
      
    } elseif (is_string($header)) {
      throw new \Psc\Exception('Parsing vom Header als String noch nicht erlaubt');
    } else {
      throw new \InvalidArgumentException('Header hat ein Falsches Format. '.Code::varInfo($headers));
    }
    
    return new static($method, $resource, $header);
  }
  
  public function init() {
    $this->parts = array_filter(array_map('trim',explode('/',$this->resource)));
  }
  
  public function slice($fromNum, $length) {
    return array_slice($this->parts, $fromNum-1, $length);
  }
  
  /**
   * @param int $num bei 1 anfangend (net 0)
   */
  public function part($num) {
    if ($num == 0) throw new \InvalidArgumentException('Num ist 1-basierend');
    if ($num > count($this->parts)) return NULL;
    
    return $this->parts[$num-1];
  }
  
  public function getParts() {
    return $this->parts;
  }
  
  public function replacePart($num, $value) {
    if ($num == 0) throw new \InvalidArgumentException('Num ist 1-basierend');
    if ($num > count($this->parts)) return $this;
    
    $this->parts[$num-1] = trim($value);
    return $this;
  }
  
  /**
   * @return bool
   */
  public function accepts($mimeType) {
    $accept = $this->getHeader()->getField('Accept');
    if (is_array($accept)) {
      $accept = implode(',',$accept);
    }
    return mb_strpos($accept, $mimeType) === 0; // naive
  }
  
  public function setMethod($method) {
    Code::value($method, self::GET, self::POST, self::PUT, self::DELETE, self::PATCH);
    $this->method = $method;
  }
  
  public function getMethod() {
    return $this->method;
  }
  
  /**
   * Setzt den RequestBody
   * 
   * wird intern als stdClass umgewandelt, wenn es object oder array ist
   */
  public function setBody($body) {
    if (is_array($body) || is_object($body))
      $this->body = (object) $body;
    else
      $this->body = $body;
    return $this;
  }
  
  /**
   * @return \stdClass
   */
  public function getBody() {
    return $this->body;
  }
  
  /**
   * @return bool
   */
  public function isXMLHTTPRequest() {
    return $this->header->getField('X-Requested-With') === 'XMLHttpRequest';
  }
  
  /**
   * @return bool
   */
  public function isAjax() {
    return $this->isXMLHTTPRequest();
  }
  
  /**
   * @param Psc\Net\HTTP\RequestHeader $header
   * @chainable
   */
  public function setHeader(RequestHeader $header) {
    $this->header = $header;
    return $this;
  }
  
  /**
   * @return Psc\Net\HTTP\RequestHeader
   */
  public function getHeader() {
    return $this->header;
  }
  
  /**
   * @chainable
   */
  public function setHeaderField($name, $value) {
    $this->header->setField($name,$value);
    return $this;
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
  
  public function getVersion() {
    return $this->header->getVersion();
  }
  
  /**
   * @param array $query
   * @chainable
   */
  public function setQuery(Array $query) {
    $this->query = $query;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getQuery() {
    return $this->query;
  }
  
  /**
   * @return string|NULL wenn string dann ist schon ein ? davor
   */
  public function getQueryString() {
    return count($this->query) > 0 ? '?'.http_build_query($this->query) : NULL;
  }
  
  public function getResource() {
    return $this->resource;
  }
  
  public function debug() {
    $converter = new JSONConverter();

    $text  = "== Psc\Net\HTTP\Request =========================\n";
    $text .= $this->method.' : /'.$this->resource.($this->getQueryString())."\n";
    $text .= $this->getHeader()."\n";
    $text .= "== Request-Body =================================\n";
    $text .= $converter->stringify($this->body, JSONConverter::PRETTY_PRINT)."\n";
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
   * @param array $files
   */
  public function setFiles(Array $files) {
    $this->files = $files;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getFiles() {
    return $this->files;
  }
  
  /**
   * @param string $referer
   */
  public function setReferer($referer) {
    $this->referer = $referer;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getReferer() {
    return $this->referer;
  }
  
  /**
   * @param array $preferredLanguages
   */
  public function setPreferredLanguages(Array $preferredLanguages) {
    $this->preferredLanguages = $preferredLanguages;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getGreferredLanguages() {
    return $this->preferredLanguages;
  }

  /**
   * @return bool
   */
  public function isContentType($contentType) {
    return mb_strpos($this->getHeaderField('Content-Type'), $contentType) === 0;
  }
}