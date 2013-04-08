<?php

namespace Psc\CMS\Controller;

use Psc\Entities\ContentStream\ContentStream;

class ContentStreamControllerTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Controller\\ContentStreamController';
    parent::setUp();

    $this->controller = $this->getContainer()->getControllerFactory()->getController('ContentStream');

    $this->cs1 = ContentStream::create('de', 'page-content', 'default', 'de-default');
    $this->cs2 = ContentStream::create('en', 'page-content', 'default', 'en-default');

    $this->initInstancer();
  }

  public function testCorrectInstance() {
    $this->assertChainable($this->controller);
  }
}
