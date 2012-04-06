<?php

namespace Psc\XML;

use \Psc\XML\RssReader,
    \Psc\XML\RssItem
;

class RssReaderTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\XML\RssReader';
  
  protected $rss;
  
  public function testConstruct() {
    //$this->rss = new $this->c(); // deprecated NULL Nicht mehr erlaubt
  }
  
  public function testRead() {
    $file = $this->getTestDirectory()->getFile('sjepisodes.xml');
    
    $this->rss = new RssReader(new \Psc\URL\Request('http://www.ps-webforge.com/testfeed.xml'));
    $this->rss->read();
    
    $dates = array_fill(0,100,'Wed, 13 Jul 2011 07:10:05 +0200');
    $dates[2] = 'Fri, 15 Jul 2011 13:30:05 +0200';
    $dates[3] = 'Thu, 02 Jun 2011 02:10:05 +0200';
    
    /** alle dates vergleichen */
    foreach ($this->rss->getItems() as $key=>$item ) {
      $this->assertEquals($dates[$key], $item->getPubDate()->format('D, d M Y H:i:s O'));
      
      $this->assertNotEmpty($item->getDescription());
      $this->assertNotEmpty($item->getTitle());
      $this->assertNotEmpty($item->getLink());
    }
  }
}

?>