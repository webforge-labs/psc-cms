<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Code\Generate\GClass;

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
    
    $this->svc = $this->getMock($this->chainClass, array('getControllersNamespace'), array($this->dc, $this->project, 'entities'));
    $this->svc->expects($this->any())->method('getControllersNamespace')
              ->will($this->returnValue('Psc\Test\Controllers'));
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

  public function testControllerRoute_inserEntityAkaPostEntity() {
    $this->assertRouteController(
      $this->rq(array('entities','tags'), 'POST')->setBody(array('some'=>'data')),
      'Psc\Test\Controllers\TagController',
      'insertEntity',
      array(array('some'=>'data'))
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
  
  // den test gibt es erstmal nicht mehr, weil wir jetzt auch delete können
  ///**
  // * @dataProvider provideBadRequestType
  // */
  //public function testRoute_toController_badType($type) {
  //  $svc = $this->svc;
  //  $request = $this->rq(array('entities','tag',1), $type);
  //  
  //  $this->assertRoutingException(function () use ($svc, $request) {
  //    $svc->routeController($request);
  //  });
  //}
  //
  //public static function provideBadRequestType() {
  //  return array(
  //    array('head'),
  //  );
  //}
  
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

namespace Psc\Test\Controllers;

class TagController extends \Psc\CMS\Controller\AbstractEntityController {
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Tag';
  }
  
}
?>