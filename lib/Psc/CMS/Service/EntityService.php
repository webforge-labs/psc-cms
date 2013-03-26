<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Net\ServiceResponse;
use Psc\Code\Generate\GClass;
use Psc\Code\Code;
use Webforge\Common\ArrayUtil as A;
use Psc\Net\HTTP\HTTPException;
use Psc\Net\RequestMatcher;
use Psc\Inflector;
use Psc\CMS\Controller\Factory as ControllerFactory;

class EntityService extends ControllerService {
  
  /**
   * @var Psc\Doctrine\Module
   */
  protected $doctrine;
  
  protected $dc;
  
  protected $languages = array();
  protected $language = NULL;
  
  /**
   * Der erste Part eines Requestes, wenn er für den EntityService ist
   *
   * ist meist: "entities"
   * ergo: /entities/person/1/form
   *
   * ist er null ist:
   * /person/1/form okay
   */
  protected $prefixPart;

  /**
   * @var ControllerFactory
   */
  protected $controllerFactory;
  
  public function __construct(\Psc\Doctrine\DCPackage $dc, ControllerFactory $controllerFactory, \Psc\CMS\Project $project = NULL, $prefixPart = 'entities') {
    $this->dc = $dc;
    $this->doctrine = $this->dc->getModule();
    $this->prefixPart = $prefixPart;
    $this->controllerFactory = $controllerFactory;
    parent::__construct($project ?: $this->doctrine->getProject());
  }
  
  /**
   * Überprüft ob der Request zu diesem Service hört
   *
   * wir sind jetzt ein gemeiner Service und behaupten, dass alles was mit "entities" anfängt zu uns gehört
   * das ist praktisch um z.B. abfangen zu können, dass ein AbstractEntityController fehlt (um ihn automatisch zu erstellen)
   */
  protected function doResponsibleFor(ServiceRequest $request) {
    $r = $this->initRequestMatcher($request);
    
    // nach "entities/" kommt immer ein identifier 
    $entityPart = $r->qmatchRx('/[a-z0-9]+/i',0);
    
    return TRUE;
  }
  
