<?php

namespace Psc\CMS;

use Psc\Doctrine\TestEntities\Tag;

class ActionRouterTest extends \Psc\Code\Test\Base {
  
  protected $router;
  protected $entity, $entityMeta;
  protected $specificAction, $generalAction;
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\ActionRouter';
    parent::setUp();

    $this->entity = new Tag('nice-label');
    $this->entity->setId(7);
    $this->entityMeta = $this->getMockBuilder('Psc\CMS\EntityMeta')->disableOriginalConstructor()->getMock();
    $this->entityMeta->expects($this->any())->method('getClass')->will($this->returnValue('Psc\Doctrine\TestEntities\Tag'));
    $this->entityMeta->expects($this->any())->method('getEntityName')->will($this->returnValue('tag'));
    $this->entityMeta->expects($this->any())->method('getEntityNamePlural')->will($this->returnValue('tags'));
    
    $this->specificAction = new Action($this->entity, ActionMeta::PUT, 'relevance');
    $this->generalAction = new Action($this->entityMeta, ActionMeta::GET, 'infos');
    
    $this->router = new ActionRouter(
      $this->doublesManager->createEntityMetaProvider(array($this->entityMeta))
    );
  }
  
  public function testMapsConcretePutToSingleEntityNameUrlWithSubresource() {
    $this->assertRequestMetaMapping(
      $this->specificAction,
      'PUT',
      '/entities/tag/7/relevance'
    );
  }

  public function testMapsGeneralGetToPluralEntityNameUrlWithSubresource() {
    $this->assertRequestMetaMapping(
      $this->generalAction,
      'GET',
      '/entities/tags/infos'
    );
  }

  protected function assertRequestMetaMapping(Action $action, $method, $url) {
    $requestMeta = $this->router->route($action);
    $this->assertInstanceOf('Psc\CMS\RequestMetaInterface', $requestMeta);
    
    $this->assertEquals(
      $method.' '.$url,
      $requestMeta->getMethod().' '.$requestMeta->getUrl()
    );
    
    return $requestMeta;
  }
}
?>