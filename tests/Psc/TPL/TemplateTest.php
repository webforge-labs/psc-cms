<?php

namespace Psc\TPL;

use Psc\TPL\Template;

/**
 * @group class:Psc\TPL\Template
 */
class TemplateTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\TPL\Template';
    parent::setUp();
  }
  
  public function testHTMLInterface() {
    $template = $this->createTemplate('throwsException');
    
    $this->assertInstanceOf('Psc\HTML\HTMLInterface', $template);
  }

  public function testInnnerTemplateExceptionDecoration() {
    $template = $this->createTemplate('throwsException');
    
    try {
      $template->get();
    } catch (\Psc\Exception $e) {
      $this->assertEquals('in TPL(/throwsException.html): Innerhalb des Templates ist ein Fehler aufgetreten', $e->getMessage());
      return NULL;
    }
    
    $this->fail('get() hat entweder die exception gecatched. Oder keine ist von Template: throwsException.html geworfen worden (was komisch wäre)');
  }
  
  public function testGetFileReturnsFile() {
    $tpl = $this->createTemplate('throwsException');
    $tpl->validate();
    $this->assertInstanceOf('Webforge\Common\System\File', $tpl->getFile());
  }
  
  public function testGetVarsReturnsSomething() {
    $this->assertInternalType('array',$this->createTemplate('throwsException')->getVars());
  }

  public function testI18nGetterSetter() {
    $tpl = $this->createTemplate('throwsException');

    $i18n = array();
    $i18n['en']['email'] = 'Email';
    $i18n['de']['email'] = 'E-Mail-Adresse';
    $tpl->setI18n($i18n);
    
    $tpl->setLanguage('de');
    $this->assertEquals('E-Mail-Adresse', $tpl->__('email'));
    $tpl->setLanguage('en');
    $this->assertEquals('Email', $tpl->__('email'));
  }

  public function createTemplate($tpl) {
    return new Template($tpl);
  }
}
?>