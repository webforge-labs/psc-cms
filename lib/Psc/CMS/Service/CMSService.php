<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceResponse;
use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Net\RequestMatcher;
use Psc\Net\HTTP\HTTPException;
use Psc\CMS\Project;
use Psc\Doctrine\DCPackage;
use Psc\CMS\Controller\ImageController;

/**
 * Der Standard Service fürs CMS
 *
 * Wird in Project nicht getMainService() überschrieben, so wird immer dieser Service an den FrontController (ohne Parameter) übergeben
 */
class CMSService extends ControllerService {
  
  protected $project;

  public function __construct(Project $project, $prefixPart = 'cms', DCPackage $dc = NULL) {
    $this->prefixPart = $prefixPart;
    $this->dc = $dc;
    parent::__construct($project);
  }
  
  public function routeController(ServiceRequest $request) {
    $r = $this->initRequestMatcher($request);
    
    $controller = $r->qmatchiRx('/^(tpl|excel|images|uploads|persona)$/i');
    
    if ($controller === 'tpl') {
      $x = 0;
      $tpl = array();
      while (!$r->isEmpty() && $x <= 10) {
        $tpl[] = $r->qmatchRx('/^([-a-zA-Z0-9_.]+)$/');
        $x++;
      }
      
      $controller = new \Psc\CMS\Controller\TPLController($this->project);
      
      return array($controller, 'get', array($tpl));
    
    } elseif ($controller === 'excel') {
      
      $controller = new \Psc\CMS\Controller\ExcelController($this->project);
      
      if ($request->getType() === Service::POST) {
        $body = $request->getBody();
        
        if ($r->part() === 'convert') {
          // importieren

          // im body können dann options stehen oder sowas
          // der controller holt sich die excelFile selbst
          return array($controller, 'convert', array(is_object($body) ? $body : new \stdClass)); 

        } else {
          // exportieren
          
          return array($controller, 'create', array($body,$r->part())); // nächste ist filename ohne endung
        }
      }
    } elseif ($controller === 'images') {
      $controller = new ImageController(
        new \Psc\Image\Manager(
          $this->getDoctrinePackage()->getModule()->getEntityName('Image'),
          $this->getDoctrinePackage()->getEntityManager()
        )
      );
      
      if ($r->isEmpty() && $request->getType() === Service::POST && $request->hasFiles()) {
        // nimmt nur eine file, weil moep
      
        return array($controller, 'insertImageFile', array(current($request->getFiles()), $request->getBody()));
      } else {
        $method = 'getImage';
        $cacheAdapters = array('thumbnail');
        $params = array($r->qmatchiRX('/([a-z0-9]+)/')); // id or hash

        // filename immer am ende und optional
        $filename = NULL;
        if (\Psc\Preg::match($r->getLastPart(), '/[a-z0-9]+\.[a-z0-9]+/i')) {
          $filename = $r->pop();
        }
      
        /* gucken ob es eine Version des Images werden soll */
        if (in_array($r->part(), $cacheAdapters)) {
          $method = 'getImageVersion';
          $params[] = $r->matchNES(); // type
          $params[] = $r->getLeftParts(); // parameter für den cache adapter
        }
      
        if ($filename) {
          $params[] = $filename;
        }
        
        return array($controller, $method, $params);
      }
      

    } elseif ($controller === 'uploads') {
      $controller = new \Psc\CMS\Controller\FileUploadController(
        $this->getDoctrinePackage()
      );
      
      $this->log('upload-request method: '.$request->getType());
      $this->log('  has files: '.($request->hasFiles() ? 'true' : 'false'), 1);
      
      if ($r->isEmpty() && $request->getType() === Service::POST && $request->hasFiles()) {
        // nimmt nur eine file, weil moep
      
        return array($controller, 'insertFile', array(current($request->getFiles()), $request->getBody()));
      } elseif($r->isEmpty() && $request->getType() === Service::GET) {
        $params = array();

        // criterias
        $params[] = array(); 

        $params[] = $r->matchOrderBy(
          $r->qVar('orderby'), 
          array('name'=>'originalName')
        );
        
        return array($controller, 'getFiles', $params);
      
      } else {
        $method = 'getFile';
        $params = array($r->qmatchiRX('/([a-z0-9]+)/')); // id or hash

        // filename immer am ende und optional
        $filename = NULL;
        if (\Psc\Preg::match($r->getLastPart(), '/^(.+)\.(.+)$/')) {
          $filename = $r->pop();
        }
      
        if ($filename) {
          $params[] = $filename;
        }
        
        return array($controller, $method, $params);
      }
    } elseif ($controller === 'persona') {
      $controller = new \Webforge\Persona\Controller();

      return array($controller, 'verify', array($r->bvar('assertion')));
    }
    
    throw HTTPException::NotFound('Die Resource für cms '.$request.' existiert nicht.');
  }

  public function initRequestMatcher(ServiceRequest $request) {
    $r = parent::initRequestMatcher($request);
    
    if (isset($this->prefixPart)) {
      $r->matchIValue($this->prefixPart);
      $this->logf("ok: matches Prefix: '%s'",$this->prefixPart);
    }
    
    return $r;
  }
  
  public function getDoctrinePackage() {
    if (!isset($this->dc)) {
      $this->dc = new DCPackage($doctrine = $this->project->getModule('Doctrine'), $doctrine->getEntityManager());
    }
      
    return $this->dc;
  }
  
  public function setDoctrinePackage(DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }
  
  public function getProject() {
    return $this->project;
  }
}
