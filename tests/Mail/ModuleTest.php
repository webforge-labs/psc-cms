<?php

namespace Psc\Mail;

use Psc\Mail\Module;

class ModuleTest extends \Psc\Code\Test\Base {

  public function testLoad() {
    $this->assertInstanceOf('Psc\Mail\Module',$module = \Psc\PSC::getProject()->getModule('Swift'));
    
    $this->assertInstanceOf('Psc\Mail\Module',$module->bootstrap());
    
    $this->assertInstanceof('Swift_MailTransport',\Swift_MailTransport::newInstance());
  }
}
?>