<?php

namespace Psc\tests\Mail;

use \Psc\Mail\Helper as email;

class HelperTest extends \PHPUnit_Framework_TestCase {
  
  
  public function testSmokes() {
    email::loadModule('swift');
    
    $html = '<div class="content"><h1>Mail aus dem Test</h2>Toll, ne?</div>';
    
    $msg = email::send(
      email::htmlMessage('Test Mail aus dem Test', $html, 'null@ps-webforge.com')
    );
  }
}
?>