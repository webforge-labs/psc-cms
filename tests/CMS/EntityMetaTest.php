<?php

namespace Psc\CMS;

class EntityMetaTest extends \Psc\Code\Test\Base {
  
  protected $entityMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityMeta';
    parent::setUp();
    $this->classMetadata = $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array('getIdentifierFieldNames'), array('Psc\Doctrine\TestEntitites\Article'));
    $this->classMetadata->expects($this->any())->method('getIdentifierFieldNames')->will($this->returnValue(array('id')));
    $this->entityMeta = new EntityMeta('Psc\Doctrine\TestEntities\Article', $this->classMetadata, 'Artikel');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$gClass = $this->entityMeta->getGClass());
    
    $this->assertEquals(\Psc\Doctrine\TestEntities\Article::getSetMeta(),$this->entityMeta->getSetMeta());
    
    $this->assertEquals('article',$this->entityMeta->getEntityName());
    $this->assertEquals('Artikel',$this->entityMeta->getLabel(\Psc\CMS\TabsContentItem2::LABEL_DEFAULT));
    
    // defaults
    $this->assertEquals(300, $this->entityMeta->getAutoCompleteDelay());
    $this->assertEquals(2, $this->entityMeta->getAutoCompleteMinLength());
  }
  
  public function testEntityMetaProperty() {
    $property = $this->entityMeta->getPropertyMeta('content');
    
    $this->assertEquals('content',$property->getName());
    $this->assertEquals('MarkupText',$property->getType()->getName());
    $this->assertEquals('Content',$property->getLabel()); // solange der labeler das nicht anders errät
  }
  
  public function testRequestMetas() {
    $this->assertEquals('GET /entities/article/17/form',
                        $this->entityMeta->getFormRequestMeta()->getMethod().' '.
                        $this->entityMeta->getFormRequestMeta()->getUrl(17));
    
    $this->assertEquals('POST /entities/articles/',
                        $this->entityMeta->getNewRequestMeta()->getMethod().' '.
                        $this->entityMeta->getNewRequestMeta()->getUrl());

    $this->assertEquals('GET /entities/articles/form',
                        $this->entityMeta->getNewFormRequestMeta()->getMethod().' '.
                        $this->entityMeta->getNewFormRequestMeta()->getUrl());

    $this->assertEquals('DELETE /entities/article/17',
                        $this->entityMeta->getDeleteRequestMeta()->getMethod().' '.
                        $this->entityMeta->getDeleteRequestMeta()->getUrl(17));

    $this->assertEquals('PUT /entities/article/17',
                        $this->entityMeta->getSaveRequestMeta()->getMethod().' '.
                        $this->entityMeta->getSaveRequestMeta()->getUrl(17));

    $this->assertEquals('GET /entities/articles/?tag=wahlen&sort=desc',
                        $this->entityMeta->getSearchRequestMeta()->getMethod().' '.
                        $this->entityMeta->getSearchRequestMeta()->getUrl(array('tag'=>'wahlen','sort'=>'desc')));

    $this->assertEquals('GET /entities/articles/?term=gir',
                        $this->entityMeta->getAutoCompleteRequestMeta()->getMethod().' '.
                        $this->entityMeta->getAutoCompleteRequestMeta()->getUrl(array('term'=>'gir'))
                       );
  }
}
?>