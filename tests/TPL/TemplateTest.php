<?php

namespace Psc\TPL;

use Psc\TPL\Template;

class TemplateTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\TPL\Template';
    parent::setUp();
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

  public function createTemplate($tpl) {
    return new Template($tpl);
  }
}
?>