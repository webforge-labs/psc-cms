<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\EntityFactory
 */
class EntityFactoryTest extends \Psc\Code\Test\Base {
  
  protected $entityFactory;
  
  protected $entityClass;
  protected $entityMeta;
  
  protected $tags;
  protected $content;
  protected $title;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\EntityFactory';
    parent::setUp();
    
    $this->entityClass = 'Psc\Doctrine\TestEntities\Article';
    $this->entityMeta = $this->getEntityMeta($this->entityClass);
    $this->entityFactory = new EntityFactory($this->entityMeta);
    $this->tags = new \Psc\Data\ArrayCollection($this->loadTestEntities('tags'));
  }
  
  public function testAcceptance() {
    $this->entityFactory->set('tags', $this->tags);
    $this->entityFactory->set('content', $this->content = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam');
    $this->entityFactory->set('title', $this->title = 'Ein Article erstellt durch eine EntityFactory');
    
    $article = $this->entityFactory->getEntity();
    
    $this->assertInstanceOf($this->entityClass, $article);
    $this->assertEntityCollectionSame($article->getTags(), $this->tags,'tags');
    $this->assertEquals($this->content, $article->getContent());
    $this->assertEquals($this->title, $article->getTitle());
  }
}
?>