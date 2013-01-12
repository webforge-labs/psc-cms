<?php

namespace Psc\XML;

use \SimpleXMLElement,
    \Psc\DateTime\DateTime,
    \Psc\Code\Code
;

/**
 *
das XML ist: 

<item>
 <title><![CDATA[Frankfurter Hotel Radisson Blu ist verkauft]]></title>
 <link>http://www.immobilien-zeitung.de/1000004466/frankfurter-hotel-radisson-blu-ist-verkauft</link>
 <description><![CDATA[Das Frankfurter Vier-Sterne-Hotel Radisson Blu hat für über 100 Mio. Euro den Eigentümer gewechselt. Verkäufer ist die dänische ...]]></description>
 <pubDate>Wed, 13 Jul 2011 07:10:05 +0200</pubDate>

 </item>
 */
class RssItem extends \Psc\Object {
  
  protected $link;
  protected $title;
  protected $description;
  protected $pubDate;
  
  
  protected $type;
  protected $rssReader;
  
  /**
   * @var SimpleXMLElement
   */
  protected $xml;
  
  /**
   * @param SimpleXMLElement $xml die Node des <item> Elements aus aus dem RSS-Feed
   */
  public function __construct(SimpleXMLElement $xml, RssReader $rssReader) {
    $this->xml = $xml;
    $this->rssReader = $rssReader;
    $this->type = $rssReader->getType();
    
    $this->init();
  }
  
  protected function init() {
    
    if ($this->type == 'atom') {
      $this->title = (string) $this->xml->title;
      $this->pubDate = DateTime::parse(
        DateTime::ATOM,
        (string) $this->xml->published
      );
      $this->link = (string) $this->xml->link['href'];
      
    } else {
      $this->title = (string) $this->xml->title;
      $this->link = (string) $this->xml->link;
      $this->description = (string) $this->xml->description;
    
      $this->pubDate = DateTime::parse(
      //Wed, 13 Jul 2011 07:10:05 +0200
      //'dayname"," dd M YY HH":"II":"SS tzcorrection',
      // how geil is das denn bitte:
      DateTime::RSS,
      (string) $this->xml->pubDate
      );
    }
  }
}