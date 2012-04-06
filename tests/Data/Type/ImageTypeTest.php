<?php

namespace Psc\Data\Type;

use Psc\Data\Type\ImageType;

class ImageTypeTest extends \Psc\Code\Test\Base {

  public function testConstruct() {
    $imageType = new ImageType();
    $this->assertEquals('Image',$imageType->getName());
  }
}
?>