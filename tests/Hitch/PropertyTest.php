<?php

namespace Psc\Hitch;

use Psc\Hitch\Property;
use Psc\Code\Generate\GClass;

/**
 * @group Hitch
 */
class PropertyTest extends \Psc\Code\Test\Base {

  public function testDocBlock() {
    
    $property = new Property('categories',Property::TYPE_LIST);
    $property->setObjectType(new GClass('ePaper42\Category'))
      ->setXmlName('category')
      ->setWrapper('categoryList')
    ;
    
    $docb  = "/**\n";
    $docb .= ' * @Hitch\XmlList(name="category", type="ePaper42\Category", wrapper="categoryList")'."\n";
    $docb .= " */\n";
    
    $this->assertEquals($docb, $property->createDocBlock()->toString());
    
  }
}
?>