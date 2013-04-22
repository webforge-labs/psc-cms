<?php

namespace Psc\TPL\ContentStream;

use Psc\TPL\ContentStream\ContentStream as ContentStreamInterface;
use Psc\Entities\ContentStream\ContentStream;
use Psc\Entities\ContentStream\Headline;
use Psc\Entities\ContentStream\Paragraph;
use Psc\Entities\ContentStream\ContentStreamWrapper;
use Psc\Entities\ContentStream\Image;
use Psc\Entities\ContentStream\Li;

class ConverterTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Entities\\ContentStream\\ContentStream';
    parent::setUp();
    $this->initInstancer();

    $this->cs = ContentStream::create('de');

    $this->headline1 = new Headline('Some Headline (H1)');
    $this->headline2 = new Headline('Some Other Headline (H1)');
    $this->paragraph = new Paragraph('the text of the paragraph can be spanned'."\n".'along lines');

    $this->cs1 = ContentStream::create('de');
    $this->headline1->setContentStream($this->cs1);
    $this->headline2->setContentStream($this->cs1);
    $this->paragraph->setContentStream($this->cs1);

    $this->converter = $this->getContainer()->getContentStreamConverter();

    $this->imageEntity = $this->instancer->getImage(1);
  }

  protected function setUpDatabase() {
  }

  public function testSerializeInSubMode() {
    $wrapper = new ContentStreamWrapper();
    $wrapper->setWrapped($this->cs1);

    $this->cs->addEntry($wrapper);

    $serialized = $this->converter->convertSerialized($this->cs);

    $this->assertCount(1, $serialized);
    list ($swrapper) = $serialized;

    $this->assertAttributeEquals('ContentStreamWrapper', 'type', $swrapper);

    $serializedContentStream = (object) array(
      'type'=>'page-content',
      'locale'=>'de',
      'revision'=>'default',
      'entries'=>array(
        $this->converter->serializeEntry($this->headline1),
        $this->converter->serializeEntry($this->headline2),
        $this->converter->serializeEntry($this->paragraph)
      )
    );

    $this->assertAttributeEquals($serializedContentStream, 'wrapped', $swrapper);
  }

  public function testSerializingHeadline() {
    $this->assertSerialized(array(
        'type'=>'Headline',
        'level'=>2,
        'label'=>'Zwischenüberschrift',
        'content'=>'mycontent',
      ), new Headline('mycontent', 2)
    );
  }

  public function testSerializingImages() {
    $img = new Image('/path/to/image.jpg', 'a nice image', 'right');
    $img->setImageEntity($this->imageEntity);

    $this->assertSerialized(array(
        'type'=>'Image',
        'caption'=>'a nice image',
        'label'=>'Bild',
        'imageEntity'=>$this->imageEntity->getId(),
        'url'=>'/path/to/image.jpg',
        'align'=>'right'
      ), 
      $img
    );
  }

  public function testSerializingParagraph() {
    $this->assertSerialized(array(
        'type'=>'Paragraph',
        'label'=>'Absatz',
        'content'=>'mycontent'
      ), new Paragraph('mycontent')
    );
  }

  public function testSerializingLis() {
    $this->markTestIncomplete('copy li: make commonProjectCompilable');
    $this->assertSerialized(array(
        'type'=>'Li',
        'label'=>'Aufzählung',
        'content'=>array('list1','list2'),
      ), new Li(array('list1','list2'))
    );
  }

  public function testSerializingDownloadsAndDownload() {
    $this->markTestIncomplete('TODO');
  }

  protected function assertSerialized(Array $expectedSerializedEntry, Entry $entry, $withBack = TRUE) {
    $entry->setContentStream($this->cs);
    
    $this->assertEquals(
      Array((object) $expectedSerializedEntry),
      $serializedEntries = $this->converter->convertSerialized($this->cs)
    );
    
    // back
    if ($withBack) {
      $newContentStream = ContentStream::create('de'); // same as in setup
      $this->converter->convertUnserialized($serializedEntries, $newContentStream);
      $this->assertEquals($this->cs, $newContentStream, 'bidirectional (un)serialization has failed');
    }
  }
}
