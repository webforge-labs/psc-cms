<?php

namespace Psc\Hitch;

use \Psc\Hitch\Module;

/**
 * @group Hitch
 */
class ModuleTest extends \Psc\Code\Test\Base {

  public function testLoad() {
    $this->assertInstanceOf('Psc\Hitch\Module',$module = \Psc\PSC::getProject()->getModule('Hitch'));
    
    $this->assertEquals('Psc',$module->getObjectsNamespace());
    
    $module->setObjectsNamespace('\Psc\Hitch\TestObjects');
    
    $this->assertEquals('\Psc\Hitch\TestObjects',$module->getObjectsNamespace());
    $immoClass = $module->expandClassName('Immo');
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$immoClass);
    $this->assertEquals('\Psc\Hitch\TestObjects\Immo',$immoClass->getName());

    $otherClass = $module->expandClassName('Other\Immo');
    $this->assertInstanceOf('Psc\Code\Generate\GClass',$otherClass);
    $this->assertEquals('\Psc\Hitch\TestObjects\Other\Immo',$otherClass->getName());
  }
}
?>