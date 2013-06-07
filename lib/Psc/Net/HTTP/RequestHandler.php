<?php

namespace Psc\Net\HTTP;

use Psc\Net\Service;
use Psc\Net\ServiceRequest;
use Psc\Net\ServiceResponse;
use Psc\Net\FormatServiceResponse;
use Psc\System\Logger;
use Psc\System\BufferLogger;
use Psc\Code\Code;
use Psc\CMS\Service\MetadataGenerator;
use Psc\Net\ServiceErrorPackage;

/**
 * Verwaltet den Main Request der Applikation
 * 
 * Nimmt den Main Request, routet und schickt Ihn an den Service in einem HTTP-Unabhängigen Format
 * Nimmt die (HTTP-unabhängige) Antwort vom Service und wandelt diese in eine HTTP-Response um und gibt diese zurück
 */
class RequestHandler extends \Psc\System\LoggerObject {
  
  protected $services = array();
  
  /**
   * @var Psc\Net\Service
   */
  protected $service;
  
  /**
   * @var Psc\Net\HTTP\Request
   */
  protected $request;
  
  /**
   * @var Psc\Net\HTTP\Response
   */
  protected $response;
  
  /**
   * Das DebugLevel für den RequestHandler
   * 
   * ist das DebugLevel >= 10 werden Exceptions nicht gefangen, sondern geworfen
   */
  protected $debugLevel = 0;
  
  /**
   * @var Psc\Net\HTTP\ResponseConverter
   */
  protected $responseConverter;
  
  /**
   * @var Psc\Net\HTTP\RequestConverter
   */
  protected $requestConverter;
  
  
  /**
   * Ein Helper für die Umwandlung von ValidationErrors zu Responses
   *
   * natürlich kann das ServiceErrorPackage eigentlich noch mehr, aber hierfür wird es gerade gebraucht
   * @var Psc\Net\ServiceErrorPackage
   */
  protected $err;
  
  protected $ignoredErrors = array(404);
  
  /**
   * Einen Service muss es mindestens geben
   */
  public function __construct(Service $service, ResponseConverter $responseConverter = NULL, Logger $logger = NULL, MetadataGenerator $metadataGenerator = NULL, RequestConverter $requestConverter = NULL, ServiceErrorPackage $errorPackage = NULL) {
    $this->setLogger($logger ?: new BufferLogger());
    $this->responseConverter = $responseConverter ?: new ResponseConverter();
    $this->requestConverter = $requestConverter ?: new RequestConverter();
    $this->metadataGenerator = $metadataGenerator ?: new MetadataGenerator();
    $this->err = $errorPackage ?: new ServiceErrorPackage($this);
    
    $this->addService($service);
  }
  