  /**
   * Findet den Controller anhand des Requests
   * 
   * GET [/$prefix]/person/1
   *  =>
   * \Project::getNamespace()\Controllers\PersonController::getEntity(1)
   * =>
   * \CoC\Controllers\PersonController::getEntity(1)
   *
   * GET [/$prefix]/person/1/form
   * =>
   * \Project::getNamespace()\Controllers\PersonController::getEntity(1,'form')
   *
   * GET [/$prefix]/persons/grid?filter1=value1&filter2=value2
   * =>
   * \Project::getNamespace()\Controllers\PersonController::getEntities(array('filter1'=>'value1', 'filter2'=>'value2'),'grid')
   */
  public function routeController(ServiceRequest $request) {
    $r = $this->initRequestMatcher($request);
    
    $entityPart = $r->qmatchRx('/^[a-z-0-9]+$/i',0);

    if (mb_strpos($entityPart, '-') !== FALSE) {
      $entityPart = Code::dashToCamelCase($entityPart);
    }

    // alle weiteren Parameter an den Controller weitergeben
    $params = $r->getLeftParts();
    
    if ($request->getType() === self::GET) {
      $this->log('EntityPart: '.$entityPart.' '.($this->isPlural($entityPart) ? 'ist plural' : 'ist singular'), 2);

      if ($this->isPlural($entityPart)) {
        if ($r->part() === 'form') {
          $method = 'getNewEntityFormular';
        } else {
          $method = 'getEntities';
          A::insert($params, $request->getQuery(), 0); // query als 1. parameter
        }
        $entityPart = Inflector::singular($entityPart);

      } else {
        $method = 'getEntity';
        $params = array();
        $params[] = $r->shift(); // id
        $params[] = count($r->getLeftParts()) > 1 ? $r->getLeftParts() : $r->shift(); // subresource
        $params[] = $request->getQuery();
      }

    } elseif ($request->getType() === self::PUT) {
      if ($r->part() === 'grid') {
        $entityPart = Inflector::singular($entityPart);
        $controller = $this->getEntityController($entityPart);
  
        $method = 'saveSort';
        $params = array($r->bvar($controller->getSortField(), array()));
      } elseif($request->hasMeta('revision')) {
        $method = 'saveEntityAsRevision';
        
        A::insert($params, $request->getMeta('revision'), 1);
        A::insert($params, (object) $request->getBody(), 2);
      } else {
      
        $method = 'saveEntity';
        A::insert($params, (object) $request->getBody(), 1); // $formData als parameter 2
      }

    } elseif ($request->getType() === self::PATCH) {
      $method = 'patchEntity';
      A::insert($params, (object) $request->getBody(), 1); // $formData als parameter 2
      
    } elseif ($request->getType() === self::DELETE) {
      $method = 'deleteEntity'; // das gibt einen "missing argument 1" fehler, wenn id fehlt, aber ka welche httpException ich hier nehmensoll, deshalb bleibt das erstmal so

    } elseif ($request->getType() === self::POST) {
      $entityPart = Inflector::singular($entityPart); // singular und plural okay
      
      A::insert($params, $request->getBody(), 0); // $formData als parameter 1
      
      if ($request->hasMeta('revision')) {
        $method = 'insertEntityRevision';
        A::insert($params, $request->getMeta('revision'), 0);
      } else {
        $method = 'insertEntity';
      }
      
    } else {
      // das kann glaub ich nicht mehr passieren, weil wir jetzt alle haben: put/pust/delete/get gibts nicht noch head?
      throw HTTPException::MethodNotAllowed('Die Methode: '.$request->getType().' ist für diesen Request nicht erlaubt');
    }
    
    if (!isset($controller))
      $controller = $this->getEntityController($entityPart);
    
    return array($controller, $method, $params);
  }
  
  public function getEntityController($part) {
    $entityClass = $this->doctrine->getEntityName($part);
    $entityName = Code::getClassName($entityClass);

    return $this->controllerFactory->getController($entityName);
  }
  
  /**
   * Konvertiert die Responses der Controller in Responses für den RequestHandler
   */
  protected function setResponseFromControllerResponse($controllerResponse, $status = Service::OK, $format = NULL) {
    
    if ($controllerResponse instanceof \Psc\CMS\EntityFormPanel) {
      $format = ServiceResponse::HTML;

    } elseif ($controllerResponse instanceof \Psc\CMS\Entity) {
      $format = ServiceResponse::JSON;

    } elseif ($controllerResponse instanceof \Psc\Doctrine\Object) {
      $format = ServiceResponse::JSON;
    }
    
    parent::setResponseFromControllerResponse($controllerResponse, $status, $format);
  }
  
  protected function isPlural($name) {
    // das ist nicht so schön, aber ich hab grad keine schönere idee als eine liste aller entities zu haben (entity meta?)
    return Inflector::plural(Inflector::singular($name)) === $name;
  }

  public function getRepository($entityClass) {
    return $this->doctrine->getRepository($entityClass);
  }
  
  public function initRequestMatcher($request) {
    $r = new RequestMatcher($request);
    
    if (isset($this->prefixPart)) {
      $r->matchIValue($this->prefixPart);
      $this->logf("ok: matches Prefix: '%s'",$this->prefixPart);
    }
    
    return $r;
  }

  public function setControllerClass($controllerName, $controllerClass) {
    parent::setControllerClass($controllerName, $controllerClass);
    $this->controllerFactory->setControllerFQN($controllerName, $controllerClass);
    return $this;
  }

  public function setControllersNamespace($ns) {
    parent::setControllersNamespace($ns);
    $this->controllerFactory->setDefaultNamespace($ns);
    return $this;
  }

  
  /**
   * @param array $languages
   * @chainable
   */
  public function setLanguages(array $languages) {
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