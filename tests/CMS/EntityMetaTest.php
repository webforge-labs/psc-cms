<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\EntityMeta
 */
class EntityMetaTest extends \Psc\Code\Test\Base {
  
  protected $entityMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityMeta';
    parent::setUp();
    
    // hier nativ erzeugen, da sonst entityMetas im Doctrine Module gecached werden und die Seiteneffekt schwierig zu sehen sind
    $this->entityMeta = $this->createEntityMeta('Article', 'Artikel');
    $this->entity = current($this->loadTestEntities('articles'));
    $this->entity->setId(17);
  }
  
  protected function createEntityMeta($name, $label) {
    $classMetadata = \Psc\PSC::getProject()->getModule('Doctrine')->getEntityManager('tests')->getClassMetadata('Psc\Doctrine\TestEntities\\'.$name);
    
    $entityMeta = new EntityMeta('Psc\Doctrine\TestEntities\\'.$name, $classMetadata, $label);
    $entityMeta->setLabel($label);
    
    return $entityMeta;
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$gClass = $this->entityMeta->getGClass());
    
    $this->assertEquals(\Psc\Doctrine\TestEntities\Article::getSetMeta(),$this->entityMeta->getSetMeta());
    
    $this->assertEquals('article',$this->entityMeta->getEntityName());
    $this->assertEquals('Artikel',$this->entityMeta->getLabel(\Psc\CMS\TabsContentItem2::LABEL_DEFAULT));
    $this->assertEquals('Psc\Doctrine\TestEntities\Article', $this->entityMeta->getClass()); // das benutzen
    $this->assertEquals('Psc\Doctrine\TestEntities\Article', $this->entityMeta->getGClass()->getFQN()); // das nicht
    
    // defaults
    $this->assertEquals(300, $this->entityMeta->getAutoCompleteDelay());
    $this->assertEquals(2, $this->entityMeta->getAutoCompleteMinLength());
  }
  
  // @deprecated
  public function testTCIInstances() {
    //$this->assertInstanceOf('Psc\CMS\TabsContentItem2', $this->entityMeta->getGridTCI());
    //$this->assertInstanceOf('Psc\CMS\TabsContentItem2', $this->entityMeta->getNewFormTCI());
    //$this->assertInstanceOf('Psc\CMS\TabsContentItem2', $this->entityMeta->getFormTCI($this->entity));
  }
  
  public function testRequestMetas() {
    $this->assertEquals('GET /entities/article/17/form',
                        $this->entityMeta->getFormRequestMeta()->getMethod().' '.
                        $this->entityMeta->getFormRequestMeta()->getUrl(17));

    $this->assertEquals('GET /entities/articles/grid',
                        $this->entityMeta->getGridRequestMeta()->getMethod().' '.
                        $this->entityMeta->getGridRequestMeta()->getUrl(17));

    $this->assertEquals('GET /entities/article/17/generic',
                        $this->entityMeta->getActionRequestMeta('generic')->getMethod().' '.
                        $this->entityMeta->getActionRequestMeta('generic')->getUrl(17));
    
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

    $this->assertEquals('GET /entities/articles/search',
                        $this->entityMeta->getSearchPanelRequestMeta()->getMethod().' '.
                        $this->entityMeta->getSearchPanelRequestMeta()->getUrl(array('tag'=>'wahlen','sort'=>'desc')));

    $this->assertEquals('GET /entities/articles/?term=gir',
                        $this->entityMeta->getAutoCompleteRequestMeta()->getMethod().' '.
                        $this->entityMeta->getAutoCompleteRequestMeta()->getUrl(array('term'=>'gir'))
                       );
    $this->assertInstanceOf('Psc\CMS\AutoCompleteRequestMeta',$this->entityMeta->getAutoCompleteRequestMeta());
  }
  
  public function testSetUrlPrefixPartsChangesUrl() {
    $this->entityMeta->setUrlPrefixParts(array('api','some','entities'));
    $this->assertEquals('/api/some/entities/articles/form', $this->entityMeta->getNewFormRequestMeta()->getUrl());
  }
  
  public function testGetAdapterReturnsMetaAdapterWithRightContextOnSingleArgument() {
    $this->assertInstanceOf('Psc\CMS\Item\MetaAdapter',$metaAdapter = $this->entityMeta->getAdapter('grid'));
    $this->assertEquals('grid', $metaAdapter->getContext());
  }

  public function testGetAdapterReturnsAdapterWithRightContextOnTwoArguments() {
    $this->assertInstanceOf('Psc\CMS\Item\Adapter',$adapter = $this->entityMeta->getAdapter($this->entity, 'grid'));
    $this->assertEquals('grid', $adapter->getContext());
  }
  
  public function testGetActionRequestMetaOverAdapter() {
    $action = $this->entityMeta->getAdapter($this->entity, EntityMeta::CONTEXT_ACTION, 'custom-action');
    
    $action->getTabRequestMeta()
      ->addInputMeta(\Psc\CMS\RequestMeta::QUERY)->addInput(array('par1'=>'value1','par2'=>'value2'));
    
    $this->assertEquals('/entities/article/17/custom-action?par1=value1&par2=value2',
                        $action->getTabRequestMeta()->getUrl()
                       );
  }
  
  public function testGetDefaultRequestMeta_respectsDefaultRequestableEntity() {
    $entityMeta = $this->createEntityMeta('Category','Kategorie');
    $category = new \Psc\Doctrine\TestEntities\Category('some Cat');
    $category->setId(2);
    
    $this->assertEquals('GET /entities/category/2/articles', 
                        $entityMeta->getDefaultRequestMeta($category)->getMethod().' '.
                        $entityMeta->getDefaultRequestMeta($category)->getUrl()
                       );
  }
}
?>