  /**
   * @return Response
   */
  public function handle(Request $request) {
    $this->log($request->debug());
    $this->request = $request;
    
    try {

      $serviceRequest = $this->createServiceRequest($this->request);
    
      $serviceResponse = $this->route($serviceRequest);
    
      $this->response = $this->convertResponse($serviceResponse);
    
    } catch (HTTPResponseException $e) {
      $this->response = Response::create($e->getCode(),
                                         $e->getResponseBody(),
                                         $e->getHeaders()
                                        );
    } catch (HTTPException $e) {
      // die darstellung sollte eigentlich woanders sein, nicht hier
      // eigentlich müssten wir hier auch request->content-type auswerten und response umwandeln
      $this->response = Response::create($e->getCode(),
                                         sprintf("%s\n\n%s\n%s",
                                                 $e->getResponseBody(),
                                                 $request->getResource(),
                                                 $this->getDebugInfo()),
                                         $e->getHeaders()
                                        );
    
    } catch (NoServiceFoundException $e) {
      $this->logError($e, $this->debugLevel, 1);
      $e->setCode(404);
      $this->response = Response::create(404, sprintf("Es wurde kein Service für: %s gefunden\n\n%s",
                                                      $request->getResource(),
                                                      $this->getDebugInfo()
                                                      ));
      
      
    } catch (\Psc\CMS\Service\ControllerRouteException $e) {
      $this->logError($e, $this->debugLevel, 5); // nicht 1 da das mit dem Klassennamen ja schon "interna" sind
      $e->setCode(404);
      $this->response = Response::create(404, sprintf($e->getMessage()."\n\n%s",
                                                      $this->getDebugInfo()
                                                      ));
      
    } catch (\Exception $e) {
      $this->logError($e, $this->debugLevel, 1);
      $this->response = Response::create(500, sprintf("Es ist ein Fehler aufgetreten. URL: %s%s\n\n%s",
                                                      $request->getResource(),
                                                      ($this->debugLevel >= 5 ? "\nFehler: ".$e->getMessage() : NULL),
                                                      $this->getDebugInfo()
                                                      ), $this->getErrorMessageHeaders($e));
    }
    
    if (isset($e)) { // oder halt code >= 400
      if (!$this->isIgnoredError($e)) {
        $contextInfo = 'im RequestHandler. '."\n";
        $contextInfo .= '  '.$request->getMethod().' /'.implode('/',$request->getParts())."\n";
        $contextInfo .= '  Referrer: '.$request->getReferrer()."\n";
        $contextInfo .= '  User-Agent: '.$request->getUserAgent();
        if (isset($this->contextInfo)) {
          $contextInfo .= "\n".$this->contextInfo;
        }
        $contextInfo .= "\n".$this->dumpRequest($request);
        \Psc\PSC::getEnvironment()->getErrorHandler()->handleCaughtException($e, $contextInfo);
      }
    }
    
    return $this->response;
  }

  /**
   * @return ServiceResponse
   */
  public  function route(ServiceRequest $serviceRequest) {
    try {

      $this->service = $this->findService($serviceRequest);
      
      return $this->service->route($serviceRequest);

    } catch (\Psc\Form\ValidatorException $e) {
      $this->logError($e, $this->debugLevel, 2);
      throw HTTPException::BadRequest(
        sprintf(
          "Beim Validieren der Daten ist ein Fehler aufgetreten. %s",
          //is_array($e->field) ? implode(' ',$e->field) : $e->field,
          $e->getMessage()
        ),
        $e,
        array_merge(
          array('Content-Type'=>'text/html'),
          $this->metadataGenerator->validationError($e)->toHeaders()
        )
      );

    } catch (\Psc\Form\ValidatorExceptionList $list) {
      $this->logError($list, $this->debugLevel, 2);
      
      throw $this->err->validationResponse(
        $list,
        $this->request->accepts('application/json')
          ? ServiceResponse::JSON : ServiceResponse::HTML,
        $this->metadataGenerator
      );
    }
  }
  
  protected function isIgnoredError(\Exception $e) {
    return in_array($e->getCode(), $this->ignoredErrors);
  }
  
  protected function getErrorMessageHeaders(\Exception $e) {
    if ($this->debugLevel >= 5) {
      // flatten is wichtig für Header() von PHP
      $message = $e->getMessage().' '.$e->getFile().':'.$e->getLine();
      $message = str_replace(array("\n","\"r"), ' ',$message);
    } else {
      $message = 'Message is hidden. Increase DebugLevel.';
    }
    
    return array(
      'X-Psc-CMS-Error'=>'true',
      'X-Psc-CMS-Error-Message'=>$message
    );
  }
  
  /**
   * Sucht den ersten der Services, der den Request bearbeiten will
   * 
   * @throws NoServiceFoundException
   */
  public function findService(ServiceRequest $serviceRequest) {
    $this->log('Find Services..');
    foreach ($this->services as $service) {
      $this->log('Service: '.Code::getClass($service).'::isResponsibleFor');
      if ($service->isResponsibleFor($serviceRequest)) {
        $this->log('ok: Service übernimmt den Request');
        return $service;
      }
    }
    
    throw NoServiceFoundException::build(
        'Es konnte kein passender Service ermittelt werden. Es wurden %d Services (%s) befragt.',
        count($this->services),
        \Webforge\Common\ArrayUtil::implode($this->services, ', ', function ($svc) { return Code::getClass($svc); })
    )->set('serviceRequest',$serviceRequest)
     ->end();
  }
  
