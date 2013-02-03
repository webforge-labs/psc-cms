<?php

namespace Psc\Net\HTTP;

use Psc\Net\ServiceResponse;
use Psc\Net\Service;
use Psc\Data\ArrayCollection;
use Psc\CMS\RequestMeta;
use Psc\CMS\Service\MetadataGenerator;
use Psc\Entities\File AS UplFile;

/**
 * @group class:Psc\Net\HTTP\ResponseConverter
 */
class ResponseConverterTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\ResponseConverter';
    parent::setUp();
    $this->converter = new ResponseConverter();
    
    $this->fileWithWhitespace = $this->getCommonFile('whitespace file.txt');
  }
  
  /**
   * @dataProvider provideNormalConversions
   */
  public function testOKConversion($expectedBody, $serviceResponseBody, $format, Request $request = NULL) {
    return $this->assertOKConversion($expectedBody, $serviceResponseBody, $format, $request);
  }
  
  protected function assertOKConversion($expectedBody, $serviceResponseBody, $format, Request $request = NULL) {
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
    
    return $httpResponse;
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
    
  public function testFileWithWhitespaceIsNotURLEncoded() {
    $uplFile = new UplFile($this->fileWithWhitespace);
    $uplFile->setOriginalName($this->fileWithWhitespace->getName());
    
    $response = $this->assertOKConversion(
      NULL,
      $uplFile,
      ServiceResponse::SOME_FILE
    );
    
    $contentDisposition = $response->getHeaderField('Content-Disposition');
    
    $this->assertRegExp(
      '/filename=("|\')?whitespace file.txt("|\')?/',
      $contentDisposition
    );
  }
  
  public function testMetadataConversion() {
    $tabOpenable = $this->doublesManager->createTabOpenable('/url/des/tabs', 'label des Tabs');
    
    $request = $this->doublesManager->createHTTPRequest('POST', '/entities/persons');
    $response = new ServiceResponse(Service::OK, (object) array('ok'=>'true'), 'json');
    $response->setMetadata(
      MetadataGenerator::create()
        ->openTab($tabOpenable)
    );
    
    $httpResponse = $this->converter->fromService($response, $request);
    $this->assertInstanceof('Psc\Net\HTTP\Response', $httpResponse);
    
    $this->assertTrue($httpResponse->hasHeaderField('X-Psc-Cms-Meta'));
  }
}
?>