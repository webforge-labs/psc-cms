<?php

namespace Psc\JS;

use Psc\JS\ProxyManager;

/**
 * @group class:Psc\JS\ProxyManager
 */
class ProxyManagerTest extends \Psc\Code\Test\HTMLTestCase {

  public function testAcceptance() {
    $configuration = new \Psc\CMS\Configuration(array('js'=>array('url'=>'/js/cms/')));
    $proxyManager = new ProxyManager('proxy',$configuration);
    $this->assertInstanceOf('Psc\HTML\HTMLInterface',$proxyManager);
    
    $proxyManager->register('jquery-1.7.1.min.js','jquery');
    $proxyManager->register('jquery.json-2.2.min.js','jquery-json',array('jquery')); // das ist abhängig von jquery, d.h. wenn wir das laden wir jquery mitgeladen
    
    $proxyManager->enqueue('jquery-json');
    
    $this->html = $proxyManager->html();
    
    // reihenfolge wichtig! erst jquery (denn json braucht das)
    $this->test->css('script:eq(0)')
      ->count(1)
      ->hasAttribute('type',"text/javascript")
      ->hasAttribute('src','/js/cms/jquery-1.7.1.min.js')
    ;
      
    $this->test->css('script:eq(1)')
      ->count(1)
      ->hasAttribute('type',"text/javascript")
      ->hasAttribute('src', "/js/cms/jquery.json-2.2.min.js")
    ;
  }
}
?>