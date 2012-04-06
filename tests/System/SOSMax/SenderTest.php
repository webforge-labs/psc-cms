<?php

namespace Psc\System\SOSMax;

class SenderTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\System\SOSMax\Sender';
    parent::setUp();
  }

  public function testMessagin() {
    return;
    $this->sender = new Sender();  
    $this->sender->fold('textsdlfjsldfj
                        sdlfjsd
                        fjsd
                        fjsdf', 'FoldLabel', 'error');
  }
}
?>