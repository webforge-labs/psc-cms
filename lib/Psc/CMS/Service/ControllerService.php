<?php

namespace Psc\CMS\Service;

use Psc\Net\Service;
use Psc\Net\ServiceResponse;
use Psc\Net\ServiceRequest;
use Psc\CMS\Controller\ServiceController;
use Psc\CMS\Controller\TransactionalController;
use Psc\Code\Callback;
use Psc\Code\Code;
use Psc\A;
use Psc\CMS\Project;

use Psc\Net\HTTP\HTTPException;
use Psc\ExceptionDelegator;
use Psc\ExceptionListener;
use Psc\Code\Generate\GClass;

/**
 * @TODO das Controller Erstellen muss in eine Factory ausgelagert werden
 * sodass man auch weitere Parameter an den Constructor injecten kann
 * außerdem hat das ganze Konstrukten hier nix verloren
 * 
 */
abstract class ControllerService extends \Psc\System\LoggerObject implements \Psc\Net\Service, \Psc\ExceptionDelegator {
  
  protected $exceptionListeners = array();
  
  protected $project;
  
  protected $logger;
  
  protected $controllersNamespace;
  
  /**
   * Mapping von Namen zu Controller-Klassen (wenn man diese Injecten möchte)
   * 
   * @var array
   */
  protected $controllerClasses = array();
  
  protected $language;
  protected $languages = array();

  public function __construct(Project $project, \Psc\System\DispatchingLogger $logger = NULL) {
    $this->project = $project;
    $this->setLogger($logger ?: new \Psc\System\BufferLogger());
  }
  
  /**
   * Gibt True zurück wenn der Service den ServiceRequest erfolgreich bearbeiten kann
   *
   * @todo "erfolgreich" definieren
   * @return bool
   */
  public function isResponsibleFor(ServiceRequest $request) {
    /*
      hier könnte man mal den request hashen, dann den controller suchen und zu dem hash speichern
      dann könnte man den controller bei route() direkt aufrufen
    */
    $this->log('überprüfe ob verantwortlich für: '.$request->debug());

    try {
      return $this->doResponsibleFor($request);

    } catch (\Psc\CMS\Service\ControllerRouteException $e) {
      $this->log('fail: '.$e->getMessage());
    } catch (\Psc\Net\HTTP\HTTPException $e) {
      $this->log('fail: '.$e->debug());
    } catch (\Psc\Net\RequestMatchingException $e) {
      $this->log('fail: '.$e->getMessage());
    }
    
    return FALSE;
  }
  
  protected function doResponsibleFor(ServiceRequest $request) {
    // wenn dies keine exceptions hier gibt, gebe true zurück
    list ($controller, $method, $params) = $this->routeController($request);
    
    $this->validateController($controller, $method, $params);
    
    return TRUE;
  }
  
  /**
   * Führt den ServiceRequest aus
   *
   * ruft den passenden ServiceController auf
   * @return ServiceResponse
   */
  public function route(ServiceRequest $request) {
    list ($controller, $method, $params) = $this->routeController($request);
    
    $this->validateController($controller, $method, $params);
    
    $this->runController(
      $controller, $method, $params
    );
    
    return $this->response;
  }

  /**
   * Findet den Controller anhand des Requests
   *
   * sollte unbedingt getControllerInstance() benutzen, um den Controller zu erstellen
   * sollte kein Controller gefunden werden können (sprich: der Service kümmert sich nicht um diesen Request), sollte eine ControllerRouteException geworfen werden
   * @return Psc\CMS\Controller\ServiceController
   */
  abstract public function routeController(ServiceRequest $request);
  
  public function validateController($controller, $method, Array $params) {
    if (!is_object($controller) || !method_exists($controller, $method)) {
      throw HTTPException::NotFound('Methode '.Code::getClass($controller).'::'.$method.' ist nicht definiert');
    }
  }
  
  /**
   * Führt den Controller aus
   *
   * catched dabei Exceptions und wandelt diese gegebenfalls in Error-Responses um
   */
  public function runController($controller, $method, Array $params) {
    $transactional = $controller instanceof \Psc\CMS\Controller\TransactionalController;
    
    try {
      try {
        $this->logf('running Controller: %s::%s(%s)', Code::getClass($controller), $method, implode(', ',array_map(function ($param) { return Code::varInfo($param); }, $params)));
        $cb = new Callback($controller, $method);
        $controllerResponse = $cb->call($params);
        
        $this->log('Converting Controller Response');
        $this->setResponseFromControllerResponse($controllerResponse);
        $this->log('Response Format: '.($this->response->getFormat() ?: 'null'));
        $this->log('successful run');
        
        $this->log('überprüfe ResponseMetadataController');
        if ($controller instanceof \Psc\CMS\Controller\ResponseMetadataController && ($meta = $controller->getResponseMetadata()) != NULL) {
          $this->response->setMetadata($meta);
          $this->log('Metadaten übertragen');
        }
        
      /* PDO Exceptions */
      } catch (\PDOException  $e) {
        throw \Psc\Doctrine\Exception::convertPDOException($e);
      }

    } catch (\Exception $e) {
      if ($transactional && $controller->hasActiveTransaction())
        $controller->rollbackTransaction();
      
      $this->handleException($e);
    }
  }
  
