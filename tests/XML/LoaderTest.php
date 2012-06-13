<?php

namespace Psc\XML;

use \Psc\Code\Test\Base,
    \Psc\XML\Loader;

/**
 * @group class:Psc\XML\Loader
 */
class LoaderTest extends \Psc\Code\Test\Base {

  public function testApaLoad() {
    $xmlFile = $this->getTestDirectory()->getFile('apa.main.xml');
    $raw = $xmlFile->getContents();
    
    $this->assertNotEmpty($raw);
    
    $loader = new Loader();
    try {
      $root = $loader->process($raw);
      
    } catch (\Psc\Exception $e) {
      throw $e;
    }
    
  }
  
  public function testErroneousLoad() {
    $xmlFile = $this->getTestDirectory()->getFile('value.main.xml');
    $raw = $xmlFile->getContents();
    
    $this->assertNotEmpty($raw);
    
    $loader = new Loader();
    try {
      $root = $loader->process($raw);
      
    } catch (\Psc\XML\LoadingException $e) {
      $errors = $e->libxmlErrors;
      $this->assertGreaterThan(18, count($errors));
      return; 
    }
    
    $this->fail('Test hat keine Exception gefangen, obwohl er sollte');
  }
  
  public function testRSSLoad() {
    $loader = new Loader();
    $root = $loader->process($this->getFile('episodes.xml')->getContents());
    
    $this->assertInstanceOf('SimpleXMLElement',$root);
    $this->assertEquals('rss',$root->getName());
  }
}

?>