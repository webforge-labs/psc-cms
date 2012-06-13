<?php

namespace Psc\URL;

use Psc\Data\ObjectCache;
use \Psc\URL\RequestBundle;
use Psc\System\File;
use Psc\Data\PHPFileCache;

/**
 * @group class:Psc\URL\RequestBundle
 */
class RequestBundleTest extends \Psc\Code\Test\Base {
  
  public function testCaching() {
    $cachedRequest = new CachedRequest('http://www.google.de');
    $cachedRequest->setCachedResponse(new Response('ich bin gecached http://www.google.de', new HTTP\Header()));
    
    $oc = new ObjectCache(function ($request) {
      return array($request->getURL());
    }, 200000);
    $oc->store(array('http://www.google.de'), $cachedRequest);
    $this->assertTrue($oc->hit(array('http://www.google.de')));
    
    $bundle = new RequestBundle(0, $oc);
    
    $googleReq = $bundle->createRequest('http://www.google.de');
    $this->assertInstanceOf('Psc\URL\CachedRequest',$googleReq);
    $this->assertEquals($cachedRequest,$googleReq);
    $this->assertEquals('ich bin gecached http://www.google.de',$googleReq->init()->process());
    $this->assertEquals('ich bin gecached http://www.google.de',$googleReq->getResponse()->getRaw());
  }
  
  public function testPHPFileCaching() {
    $this->markTestSkipped('Feature noch nicht implementiert');
    /* Schön wäre hier, das RequestBundle mit einem Cache zu füttern, der direkt
      Objekte abspeichern kann. Quasi der PermanentObjectCache
      
      dieser Cache müsste dann können, dass man die Objekte serialisiert oder einfach selbst erstellt (so mit setState und so)
      
      NOCH schöner wäre hier
      eine zweiten PermanentCache zu übergeben und dann ein PermanentCachedRequestBundle zu haben
      welches einfach nur url + content + headers in dem permanent cache speichert und dann selbst die objekte
      instanziiert
    */

    /* FileCache erstellen und laden */
    $file = $this->newFile('storage.phpfile.php');
    $file->delete();
    $cache = new PHPFileCache($file);
    
    /* CachedRequest im Cache abspeichern (unter der URL) */
    $cachedRequest = new CachedRequest('http://www.google.de');
    $cachedRequest->setCachedResponse(new Response('ich bin gecached http://www.google.de', new HTTP\Header()));
    $cache->store(array('http://www.google.de'), $cachedRequest);
    $this->assertTrue($cache->hit(array('http://www.google.de')));
    
    $bundle = new RequestBundle(0, $cache);
    
    $googleReq = $bundle->createRequest('http://www.google.de');
    $this->assertInstanceOf('Psc\URL\CachedRequest',$googleReq);
    $this->assertEquals($cachedRequest,$googleReq);
    $this->assertEquals('ich bin gecached http://www.google.de',$googleReq->init()->process());
    $this->assertEquals('ich bin gecached http://www.google.de',$googleReq->getResponse()->getRaw());
    
  }
}

?>