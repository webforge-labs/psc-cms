<?php

namespace Psc\Mail;

use Psc\Mail\Helper as email;
use Psc\PSC;

/**
 * @group class:Psc\tests\Mail\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase {
  
  
  public function testSmokes() {
    if (PSC::isTravis()) {
      email::loadModule('swift');
    
      $html = '<div class="content"><h1>Mail aus dem Test</h2>Toll, ne?</div>';

      $msg = email::send(
        email::htmlMessage('Test Mail aus dem Test', $html, 'null@ps-webforge.com')
      );
    } else {
      $this->markTestSkipped('kein Sendmail für travis und ich möchte nicht smtp daten ins public verzeichnis packen');
    }
  }
}