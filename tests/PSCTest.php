<?php

use \Psc\PSC,
    \Psc\System\File,
    \Psc\System\Dir
;

/**
 * @group class:PSC
 */
class PSCTest extends PHPUnit_Framework_TestCase {
  
  protected $srcPath;
  
  public function setUp() {
    $this->srcPath = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.str_repeat('..'.DIRECTORY_SEPARATOR,2)).DIRECTORY_SEPARATOR;
  }
  
  public function testClassName() {
    $ds = DIRECTORY_SEPARATOR;
    $this->assertEquals('\Psc\HTML\Tag', PSC::getFullClassName(new File($this->srcPath.'psc'.$ds.'class'.$ds.'Psc'.$ds.'HTML'.$ds.'Tag'.$ds)));
    $this->assertEquals('\Psc\PHPJSC\Object', PSC::getFullClassName(new File($this->srcPath.'psc'.$ds.'class'.$ds.'Psc'.$ds.'PHPJSC'.$ds.'Object')));
  }
  
  /**
   * @expectedException \Psc\ProjectNotFoundException
   */
  public function testGetWrongProject() {
    PSC::getProjectsFactory()->getProject('hononululuJuicer');
  }
  
  public function instanceClass() {
    
    new Psc\Form\NesValidatorRule();
    
    \Psc\Form\StandardValidatorRule::generateRule('nes');
    
  }
  
  public function testMethods() {
    $this->assertInstanceOf('Psc\CMS\ProjectsFactory', PSC::getProjectsFactory());
    $this->assertInstanceOf('Psc\CMS\Project', PSC::getProject());
    $this->assertInstanceOf('Psc\Code\ErrorHandler', PSC::getErrorHandler());
    $this->assertInstanceOf('Psc\Environment', PSC::getEnvironment());
    $this->assertTrue(PSC::inTests());
    
    
    $ds = DIRECTORY_SEPARATOR;
    $this->assertEquals($this->srcPath.'psc'.$ds.'class'.$ds.'Psc'.$ds.'CMS'.$ds.'Project.php',
                        (string) PSC::getClassFile('\Psc\CMS\Project')
                      );
    
    $this->assertEquals('psc-cms', PSC::getProject()->getName());
    $this->assertEquals($this->srcPath.'psc'.$ds.'tests'.$ds, (string) PSC::getProject()->getTestsPath());
  }
  
  public function testModuleBootstraps() {
    $doctrine = PSC::getProject()->getModule('Doctrine');
    
    $this->assertInstanceOf('Psc\Doctrine\Module',$doctrine);
    $em = $doctrine->getEntityManager();
    $this->assertEquals('tests',$doctrine->getConnectionName());
    $this->assertEquals($em,\Psc\Doctrine\Helper::em());
    
    $this->assertEquals(TRUE,PSC::getProject()->getTests());
    $this->assertEquals(PSC::getProject()->getLowerName().'_tests',$em->getConnection()->getDatabase());
  }
  
  public function testEventManager() {
    $this->assertInstanceOf('Psc\Code\Event\Manager',PSC::getEventManager());
  }
}