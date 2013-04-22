<?php

namespace Psc\CMS;

use Psc\Entities\ContentStream\TeaserHeadlineImageTextLink;

class CommonProjectCompilerTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\CommonProjectCompiler';
    parent::setUp();

    $this->initInstancer();

    $this->converter = $this->getContainer()->getContentStreamConverter();
    $this->specification = \Psc\System\Console\CompileTestEntitiesCommand::getTeaserSpecification();
  }


  public function testCSWidgetCompiling() {
    // siehe compiler in test for call for the spec

    $teaser = $this->createTeaser();

    $this->assertSame($this->image, $teaser->getImage());
    $this->assertSame($this->headline, $teaser->getHeadline());
    $this->assertSame($this->link, $teaser->getLink());
    $this->assertSame($this->text, $teaser->getText());

    $this->assertEquals('TemplateWidget', $teaser->getType());

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

  public function testSerializing_hasTheTemplateWidgetAsTypeNotTheConcreteTypeOfClass() {
    $teaser = $this->createTeaser();
    $serialized = $teaser->serialize($context = NULL, function() {});

    $this->assertEquals('TemplateWidget', $serialized->type);
  }

  public function testSerializing_setstheSpecificationAsAParam() {
    $teaser = $this->createTeaser();
    $serialized = $this->converter->serializeEntry($teaser);

    $this->assertEquals($this->specification, $serialized->specification);
  }

  public function testSerializing_allSubItemsAreSerializedRecursively() {
    $teaser = $this->createTeaser();
    $serialized = $this->converter->serializeEntry($teaser);

    $this->assertEquals(
      $this->converter->serializeEntry($this->image),
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
    $serialized = $this->converter->serializeEntry($this->createTeaser());

    $teaser = $this->converter->unserializeEntry($serialized);

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
