<?php

namespace Psc\CMS\Controller;

require_once __DIR__.DIRECTORY_SEPARATOR.'ControllerBaseTest.php';

/**
 * @group class:Psc\CMS\Controller\AbstractEntityController
 */
abstract class AbstractEntityControllerBaseTest extends ControllerBaseTest {
  
  protected $tags;
  protected $articles, $articleMeta;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\AbstractEntityController';
    $this->entityFQN = 'Psc\Doctrine\TestEntities\Article';
    parent::setUp();
    
    $this->article = new \Psc\Doctrine\TestEntities\Article('Lorem Ipsum:', 'Lorem Ipsum Dolor sit amet... <more>');
    $this->assertInstanceOf('Psc\Doctrine\Entity', $this->article);
    $this->article->setId(7);
    
    $this->articleMeta = $this->getEntityMeta($this->entityFQN);
    $this->articles = $this->loadTestEntities('articles');
  }  
}
