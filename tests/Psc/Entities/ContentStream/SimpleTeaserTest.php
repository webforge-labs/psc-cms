<?php

namespace Psc\Entities\ContentStream;

class SimpleTeaserTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Entities\\ContentStream\\SimpleTeaser';
    parent::setUp();

    $this->teaser = new SimpleTeaser('Have you noticed that?', 'This is the **markup text**');
  }

  public function testSerialization() {
    $serialized = $this->teaser->serialize($context = NULL);
    foreach (array('text','headline','link') as $prop) {
      $this->assertObjectHasAttribute($prop, $serialized);
    }
  }
}
