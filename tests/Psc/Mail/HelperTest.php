<?php

namespace Psc\Mail;

use Psc\Mail\Helper as email;
use Psc\PSC;

/**
 * @group class:Psc\tests\Mail\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase {
  
  public function testLoadsModuleWithoutError() {
    email::loadModule('swift');
  }
  
  public function testSmokes() {
    if (PSC::isTravis()) {
      $html = '<div class="content"><h1>Mail aus dem Test</h2>Toll, ne?</div>';

      $msg = email::send(
        email::htmlMessage('Test Mail aus dem Test', $html, 'null@ps-webforge.com')
      );

    } else {
      $this->markTestSkipped('no mailer is configured');
    }
  }
}
