<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\AssociationsLister
 */
class AssociationsListerTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $lister;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\CMS\AssociationsLister';
    parent::setUp();
    
    $this->lister = new AssociationsLister($this->getDoctrinePackage());
    
    $this->fentity1 = new \Psc\Doctrine\TestEntities\Tag('eins');
    $this->fentity2 = new \Psc\Doctrine\TestEntities\Tag('zwei');
    $this->fentity3 = new \Psc\Doctrine\TestEntities\Tag('drei');
    $this->entity = new \Psc\Doctrine\TestEntities\Article('Der Tag-Artikel','hat content');
    $this->entity
      ->addTag($this->fentity1)
      ->addTag($this->fentity2)
      ->addTag($this->fentity3)
    ;
    
    
    $this->lister->setEntity($this->entity);
  }
  
  public function testSimpleAssociationCreation() {
    $assoc = $this->lister
      ->listAssociation('tags', "Wird unter den Tags:\n%list% einsortiert.")
        ->withLabel('label')
        ->joinedWith(",\n")
    ;
    
    $this->assertInstanceOf('Psc\CMS\AssociationList', $assoc);
    $this->assertEquals(
      "Wird unter den Tags:\n".
      "eins,\n".
      "zwei,\n".
      "drei einsortiert.",
      
      $this->lister->getHTMLForList($assoc)
    );
  }

  public function testButtonAssociationListCreation() {
    $assoc = $this->lister
      ->listAssociation('tags', "Wird unter den Tags:<br />%list% einsortiert.")
        ->withButton()
        ->joinedWith(",<br />")
    ;
    
    $this->assertInstanceOf('Psc\CMS\AssociationList', $assoc);
    $this->html = $this->lister->getHTMLForList($assoc);
    
    $this->test->css('button')->count(3);
    $this->assertContains('Wird unter den Tags', $this->html);
    $this->assertContains('einsortiert.', $this->html);
  }

  //public function testSimpleAssociationCreationSprintfJoin() {
  //  $assoc = $this->lister
  //    ->listAssocation('tags', "Wird unter den Tags:\n%list% einsortiert.")
  //      ->withLabel('label')
  //      ->joinedWith("'%s',\n")
  //  ;
  //  
  //  $this->assertInstanceOf('Psc\CMS\AssociationList', $assoc);
  //  $this->assertEquals(
  //    "Wird unter den Tags:\n".
  //    "'eins',\n".
  //    "'zwei',\n".
  //    "'drei',\n, einsortiert."
  //  );
  //}
}
?>