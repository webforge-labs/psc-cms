<?php

namespace Psc\CMS\Navigation;

/**
 * @group class:Psc\CMS\Navigation\NestedSetConverter
 */
class NestedSetConverterTest extends \Psc\Code\Test\Base {
  
  protected $nestedSetConverter;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Navigation\NestedSetConverter';
    parent::setUp();
    $this->nestedSetConverter = new NestedSetConverter();
    $this->food = new \Webforge\TestData\NestedSet\FoodCategories();
  }
  
  public function testAndCreateTheArrayStructureSnippet() {
    $this->assertXmlStringEqualsXmlString(
      $c1 = $this->food->toHTMLList(),
      $c2 = $this->nestedSetConverter->toHTMLList($this->food->toArray())
    );
  }
}
?>