  /**
   * Erstellt einen ServiceRequest anhand des HTTP-Request
   * 
   * 
   * ist der header X-Psc-CMS-Request-Method gesetzt wird dieser String als serviceRequest::$type genommen
   * (erlaubt z.b. PUT über jQuery zu benutzen)
   * @return ServiceRequest
   */
  public function createServiceRequest(Request $request) {
    return $this->requestConverter->fromHTTPRequest($request);
  }
  
  /**
   * Wandelt eine ServiceResponse in eine HTTP-Response um
   * 
   * wandelt auch den komplexen Inhalt der ServiceResponse um. D. h. unsere HTTP-Response ist dann eine "dumme" Response
   * diese Funktion ist das Gegenstück zu createServiceRequest
   * @return Psc\Net\HTTP\Response
   */
  public function convertResponse(ServiceResponse $response) {
    return $this->responseConverter->fromService($response, $this->request);
  }
  
  /**
   * Fügt einen Service hinzu
   * 
   * spätere Services werden immer auch später nach isResponsibleFor()- gefragt (bis jetzt)
   */
  public function addService(Service $service) {
    if (!in_array($service, $this->services, TRUE)) {
      $this->services[] = $service;
      
      if ($service instanceof \Psc\System\LoggerObject && $service->getLogger() instanceof \Psc\System\DispatchingLogger) {
        $this->getLogger()->listenTo($service->getLogger());
      }
    }
    return $this;
  }
  
  /**
   * Gibt alle Services zurück
   * 
   * @return array
   */
  public function getServices() {
    return $this->services;
  }
  
  /**
   * Gibt den aktuellen Service zurück, der gerade den letzten Request gehandled hat
   * @return Psc\Net\Service
   */
  public function getService() {
    return $this->service;
  }
  
  /**
   * @return Psc\Net\HTTP\Request
   */
  public function getRequest() {
    return $this->request;
  }
  
  /**
   * @return Psc\Net\HTTP\Response
   */
  public function getResponse() {
    return $this->response;
  }
  
  public function getDebugInfo() {
    if ($this->debugLevel > 0) {
      return sprintf("DebugInfo [level: %d]:\n%s",
                     $this->debugLevel,
                     $this->logger->toString()
                  );
    }
  }
  
  /**
   * returns a importable string representation of the request
   * @return string
   */
  protected function dumpRequest(Request $request) {
    $body = $request->getBody();
    
    $dump = "Body of request is:\n";
    
    if (is_object($body) || is_array($body)) {
      $dump .= json_encode($body);
    } elseif(is_string($body)) {
      $dump .= sprintf('"%s"',$body);
    }
    
    return $dump;
  }
  
  /**
   * Setzt das DebugLevel für den RequestHandler
   * 
   * ein Level >= 1 bedeutet, dass für jede (Fehler-)Response DebugInformationen angehängt werden
   * @param int $debugLevel
   * @chainable
   */
  public function setDebugLevel($debugLevel) {
    $this->debugLevel = max(0,$debugLevel);
    return $this;
  }
  
  /**
   * @return int
   */
  public function getDebugLevel() {
    return $this->debugLevel;
  }
  
  /**
   * @param string $contextInfo
   * @chainable
   */
  public function setContextInfo($contextInfo) {
    $this->contextInfo = $contextInfo;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getContextInfo() {
    return $this->contextInfo;
  }
  
  /**
   * Überschreibt alle Services
   */
  public function setServices(Array $services) {
    $this->services = array();
    foreach ($services as $service) {
      $this->addService($service);
    }
    return $this;
  }
  
  /**
   * @param Psc\Net\HTTP\RequestConverter $requestConverter
   */
  public function setRequestConverter(RequestConverter $requestConverter) {
    $this->requestConverter = $requestConverter;
    return $this;
  }
  
  /**
   * @return Psc\Net\HTTP\RequestConverter
   */
  public function getRequestConverter() {
    return $this->requestConverter;
  }
}
