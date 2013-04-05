<?php

namespace Psc\CMS;

use Psc\Entities\ContentStream\TeaserHeadlineImageTextLink;

class CommonProjectCompilerTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\CommonProjectCompiler';
    parent::setUp();

    $this->initInstancer();
  }

  public function testCSWidgetCompiling() {
    // siehe compiler in test for call for the spec

    $teaser = $this->createTeaser();

    $this->assertSame($this->image, $teaser->getImage());
    $this->assertSame($this->headline, $teaser->getHeadline());
    $this->assertSame($this->link, $teaser->getLink());
    $this->assertSame($this->text, $teaser->getText());

    return $teaser;
  }

  protected function createTeaser() {
    $teaser = new TeaserHeadlineImageTextLink(
      $this->headline = 'h1 of teaser', 
      $this->image = $this->instancer->getCSImage(1), 
      $this->text = 'the content of the text', 
      $this->link = $this->instancer->getNavigationNode(1)
    );
    return $teaser;
  }

  public function testSerializing() {
    $teaser = $this->createTeaser();
    $serialized = $teaser->serialize($context = NULL);

    $this->assertEquals(
      $this->image->serialize($context),
      $serialized->image
    );

    $this->assertEquals(
      $this->link->getIdentifier(),
      $serialized->link
    );

    $this->assertEquals(
      $this->headline,
      $serialized->headline
    );

    $this->assertEquals(
      $this->text,
      $serialized->text
    );
  }

  public function testUnserializing() {
    $serialized = $this->createTeaser()->serialize($context = NULL);

    $teaser = $this->getContainer()->getContentStreamConverter()->unserializeEntry($serialized);

    $this->assertEquals(
      $this->image->getIdentifier(),
      $teaser->getImage()->getIdentifier()
    );

    $this->assertEquals(
      $this->link->getIdentifier(),
      $teaser->getLink()->getIdentifier()
    );

    $this->assertEquals(
      $this->headline,
      $teaser->getHeadline()
    );

    $this->assertEquals(
      $this->text,
      $teaser->getText()
    );
  }
}