  protected function setResponseFromControllerResponse($controllerResponse, $status = Service::OK, $format = NULL) {
    if ($controllerResponse instanceof ServiceResponse) {
      $this->response = $controllerResponse;
    } else {
      $this->response = new ServiceResponse($status, $controllerResponse, $format);
    }
  }
  
  protected function handleException(\Exception $exception) {
    /* Check Exception Listeners
       diese können z. B. eine Doctrine\UniqueConstraintException in eine ValidatorException umwandeln
    */
    $exceptionClass = Code::getClass($exception);
    if (array_key_exists($exceptionClass,$this->exceptionListeners)) {
      foreach ($this->exceptionListeners[$exceptionClass] as $listener) {
        if (($listenerException = $listener->listenException($exception)) != NULL) {
          $exception = $listenerException;
        }
      }
    }

    // wir geben zurück an den RequestHandler
    throw $exception;
  }
  
  /* INTERFACE ExceptionDelegator */
  /**
   *   Der ExceptionListener sollte in listenException eine Exception zurückgeben, wenn er will, dass diese umgewandelt wird
   *   im Moment werden nur ValidatorExceptions separat behandelt (alle anderen werden in ExceptionResponse umgewandelt)
   *   gibt er NULL zurück, passiert nichts
   *
   *   spätere Listener, die auch etwas zurückgeben überschreiben die Rückgabewerte der vorigen
   *   es werden jedoch alle Listener aufgerufen
   */
  public function subscribeException($exception, ExceptionListener $listener) {
    $this->exceptionListeners[$exception][] = $listener;
  }
  /* END INTERFACE ExceptionDelegator */
  
  /**
   * @return Object
   */
  public function getControllerInstance(GClass $class) {
    $controller = $class->getReflection()->newInstanceArgs(array_slice(func_get_args(),1));
    
    return $controller;
  }
  
  /**
   * @return Psc\Code\Generate\GClass
   */
  public function getControllerClass($controllerName, $check = TRUE) {
    if (array_key_exists($controllerName, $this->controllerClasses)) {
      $gClass = new GClass($this->controllerClasses[$controllerName]);
      $this->log(sprintf("Ueberschreibe Klasse %s fuer ControllerName: %s.", $gClass->getFQN(), $controllerName), 5); 
    } else {
      $gClass = new GClass();
      $gClass->setClassName($controllerName.'Controller');
      $gClass->setNamespace($this->getControllersNamespace());
      $this->log(sprintf("Klasse %s für ControllerName: '%s' mit Controllernamespace generiert.", $gClass->getFQN(), $controllerName), 5); 
    }
    
    try {
      if ($check && !$gClass->exists()) { // autoloads
        throw new \Psc\Exception('Klasse existiert nicht');
      }
    } catch(\Exception $e) { // sieht so kompliizert aus, damit noch mehr exceptions gecatched werden als diese unsere eigene
      throw ControllerRouteException::missingController($gClass, $e);
    }
    
    return $gClass;
  }
  
  /**
   * Überschreibt einen generierte ControllerClass durch eine spezielle
   *
   * der $controllerName ist ein bißchen abhängig von der Implementierung des ableitenden Service.
   * 
   * Z. B. beim EntityService ist dies nur der ClassName des Controllers (also die Klasse ohne Namespace). Dies ist nicht der EntityName (der wäre ja lowercase)
   */
  public function setControllerClass($controllerName, $controllerClass) {
    $this->controllerClasses[$controllerName] = $controllerClass;
    return $this;
  }

  /**
   * Gibt den Namespace für die Controllers des Service zurück
   * 
   * ohne \ davor und dahinter
   */
  public function getControllersNamespace() {
    if (!isset($this->controllersNamespace)) {
      $this->controllersNamespace = $this->project->getNamespace().'\\Controllers'; // siehe auch CreateControllerCommand
    }
    return $this->controllersNamespace;
  }
  
  public function setControllersNamespace($ns) {
    $this->controllersNamespace = $ns;
    return $this;
  }
  
  /**
   * @param Psc\CMS\Project $project
   * @chainable
   */
  public function setProject(Project $project) {
    $this->project = $project;
    return $this;
  }

  /**
   * @return Psc\CMS\Project
   */
  public function getProject() {
    return $this->project;
  }
  
  /**
   * @return Psc\CMS\Service\Response\Response
   */
  public function getResponse() {
    return $this->response;
  }
  
  
  /**
   * @param array $languages
   * @chainable
   */
  public function setLanguages(Array $languages) {
    $this->languages = $languages;
    return $this;
  }

  /**
   * @return array
   */
  public function getLanguages() {
    return $this->languages;
  }

  /**
   * @param string $language
   * @chainable
   */
  public function setLanguage($language) {
    $this->language = $language;
    return $this;
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }
}
?>