<?php

namespace Psc\URL;

/**
 * @group class:Psc\URL\Helper
 */
class HelperTest extends \Psc\Code\Test\Base {
  
  public function testIsHTTPS() {
    
    $this->assertTrue(Helper::isHTTPS('https://www.thomas-daily.de/rss/de/MorningNews/?c=5300'));
    $this->assertFalse(Helper::isHTTPS('http://www.thomas-daily.de/rss/de/MorningNews/?c=5300'));
    $this->assertFalse(Helper::isHTTPS('ftp://user@green-group.de'));
    $this->assertFalse(Helper::isHTTPS('ftps://user@green-group.de'));
  }
  
}

?>