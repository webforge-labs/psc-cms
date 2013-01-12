<?php

namespace Psc\UI;

use Psc\UI\Link;

/**
 * @group class:Psc\UI\Link
 */
class LinkTest extends \Psc\Code\Test\Base {

  public function testAPI() {
    $link = new Link('http://www.ps-webforge.com','Meine Webseite');
    $this->assertInstanceof('Psc\Data\Type\Interfaces\Link',$link);
    $this->assertEquals('http://www.ps-webforge.com', $link->getURI());
    $this->assertEquals('Meine Webseite', $link->getLabel());

    $this->test->css('a',$link->html())
      ->count(1)
      ->hasAttribute('href','http://www.ps-webforge.com')
      ->hasText('Meine Webseite');
  }
}
?>