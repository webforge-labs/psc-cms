<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Code\Generate\GClass;

class EntityServiceTest extends \Psc\Code\Test\ServiceBase {
  
  protected $svc;
  protected $doctrine;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\EntityService';
    parent::setUp();
    
    $this->project = clone \Psc\PSC::getProject();
    $this->doctrine = $this->project->getModule('Doctrine');
    
    $this->svc = $this->getMock($this->chainClass, array('getControllersNamespace'), array($this->doctrine, 'entities'));
    $this->svc->expects($this->any())->method('getControllersNamespace')
              ->will($this->returnValue('Psc\Test\Controllers'));

   $this->doctrine->registerEntityClassesMetadataDriver();
   $this->doctrine->getEntityClassesMetadataDriver()->addClass('Psc\Doctrine\TestEntities\Tag');

  }
  
  public function testRoute_toController() {
    /* der test ist käse, weil hier irgendwie alles weggemockt wird, was geht
       die Funktionalität des Services wird hier ganz schön intern geprüft, aber es geht nicht anders, wenn
       man nicht die Entities oder den Controller bereitstellt
    */
    $this->assertRouteController(
      $this->rq(array('entities','tag','1')),
      'Psc\Test\Controllers\TagController',
      'getEntity',
      array(1)
    );

    $this->assertRouteController(
      $this->rq(array('entities','tag','1','form')),
      'Psc\Test\Controllers\TagController',
      'getEntity',
      array(1, 'form')
    );
  }
  
  /**
   * @dataProvider provideBadRequestType
   */
  public function testRoute_toController_badType($type) {
    $svc = $this->svc;
    $request = $this->rq(array('entities','tag',1), $type);
    
    $this->assertRoutingException(function () use ($svc, $request) {
      $svc->routeController($request);
    });
  }
  
  public static function provideBadRequestType() {
    return array(
      array(Service::PUT),
      array(Service::DELETE),
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

namespace Psc\Test\Controllers;

class TagController extends \Psc\CMS\Controller\AbstractEntityController {
  
  public function getEntityName() {
    return 'Psc\Doctrine\TestEntities\Tag';
  }
  
}
?>