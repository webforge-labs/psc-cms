<?php

namespace Psc\Code\Build;

use Psc\PSC as nativePSC;

class CoreBuilderTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Build\CoreBuilder';
    parent::setUp();
    $this->out = $this->newFile('core.phar.gz');
    $this->out->delete();
  }
  
  public function testBuilderCreatesPhar() {
    return;
    $coreBuilder = new CoreBuilder(nativePSC::getProject()->getClassPath()->up());
    $coreBuilder->compile($this->out);
    $this->out->exists();
  }
  
  public function testCoreExposesPSC() {
    return;
    /*
     * Unser Haupt-Problem ist hier, dass wir nicht den gesamten Core in native Code schreiben wollen
     * wir wollen aber auch nicht sehr viele Klassen aus dem Psc-Module benutzen, weil wir sonst core.phar.gz sehr oft
     * updaten müssen (gerade beim Entwickeln soll dies nicht der Fall sein müssen).
     *
     * Es soll schon möglich sein, dass Core Klassen benutzt, die jetzt im Psc-Module anders Funktionieren (man denke an Dir oder File, oder HTTP Curl etc)
     * D. h. entweder müssen wir hier den Code nach Core duplizieren (beim Phar-Builden kopieren und "manuell" den Namespace ändern)
     *
     *
     * Alternative:
     * 1. was brauchen wir um das Psc-Modul zu laden?
     * 2. wir dürfen das Psc-Modul nicht laden, um das Psc-Modul zu compilen.
     *
     *
     * => Compiling / Building muss in Core sein!
     * => Configuration muss in Core sein
     * => Dir, File, Logger muss in Core sein
     * => 
     */
    
    require_once '%PSC_CMS%core.phar.gz';
    
    $core = new \Psc\Core();
    $core->autoUpdate('psc-cms');
    
    $core->load('tiptoi');
    
    
  }
}
?>