<?php

namespace Psc\CMS\Ajax;

use \Psc\CMS\Ajax\ImageResponse;

/**
 * @group class:Psc\CMS\Ajax\ImageResponse
 */
class ImageResponseTest extends \Psc\Code\Test\Base {
  
  public function testConstruct() {
    new ImageResponse('rawpngstring');
  }
}

?>