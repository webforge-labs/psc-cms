<?php

namespace Psc\Net\HTTP;

use \Psc\Net\HTTP\Request;

/**
 * @group net-service
 * @group class:Psc\Net\HTTP\Request
 *
 * braucht eine .htaccess Rule:
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond  %{REQUEST_URI}    ^/(request-acceptance)
 * RewriteRule . /request.helper.php [L]
 *
 * braucht auch die PHP Datei: request.helper.php (logisch gell)
 *
 * Alle Test-URLs müssen mit request-acceptance anfangen
 * dies ist ein lustiger Loop Test:
 * request.helper.php nimmt den Request und gibt den serialisiert zurück
 * wir unserialisieren ihn und können dann kram accepten.
 *
 * das ist der einzig vernünfte Weg Apache + htaccess + PHP Krams in einem Rutsch zu testen
 */
class RequestAcceptanceTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $someBody;
  
  public function setUp() {
    $this->someBody = array(
      'format'=>'xlsx',
      'soundExport'=>true,
      'label'=>array(
        'autoComplete'=>'aclabel with spaces',
        'remove'=>'remove it',
      )
    );
    
    if (\Psc\PSC::isTravis()) {
      $this->markTestSkipped('Kein Webserver bei Travis');
    }

    parent::setUp();
  }
  
  
  public function testPreconditionRequestCanBeSerializedBidirectional() {
    $request = Request::create('GET', '/request-acceptance/nice/api/url/', array('some'=>'header'));
    $unserialized = unserialize(serialize($request));
    
    $this->assertInstanceof('Psc\Net\HTTP\Request', $unserialized);
    $this->assertEquals($request, $unserialized);
  }
  
  
  public function testGetRequestWithoutQuery() {
    $request = $this->dispatch( // der request wird inferred und zu uns zurückgelooped
      'GET',
      '/request-acceptance/nice/api/url/',
      NULL
    );
    
    $this->assertEquals(Request::GET, $request->getMethod());
    $this->assertEquals(Request::GET, $request->getHeader()->getType());
    $this->assertEquals('request-acceptance/nice/api/url', $request->getResource());
    $this->assertEquals(array(), $request->getQuery());
  }
  
  public function testRequestHeadersGetParseInCorrectCase() {
    $request = $this->dispatch('GET', '/request-acceptance/url', NULL, array('X-Psc-Test-Header1'=>'true'
                                                                             ));
    
    $this->assertEquals('true', $request->getHeaderField('X-Psc-Test-Header1'));
    //$this->assertEquals(array('multi','value'), $request->getHeaderField('X-Psc-Test-Header2'));
    // das da kann ich nicht mit CURL! (warum net)
  }
  
  public function testGetRequestWithQuery() {
    $expectedQuery = array(
      'format'=>'xlsx',
      'soundExport'=>true,
      'label'=>array(
        'autoComplete'=>'aclabel with spaces',
        'remove'=>'remove it',
      )
    );
    
    $request = $this->dispatch(
      'GET',
      '/request-acceptance/shorter/url',
      $expectedQuery // nur damit ich mich nicht bei den & und = usw vertippe ;)
    );
    
    $this->assertEquals(Request::GET, $request->getMethod());
    $this->assertEquals($expectedQuery, $request->getQuery());
  }
  
  public function testPostWithOverridenMethodToPutNativeWayAndBodyIsParsedInPut() {
    $request = $this->dispatch('PUT', '/request-acceptance/a/put/request',
      $body = array('field'=>'put has to be url form encoded'),
      array('X-HTTP-METHOD-OVERRIDE'=>'PUT') // das kann symfony
    );
    
    $this->assertEquals(Request::PUT, $request->getMethod());
    $this->assertEquals((object) $body, $request->getBody());
  }

  public function testPostWithOverridenMethodToPATCHNativeWayAndBodyIsParsedIn() {
    $request = $this->dispatch('PATCH', '/request-acceptance/a/patch/request',
      $body = array('field'=>'patch value for field'),
      array('X-HTTP-METHOD-OVERRIDE'=>'PATCH') // das kann symfony
    );
    
    $this->assertEquals(Request::PATCH, $request->getMethod());
    $this->assertEquals((object) $body, $request->getBody());
  }
  
  public function testRequestBodyCanBeJSON() {
    $request = $this->dispatch('PUT', '/request-acceptance/a/json/put/request',
                               json_encode($this->someBody),
                               array('Content-Type'=>'application/json', // das ist der "senden" Content-Type
                                     'X-HTTP-METHOD-OVERRIDE'=>'PUT'
                               )
                              );
    $this->assertEquals(Request::PUT, $request->getMethod());
    $this->assertEquals(json_decode(json_encode($this->someBody)), // etwas kompizierter, weil json ja aus assoc arrays objects macht
                        json_decode($request->getBody()));
  }

  /**
   * @group body
   */
  public function testRequestBodyWillBeConvertedToArrayWhenXWWWFormUrlEncoded() {
    $request = $this->dispatch('POST', '/request-acceptance/a/normal/POST/request',
                               $this->someBody,
                               array('Content-Type'=>'application/x-www-form-urlencoded; charset=UTF-8', // das ist der "senden" Content-Type                                     
                               )
                              );
    
    $this->assertEquals($this->someBody, (array) $request->getBody());
  }
  
  /**
   * @group language
   */
  public function testPreferredLanguagesAreParsed() {
    $request = $this->dispatch('GET', '/request-acceptance/a/normal/request',
                               NULL,
                               array('Accept-Language'=>'de-de,en-us;q=0.7,en;q=0.3',
                               )
                              );
    
    $this->assertEquals(array('de_DE','en_US','en'), (array) $request->getPreferredLanguages());
    
  }

  /**
   * @group referer
   */
  public function testRefererIsParsed() {
    $request = $this->dispatch('GET', '/request-acceptance/a/normal/request',
                               NULL,
                               array('Referer'=>'http://somelocation.com',
                               )
                              );
    
    $this->assertEquals('http://somelocation.com', $request->getReferer());
  }

  /**
   * @group multi-part
   */
  public function testRequestBodyWillBeConvertedToArrayWhenMultiPartFormData() {
    $request = $this->dispatch('POST', '/request-acceptance/fileupload/POST/request',
                               $this->createMultiPartBody(),
                               array('Content-Type'=>'multipart/form-data; boundary=---------------------------41184676334'
                                     // das ist der "senden" Content-Type                                     
                               )
                              );
    $this->assertEquals(array('apostfield'=>'a value'), (array) $request->getBody());
    $this->assertArrayHasKey('excelFile', $files = $request->getFiles());
    $this->assertInstanceOf('Psc\System\UploadedFile', $f = $files['excelFile']);
    $this->assertEquals('small.excel.xlsx', $f->getOriginalName());
    
    // das geht leider nicht, weil der acceptance test ja in einem anderen thread läuft und deshalb die temporary datei gelöscht wird
    //$this->assertEquals($this->getFile('small.excel.xlsx')->getContents(), $files['excelFile']->getContents());
  }
  
  /**
   * @group parts
   */
  public function testgetPartsWithModRewrite() {
    $request = $this->dispatch('GET', '/request-acceptance/js/cms/tests/Psc.AjaxHandler');
    $this->assertEquals(array('request-acceptance','js','cms','tests','Psc.AjaxHandler'), $request->getParts());
    
    $request = $this->dispatch('GET', '/request-acceptance/');
    $this->assertEquals(array('request-acceptance'), $request->getParts());
  }
  
  /**
   * @group parts
   */
  public function testGetPartsWithWhitespaceAreNotURLEncoded() {
    $request = $this->dispatch('GET', '/request-acceptance/99488409004994/Some%20File%20With%20Whitespace.pdf');
    $this->assertEquals(array('request-acceptance','99488409004994','Some File With Whitespace.pdf'), $request->getParts());
  }

  protected function dispatch($method, $relativeUrl, $body = NULL, Array $headers = array()) {
    $this->tester = $this->test->acceptance(NULL);
    
    $dispatcher = $this->tester->dispatcher($method, $relativeUrl, 'text/plain');

    if (isset($body)) {
      $dispatcher->setRequestData($body);
    }

    $dispatcher->setHeaderFields($headers);
    
    $response = $this->tester->result($dispatcher, 'response', 200);
    
    $this->assertNotEmpty($serializedRequest = $response->getRaw(), 'responseRaw ist leer');
    $this->assertInstanceOf('Psc\Net\HTTP\Request', $request = unserialize($serializedRequest));
    
    return $request;
  }

  protected function onNotSuccessfulTest(\Exception $e) {
    try {
      parent::onNotSuccessfulTest($e);
    } catch (\Exception $e) {
      if (isset($this->tester)) {
        print '------------ Acceptance (Fail) ------------'."\n";
        print "\n";
        print $this->tester->getLog();
        print '------------ /Acceptance ------------'."\n";
      }
    }
    
    throw $e;
  }
  
  protected function createMultiPartBody() {
    return
      '-----------------------------41184676334'."\n".
      'Content-Disposition: form-data; name="apostfield"'."\n".
      "\n".
      'a value'."\n".
      '-----------------------------41184676334'."\n".
      'Content-Disposition: form-data; name="excelFile"; filename="small.excel.xlsx'."\n".
      'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'."\n".
      "\n".
      $this->getFile('small.excel.xlsx')->getContents()."\n".
      '-----------------------------41184676334--'."\n"
    ;
  }
}
?>