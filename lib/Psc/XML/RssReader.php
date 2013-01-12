<?php

namespace Psc\XML;

use \Psc\Code\Code,
    \SimpleXMLElement,
    \Psc\XML\Loader
;

class RssReader extends \Psc\Object {
  
  /**
   * @var Psc\URL\Request
   */
  protected $request;
  
  /**
   * @var Psc\XML\Loader
   */
  protected $xmlLoader;
  
  /**
   * @var SimpleXMLElement
   */
  protected $xml;
  
  protected $type = 'rss';
  
  /**
   * @var array RssItems[]
   */
  protected $items;
  
  
  protected $init = FALSE;
  
  protected $refresh = TRUE;
  
  /**
   * @var Cache
   */
  protected $cache;
  
  public function __construct($request = NULL, \Psc\XML\Loader $xmlLoader = NULL) {
    $this->items = array();
    
    /* Request */
    if ($request instanceof \Psc\URL\Request) {
      $this->request = $request;
    } elseif (is_string($request)) {
      $this->request = new \Psc\URL\Request($request);
    } else {
      throw new \InvalidArgumentException('Type: '.Code::varInfo($request).' als Constructor-Parameter nicht bekannt');
    }
    
    /* Loader */
    $this->xmlLoader = $xmlLoader ?: new \Psc\XML\Loader();
  }
  
  public function init() {
    if (!$this->init) {
      
      $this->xml = $this->xmlLoader->process($this->getURLContents());
      
      if ($this->xml === FALSE) {
        throw new Exception('XML konnte nicht geparsed werden. Fehlerhafter Content in URL. '.$this->url.' '.mb_substr($content,0,200));
      }
    }
  }
  
  public function read() {
    $this->init();
    
    if ($this->type == 'atom') {
      foreach ($this->xml->entry as $xmlEntry) {
        $item = new RssItem($xmlEntry, $this);
        
        $this->items[] = $item;
      }
      
    } else {
    
      foreach ($this->xml->channel->item as $xmlItem) {
        $item = new RssItem($xmlItem, $this);
      
        $this->items[] = $item;
      }
    }
  }
  
  /**
   *
   */
  protected function getURLContents() {
    
    if (isset($this->cache) && !$this->refresh) {
      $contents = $this->cache->load('URLContents',$loaded);
      
      if ($loaded) {
        return $contents;
      }
    }
    
    $contents = $this->request->init()->process();
    if ($contents === FALSE) {
      throw new Exception('URL konnte nicht gelesen werden: '.Code::varInfo($this->request->getURL()));
    }
      
    if (isset($this->cache)) $this->cache->store('URLContents',$contents);
    
    return $contents;
  }

  /**
   * @param SimpleXMLElement $xml
   * @chainable
   */
  public function setXML(SimpleXMLElement $xml) {
    $this->xml = $xml;
    return $this;
  }

  /**
   * @return SimpleXMLElement
   */
  public function getXML() {
    return $this->xml;
  }

  /**
   * @param string $url
   * @chainable
   */
  public function setURL($url) {
    $this->url = $url;
    return $this;
  }

  /**
   * @return string
   */
  public function getURL() {
    return $this->url;
  }

  
}