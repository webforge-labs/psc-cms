<?php

namespace Psc\Net\HTTP;

use Psc\Net\ServiceResponse;
use Psc\Net\Service;
use Psc\Data\ArrayCollection;

class ResponseConverterTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\ResponseConverter';
    parent::setUp();
    $this->converter = new ResponseConverter();
  }
  
  /**
   * @dataProvider provideNormalConversions
   */
  public function testOKConversion($expectedBody, $serviceResponseBody, $format, Request $request = NULL) {
    $request = $request ?: $this->doublesManager->createHTTPRequest('GET', '/entities/person/2/form');
    
    $httpResponse = $this->converter->fromService(new ServiceResponse(Service::OK, $serviceResponseBody, $format), $request);
    $this->assertInstanceof('Psc\Net\HTTP\Response', $httpResponse);
    
    if ($format === ServiceResponse::HTML) {
      $this->assertXmlStringEqualsXmlString($expectedBody, $httpResponse->getBody());
    } elseif ($format === ServiceResponse::JSON) {
      // das erlaubt uns vll mal pretty print für json
      $this->assertEquals(json_decode($expectedBody), json_decode($httpResponse->getBody()));
    } else {
      $this->assertEquals($expectedBody, $httpResponse->getBody());
    }
  }
  
  public static function provideNormalConversions() {
    $tests = array();
    
    $testHTML = function ($expectedHTML, $body, Request $request = NULL) use (&$tests) {
      $tests[] = array($expectedHTML, $body, ServiceResponse::HTML, $request);
    };
    $testJSON = function ($expectedJSON, $body, Request $request = NULL) use (&$tests) {
      $tests[] = array($expectedJSON, $body, ServiceResponse::JSON, $request);
    };
    
    $testHTML('<html><body><form></form></body></html>', '<html><body><form></form></body></html>');
    $testHTML('<input type="hidden" name="nix" />', \Psc\HTML\HTML::Tag('input', NULL, array('type'=>'hidden','name'=>'nix')));
    
    // php array
    $testJSON(json_encode(array('eins','zwei')), array('eins','zwei'));

    // php object
    $o = new \stdClass; $o->eins = 'v1'; $o->zwei = 'v2';
    $testJSON(json_encode((object) array('eins'=>'v1','zwei'=>'v2')), $o);
    
    // interface JSON
    $testJSON(json_encode(array('eins'=>TRUE,'zwei'=>'f')),
              new ArrayCollection(array('eins'=>TRUE,'zwei'=>'f'))
              );
    
    return $tests;
  }
}
?>