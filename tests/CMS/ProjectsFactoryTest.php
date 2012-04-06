<?php

use \Psc\PSC;

class ProjectsFactoryTest extends \PHPUnit_Framework_TestCase {
  
  public function testFactory() {
    $factory = PSC::getProjectsFactory();
    
    $cms = $factory->getProject('psc-cms');
    $tiptoi = $factory->getProject('tiptoi');
    
    $this->assertInstanceOf('Psc\Project',$cms); // extends Psc\CMS\Project
    
    if (PSC::getProject()->getHost() == 'psc-laptop') {
      
      $this->assertEquals('D:\www\psc-cms\Umsetzung\\',(string) $cms->getRoot());
      $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\\',(string) $cms->getSrc());
      $this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\class\Psc\\',(string) $cms->getClassPath());
      $this->assertEquals(TRUE,$cms->getProduction(),'Production');
      
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\\',(string) $tiptoi->getRoot());
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\src\tiptoi\\',(string) $tiptoi->getClassPath());
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\\',(string) $tiptoi->getPath(PSC::PATH_BASE));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\src\\',(string) $tiptoi->getPath(PSC::PATH_SRC));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\files\testdata\\',(string) $tiptoi->getPath(PSC::PATH_TESTDATA));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\files\\',(string) $tiptoi->getPath(PSC::PATH_FILES));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\cache\\',(string) $tiptoi->getPath(PSC::PATH_CACHE));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\src\tpl\\',(string) $tiptoi->getPath(PSC::PATH_TPL));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\bin\\',(string) $tiptoi->getPath(PSC::PATH_BIN));
      $this->assertEquals('D:\www\RvtiptoiCMS\Umsetzung\base\htdocs\\',(string) $tiptoi->getPath(PSC::PATH_HTDOCS));
      
    } else {
      $this->markTestSkipped('kein Switch für Host '.PSC::getHost().' angegeben');
    }
  }
  
  public function testProjectConfigLoading() {
    $factory = PSC::getProjectsFactory();
    $cms = $factory->getProject('psc-cms');
    $tiptoi = $factory->getProject('tiptoi');
    
    $this->assertEquals('psc-cms',$cms->getName());
    $this->assertEquals('tiptoi',$tiptoi->getName());
    
    $this->assertEquals('valueinpsc-cms',$cms->getConfiguration()->get('fixture.config.variable'));
    $this->assertEquals('valueintiptoi',$tiptoi->getConfiguration()->get('fixture.config.variable'));
  }
}
?>