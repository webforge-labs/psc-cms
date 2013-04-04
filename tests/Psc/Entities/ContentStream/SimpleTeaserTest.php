<?php

namespace Psc\Entities\ContentStream;

class SimpleTeaserTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Entities\\ContentStream\\SimpleTeaser';
    parent::setUp();
    $this->initInstancer();

    $this->converter = $this->getContainer()->getContentStreamConverter();

    $this->teaser = new SimpleTeaser('Have you noticed that?', 'This is the **markup text**');
    $this->teaser->setImage($this->image = $this->instancer->getCSImage(1));
  }

  public function testSerialization() {
    $serialized = $this->teaser->serialize($context = NULL);
    $this->assertEquals('SimpleTeaser', $serialized->type);

    $debug = print_r($serialized, true);

    foreach (array('text','headline','link','image') as $prop) {
      $this->assertObjectHasAttribute($prop, $serialized, $debug);
    }

    $image = $this->teaser->getImage();
    $this->assertEquals($serialized->image, $image->serialize($context), $debug);
  }

  public function testImageUnserialization() {
    $serialized = $this->teaser->serialize($context = NULL);
    $this->assertEquals('SimpleTeaser', $serialized->type, 'this will fail with cannot instantiate abstract class');

    $teaser = $this->converter->unserializeEntry($serialized);
  }

/*
  public function testLinkUnserialization() {
    $serialized = $this->teaser->serialize($context = NULL);
    $this->assertEquals('SimpleTeaser', $serialized->type, 'this will fail with cannot instantiate abstract class');

    $teaser = $this->converter->unserializeEntry($serialized);
  }
*/  
}
