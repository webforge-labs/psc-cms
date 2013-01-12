<?php

namespace Psc\Net\HTTP;

use Psc\PSC;
use Webforge\Common\System\File;

/**
 * Der FrontController delegiert an weitere Request-Handler und gibt immer eine Response aus
 * 
 * auch bei Exceptions uns Fehlern wird hier was ausgegeben (meist dann HTTP:500)
 * alles interessante macht der RequestHandler
 * 
 * auch bin ich mir nciht sicher, wann das "Main" Der Applikation gestartet werden sollte
 */
class FrontController extends \Psc\SimpleObject {
  
  protected $request;
  
  protected $response;
  
  protected $requestHandler;
  
  protected $debugLevel = 0;
  
  /**
   * Eine Map von integer => File
   *
   * die File beeinhaltet dann eine standalone html datei, die den Fehler anzeigt
   * @var array
   */
  protected $errorDocuments;
  
  public function __construct(RequestHandler $requestHandler = NULL, $debugLevel = 0, Array $errorDocuments = array()) {
    $this->requestHandler = $requestHandler ?:
      new RequestHandler(PSC::getProject()->getMainService());
    
    $this->setDebugLevel($debugLevel);
    $this->setErrorDocuments($errorDocuments);
  }
  
  public function init(Request $request = NULL) {
    $this->request = $request ?: Request::infer();
    return $this;
  }
  
  public function process() {
    $response = $this->handle($this->request);
    /*
    $log = $this->requestHandler->getLogger()->toString();
    if (!empty($log)) {
      $lines = explode("\n",$log);
      $fb = new FirePHP();
      foreach ($lines as $msg) {
        $fb->log($msg);
      }
    }
    */
    
    if ($this->debugLevel == 0) {
      if (array_key_exists($response->getCode(), $this->errorDocuments)) {
        $capture = function ($document) {
          ob_start();
          require $document;
          $body = ob_get_contents();
          ob_end_clean();
          
          return $body;
        };
        
        $response->setBody($capture($this->errorDocuments[$response->getCode()]));
      }
    }
    
    $response->output();
    
    return $this;
  }
  
  /**
   * @return Psc\Net\HTTP\Response
   */
  public function handle(Request $request) {
    try {
    
      // RequestHandler aufrufen, der muss gehen und eine response zurückgeben
      $this->response = $this->requestHandler->handle($request);
    
      if ($this->response instanceof Response) {
        return $this->response;
      
      } elseif ($this->response instanceof \Psc\CMS\Ajax\Response) { // legacy
        return Response::create(200, $response->export(), array('Content-Type', $response->getContentType()));
    
      } else {
        throw new \Psc\Exception('interner Fehler: RequestHandler gibt keine passende Response zurück');
      }
    
    } catch (\Exception $e) {
      /* dies ist nur das Ausnahmen / Ausnahmen Request-Handling. Eine 500 sollte im normalen Betrieb nie vorkommena
         sondern der RequestHandler sollte immer eine Response umwandeln
      */
      if ($this->debugLevel >= 10) {
        throw $e; // rethrow denn wir machen das schon im RequestHandler alles
      } else {
        return Response::create(500, 'Es ist ein Interner HTTP-Handler Fehler (Frontcontroller) aufgetreten: '.$e->getMessage());
      }
    }
  }
  
  public function getRequestHandler() {
    return $this->requestHandler;
  }
  
  public function getRequest() {
    return $this->request;
  }
  
  public function getResponse() {
    return $this->response;
  }
  
  public function setDebugLevel($level) {
    $this->debugLevel = max(0,$level);
    $this->requestHandler->setDebugLevel($this->debugLevel);
    return $this;
  }
  
  public function addErrorDocument($httpErrorCode, File $document) {
    if ($document->isReadable()) {
      $this->errorDocuments[$httpErrorCode] = $document;
    } else {
      throw new \InvalidArgumentException($document.' ist nicht lesbar.');
    }
    return $this;
  }
  
  /**
   * @param array $errorDocuments
   */
  public function setErrorDocuments(Array $errorDocuments) {
    $this->errorDocuments = $errorDocuments;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getErrorDocuments() {
    return $this->errorDocuments;
  }
}
?>