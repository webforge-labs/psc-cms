<?php

namespace Psc\Test\Controllers;

use Psc\Entities\ContentStream\ContentStream;

class NavigationNodeControllerTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Test\\Controllers\\NavigationNodeController';
    parent::setUp();

    $this->controller = $this->getContainer()->getControllerFactory()->getController('NavigationNode');

    $this->cs1 = ContentStream::create('de', 'page-content', 'default', 'de-default');
    $this->cs2 = ContentStream::create('en', 'page-content', 'default', 'en-default');

    $this->initInstancer();
  }

  public function testCorrectInstance() {
    $this->assertChainable($this->controller);
  }

  public function testGetEntityCanReturnContentStreamFormulars() {
    $this->resetDatabaseOnNextTest();
    $node = $this->instancer->getNavigationNode(1);
    $node->addContentStream($this->cs1)->addContentStream($this->cs2);
    $this->em->persist($this->cs1);
    $this->em->persist($this->cs2);

    $this->em->flush();

    $cs1Form = $this->controller->getEntity($node->getId(), array('contentstream', 'de'));
    $cs2Form = $this->controller->getEntity($node->getId(), array('contentstream', 'en'));

    $this->assertInstanceOf('Psc\CMS\EntityFormPanel', $cs1Form);
    $this->assertInstanceOf('Psc\CMS\EntityFormPanel', $cs2Form);
    $this->assertEquals($this->cs1->getIdentifier(), $cs1Form->getEntity()->getIdentifier());
    $this->assertEquals($this->cs2->getIdentifier(), $cs2Form->getEntity()->getIdentifier());
  }

  public function testGetEntityReturnsContentStreamFormularEvenIfItsNotExisting() {
    $this->resetDatabaseOnNextTest();
    $node = $this->instancer->getNavigationNode(1);

    $this->assertCount(0 , $node->getContentStream()->collection());

    $csForm = $this->controller->getEntity($node->getId(), array('contentstream', 'de'));

    $this->assertInstanceOf('Psc\CMS\EntityFormPanel', $csForm);
    $this->assertInstanceOf('Psc\Entities\ContentStream\ContentStream', $cs = $csForm->getEntity());

    $this->assertFalse($cs->isNew(), 'entity should be persisted and not new anymore');
    $this->assertEquals('de', $cs->getLocale());
    $this->assertEquals('page-content', $cs->getType());

    $this->em->refresh($node);
    
    $this->assertSame(
      $cs, 
      $node->getContentStream()->locale('de')->type('page-content')->one()
    );
  }
}
