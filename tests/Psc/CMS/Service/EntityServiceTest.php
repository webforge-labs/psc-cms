<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Code\Generate\GClass;
use Psc\CMS\Controller\Factory;

/**
 * @group class:Psc\CMS\Service\EntityService
 */
class EntityServiceTest extends \Psc\Code\Test\ServiceBase {
  
  protected $svc;
  protected $dc;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\EntityService';
    parent::setUp();
    
    $this->project = clone \Psc\PSC::getProject();
    $this->dc = new \Psc\Doctrine\DCPackage($this->project->getModule('Doctrine'));

    $this->dependencies = $this->getMock('Psc\CMS\Roles\ControllerDependenciesProvider', array(), array(), '', FALSE);
    
    $this->svc = new EntityService($this->dc, $this->project, 'entities', new Factory('Psc\Test\Controllers', $this->dependencies));
  }
  
  public function testControllerRoute_getEntity() {
    /* der test ist ein sehr einfacher test, weil er nur die rückgabe des services für einen request testet
       er testet also ob ein service request correct gerouted wird
    */
    $this->assertRouteController(
      $this->rq(array('entities','tag','1')),
      'Psc\Test\Controllers\TagController',
      'getEntity',
      array(1, null)
    );
  }

  public function testControllerRoute_getEntityWithSubresourceForm() {
    $this->assertRouteController(
      $this->rq(array('entities','tag','1','form')),
      'Psc\Test\Controllers\TagController',
      'getEntity',
      array('1', 'form', null)
    );
  }

  public function testControllerRoute_getEntitiesSearch() {
    $this->assertRouteController(
      $this->rq(array('entities','tags','search')),
      'Psc\Test\Controllers\TagController',
      'getEntities',
      array(null, 'search')
    );
  }

  public function testControllerRoute_getEntitiesGrid() {
    $this->assertRouteController(
      $this->rq(array('entities','tags','grid')),
      'Psc\Test\Controllers\TagController',
      'getEntities',
      array(null, 'grid')
    );
  }

  public function testControllerRoute_saveEntity() { 
    $this->assertRouteController(
      $this->rq(array('entities','tag','1'), 'PUT'),
      'Psc\Test\Controllers\TagController',
      'saveEntity',
      array('1', new \stdClass)
    );
  }
  
  public function testControllerRoute_saveEntityAsRevision() {
    $this->assertRouteController(
      $this->rq(array('entities','tag','1'), 'PUT')
        ->setMeta('revision','preview-1172'),
      'Psc\Test\Controllers\TagController',
      'saveEntityAsRevision',
      array('1', 'preview-1172', new \stdClass)
    );
  }

  public function testControllerRoute_patchEntity() { 
    $this->assertRouteController(
      $this->rq(array('entities', 'tag', '1'), 'PATCH', array('label'=>'changed label')),
      'Psc\Test\Controllers\TagController',
      'patchEntity',
      array('1', (object) array('label'=>'changed label'))
    );
  }

  public function testControllerRoute_inserEntityAkaPostEntity() {
    $this->assertRouteController(
      $this->rq(array('entities','tags'), 'POST')->setBody(array('some'=>'data')),
      'Psc\Test\Controllers\TagController',
      'insertEntity',
      array(array('some'=>'data'))
    );
  }
  
  public function testControllerRoute_inserEntityRevision() {
    $this->assertRouteController(
      $this->rq(array('entities','tags'), 'POST')
        ->setBody(array('some'=>'data'))
        ->setMeta('revision','preview-1170'),
      'Psc\Test\Controllers\TagController',
      'insertEntityRevision',
      array('preview-1170', array('some'=>'data'))
    );
  }

  public function testControllerRoute_deleteEntity() {
    $this->assertRouteController(
      $this->rq(array('entities','tag','7'), 'DELETE'),
      'Psc\Test\Controllers\TagController',
      'deleteEntity',
      array('7')
    );
  }


  public function testControllerRoute_NavigationNodeEntitySaving() {
    $this->assertRouteController(
      $this->rq(array('entities','navigation-node','default'), 'PUT')
        ->setBody($body = array((object) array('id'=>1,'title'=>'root'))),
      'Psc\Test\Controllers\NavigationNodeController',
      'saveEntity',
      array('default', (object) $body)
    );
  }

  public function testControllerRoute_NavigationNodeEntityGetting() {
    $this->assertRouteController(
      $this->rq(array('entities','navigation-node','default', 'form'), 'GET'),
      'Psc\Test\Controllers\NavigationNodeController',
      'getEntity',
      array('default', 'form', NULL)
    );
  }
  
  
  public function testInitRequestMatcher() {
    $r = $this->svc->initRequestMatcher($this->rq(array('entities','person','1')));
    $this->assertEquals(array('person','1'), $r->getLeftParts());

    $r = $this->svc->initRequestMatcher($this->rq(array('EntitiEs','person','1')));
    $this->assertEquals(array('person','1'), $r->getLeftParts());
    
    $svc = $this->svc;
    $request = $this->rq(array('entity','person','1'));
    $this->assertRoutingException(function () use ($svc, $request) {
      $svc->initRequestMatcher($request);
    });
  }
}
?>