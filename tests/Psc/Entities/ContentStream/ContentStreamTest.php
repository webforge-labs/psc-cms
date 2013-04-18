<?php

namespace Psc\Entities\ContentStream;

class ContentStreamTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Entities\\ContentStream\\ContentStream';
    parent::setUp();

    $this->cs = ContentStream::create('de');

    $this->headline1 = new Headline('Some Headline (H1)');
    $this->headline2 = new Headline('Some Other Headline (H1)');
    $this->lastcs1 = $this->paragraph = new Paragraph('the text of the paragraph can be spanned'."\n".'along lines');

    $this->cs1 = ContentStream::create('de');
    $this->headline1->setContentStream($this->cs1);
    $this->headline2->setContentStream($this->cs1);
    $this->paragraph->setContentStream($this->cs1);
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

  public function testGetsNextEntryAsEntryObjectInStreamWhenExisting() {
    $this->assertSame(
      $this->headline2,
      $this->cs1->findNextEntry($this->headline1)
    );
  }

  public function testGetsNextEntryAsNULLWhenNoNextElementIsExisting() {
    $this->assertNull(
      $this->cs1->findNextEntry($this->lastcs1)
    );
  }

  public function testGetsNextEntryAsEntryObjectInStreamWhenExisting_AfterRemovingSomethingBefore() {
    $this->assertSame(
      $this->paragraph,
      $this->cs1->findNextEntry($this->headline2)
    );

    $this->cs1->removeEntry($this->headline1);

    $this->assertSame(
      $this->paragraph,
      $this->cs1->findNextEntry($this->headline2)
    );
  }
}
