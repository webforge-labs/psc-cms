<?php

namespace Psc\URL\HTTP;

use \Psc\URL\HTTP\Header;

/**
 * @group class:Psc\URL\HTTP\Header
 */
class HeaderTest extends \Psc\Code\Test\Base {
  
  public function testParsing() {
    $headerRaw = array(
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
    $string = implode("\r\n",$headerRaw); // das ist nur damit wir hier kein line-ending verhunzen

    $header = new Header();
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
      'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
      'Pragma' => 'no-cache',
      'Content-Length' => '3481',
      'Content-Type' => 'text/html; charset=utf-8',
    );
    
    $this->assertEquals($expected,$header->getValues());
  }
}

?>