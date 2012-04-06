<?php

namespace Psc\System;

use Psc\System\AbstractFile;

class AbstractFileTest extends \Psc\Code\Test\Base {

  public function testExtension() {
    $this->markTestIncomplete('Feature noch nicht geklärt');
    $af = new MyFile('.','filename.txt');
    
    $this->assertEquals('txt', $af->getExtension());
    $this->assertEquals('non', $af->setExtension('non')->getExtension());
  }
  
  public function testConstructors() {
    $this->markTestIncomplete('Feature noch nicht geklärt');
    $f = new MyFile('./www/site/index.php');
    $this->assertEquals('Psc\System\AbstractDir',$f->getDirectory());
    $this->assertEquals('./www/site',(string) $f->getDirectory());
    $this->assertEquals('index.php',(string) $f->getName());

    $f = new MyFile(new MyDir('./www/site/'),'index.php');
    $this->assertEquals('Psc\System\AbstractDir',$f->getDirectory());
    $this->assertEquals('./www/site',(string) $f->getDirectory());
    $this->assertEquals('index.php',(string) $f->getName());

    $f = new MyFile('index.php',new MyDir('./www/site/'));
    $this->assertEquals('Psc\System\AbstractDir',$f->getDirectory());
    $this->assertEquals('./www/site',(string) $f->getDirectory());
    $this->assertEquals('index.php',(string) $f->getName());
  }
}

class MyFile extends AbstractFile {
  
}
?>