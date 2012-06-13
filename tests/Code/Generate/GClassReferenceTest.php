<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\GClassReference;

/**
 * @group generate
 * @group class:Psc\Code\Generate\GClassReference
 */
class GClassReferenceTest extends \Psc\Code\Test\Base {

  public function testConstruct() {
    $this->assertInstanceof('Psc\Code\Generate\GClass', new GClassReference('Psc\DataInput'));
  }
}
?>