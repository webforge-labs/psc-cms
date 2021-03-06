<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceResponse;
use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Net\RequestMatcher;
use Psc\Net\HTTP\HTTPException;
use Webforge\Framework\Project;
use Psc\Doctrine\DCPackage;
use Psc\CMS\Controller\ImageController;
use Psc\CMS\Controller\FileUploadController;
use Psc\CMS\UploadManager;
use Psc\Image\Manager as ImageManager;
use Psc\CMS\Controller\ExcelController;
use Psc\CMS\Controller\TPLController;

/**
 * The Default Service for several commonly used Controllers for the frontend
 *
 * @TODO remove GLOBALS['env']['container']
 * @TODO use ControllerFactory to create the controllers (and inject it here)
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
      
      $controller = new TPLController($this->project);
      
      return array($controller, 'get', array($tpl));
    
    } elseif ($controller === 'excel') {
      
      $controller = new ExcelController($this->project);
      
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
        ImageManager::createForProject(
          $this->project,
          $this->getDoctrinePackage()->getEntityManager(),
          $this->getDoctrinePackage()->getModule()->getEntityName('Image')
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
      $controller = new FileUploadController(
        UploadManager::createForProject($this->project, $this->dc)
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

        try {
          if (\Psc\Preg::match($r->getLastPart(), '/^(.+)\.(.+)$/')) {
            $filename = $r->pop();
          }
        } catch (\Webforge\Common\Exception $e) {
          if (mb_strpos('.', $r->getLastPart()) !== FALSE) {
            $filename = \Webforge\Common\System\File::safeName($r->pop());
            $filename = Preg::replace($filename, '/_+/', '_');
          }
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
      $doctrine = $GLOBALS['env']['container']->getModule('Doctrine');
      $this->dc = new DCPackage($doctrine, $doctrine->getEntityManager());
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
