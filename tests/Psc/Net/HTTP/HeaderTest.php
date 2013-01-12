<?php

namespace Psc\Net\HTTP;

use \Psc\Net\HTTP\Header;

/**
 * @group class:Psc\Net\HTTP\Header
 */
class HeaderTest extends \Psc\Code\Test\Base {
  
  protected $headerRaw;
  
  public function setUp() {
    $this->headerRaw = array(
      'HTTP/1.1 200 OK',
      'Date: Wed, 09 Nov 2011 12:38:11 GMT',
      'Server: Apache/2.2.17 (Win32) PHP/5.3.8',
      'X-Powered-By: PHP/5.3.8',
      'Set-Cookie: SID=cre3iuuc0p0s9o8bfnh5lg3fq4; path=/',
      'Expires: Thu, 19 Nov 1981 08:52:00 GMT',
      'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
      'Pragma: no-cache',
      'Set-Cookie: login=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; path=/; domain=serien-loader.philipp.zpintern',
      'Content-Length: 3481',
      'Content-Type: text/html; charset=utf-8'
    );
    parent::setUp();
  }
  
  public function testParsing() {
    $headerRaw = $this->headerRaw;
    $string = implode("\r\n",$headerRaw); // das ist nur damit wir hier kein line-ending verhunzen

    $header = new ResponseHeader();
    $header->parseFrom($string);
    
    $expected = array (
      'Date' => 'Wed, 09 Nov 2011 12:38:11 GMT',
      'Server' => 'Apache/2.2.17 (Win32) PHP/5.3.8',
      'X-Powered-By' => 'PHP/5.3.8',
      'Set-Cookie' =>
        array (
        0 => 'SID=cre3iuuc0p0s9o8bfnh5lg3fq4; path=/',
        1 => 'login=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; path=/; domain=serien-loader.philipp.zpintern',
      ),
      'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
      'Cache-Control' => array('must-revalidate','no-cache','no-store','post-check=0','pre-check=0','private'),
      'Pragma' => array('no-cache'),
      'Content-Length' => '3481',
      'Content-Type' => 'text/html; charset=utf-8',
      'Vary' => 'Accept'
    );
    
    $this->assertEquals($expected,$header->getValues());
    
    $header = ResponseHeader::parse($string);
    $this->assertEquals($expected,$header->getValues());
    
    $testHeader = $this->getMock('Psc\Net\HTTP\ResponseHeader', array('sendPHPHeader'));
    $testHeader->parseFrom($string);
    
    return $testHeader;
  }
  
  /**
   * @depends testParsing
   */
  public function testSending($testHeader) {
    $list = array();
    $testHeader->expects($this->any())
               ->method('sendPHPHeader')
               ->will($this->returnCallback(function ($key, $value, $replace) use (&$list) {
                 if ($replace)
                   $list[$key] = $value;
                 else
                   $list[$key][] = $value;
                 
                 return NULL;
               }));
    $testHeader->send();

    $raw = array(''=>'HTTP/1.1 200 OK',
                 'Pragma'=>array('0'=>'no-cache'),
                 'Cache-Control'=>array('must-revalidate','no-cache','no-store','post-check=0','pre-check=0','private'),
                 'Vary'=>'Accept',
                 'Date'=>'Wed, 09 Nov 2011 12:38:11 GMT',
                 'Server'=>'Apache/2.2.17 (Win32) PHP/5.3.8',
                 'X-Powered-By'=>'PHP/5.3.8',
                 'Set-Cookie'=>array('SID=cre3iuuc0p0s9o8bfnh5lg3fq4; path=/',
                                     'login=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; path=/; domain=serien-loader.philipp.zpintern'),
                 'Expires'=>'Thu, 19 Nov 1981 08:52:00 GMT',
                 'Content-Length'=>'3481',
                 'Content-Type'=>'text/html; charset=utf-8'
                );
    $this->assertEquals($raw, $list);
  }
  
  /**
   * @depends testParsing
   */
  public function testGetDate($header) {
    $this->assertEquals('09.11.2011 13:38:11',$header->getDate()->format('d.m.Y H:i:s')); // wir sind ja gmt+1
  }
  
  public function testGetEmptyDate() {
    $header = new Header();
    $this->assertNull($header->getDate());
  }
  
  public function testFieldParsing() {
    $header = new Header();
    $header->setField('Content-Disposition', 'inline; filename="mydownload.tar.gz"');
    $this->assertEquals('inline; filename="mydownload.tar.gz"',$header->getField('Content-Disposition'));
    $this->assertEquals((object) array('type'=>'inline','filename'=>'mydownload.tar.gz'),
                        $header->getField('Content-Disposition', Header::PARSE));
  }
  
  public function testEmptyFieldGetting_doesNotThrowException() {
    $header = new Header();
    $header->getField('Content-Disposition');
  }

  /**
   * @expectedException Psc\Net\HTTP\HeaderFieldNotDefinedException
   */
  public function testEmptyFieldParsing_doesThrowException() {
    $header = new Header();
    $header->getField('Content-Disposition', Header::PARSE);
  }
}

?>