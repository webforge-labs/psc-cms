<?php

namespace Psc\Entities\ContentStream;

class ContentStreamTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Entities\\ContentStream\\ContentStream';
    parent::setUp();

    $this->cs = ContentStream::create('de');
  }

  public function testCSImplementsTPLContentStream() {
    $this->assertInstanceOf('Psc\TPL\ContentStream\ContentStream', $this->cs);
  }

  public function testCreateSetsTypeAsWell() {
    $cs = ContentStream::create('de', 'sidebar-content');

    $this->assertEquals('de', $cs->getLocale());
    $this->assertEquals('sidebar-content', $cs->getType());
    $this->assertEquals('default', $cs->getRevision());
    $this->assertEquals(NULL, $cs->getSlug());
  }

  public function testCreateSetsPageContentAsDefault() {
    $cs = ContentStream::create('de');

    $this->assertEquals('de', $cs->getLocale());
    $this->assertEquals('page-content', $cs->getType());
    $this->assertEquals(ContentStream::PAGE_CONTENT, $cs->getType());
  }
}
?>