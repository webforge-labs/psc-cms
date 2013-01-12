<?php

namespace Psc\PHPWord;

use PHPWord;
use PHPWord_IOFactory;
use Webforge\Common\System\File;

/**
 * @group class:Psc\PHPWord\MainTemplate
 */
class MainTemplateTest extends \Psc\Code\Test\Base {
  
  public static function setUpBeforeClass() {
    \Psc\PSC::getProject()->getModule('PHPWord')->bootstrap();
    
    
  }
  
  public function setUp() {
    $this->chainClass = 'Psc\PHPWord\MainTemplate';
    parent::setUp();
  }
  
  public function testNative() {
    $path = PHPWORD_BASE_PATH . 'PHPWord/_staticDocParts/numbering.xml';
    $this->assertFileExists($path, 'Path: '.$path.' muss zugänglich sein');
    
    $PHPWord = new PHPWord();

    // Every element you want to append to the word document is placed in a section. So you need a section:
    $section = $PHPWord->createSection();

    // After creating a section, you can append elements:
    $section->addText('Hello world!');
    
    $objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
    $objWriter->save(File::createTemporary()->setExtension('docx'));
  }
  
  public function testConstruct() {
    $tpl = new MainTemplate();
    $tpl->addMarkupText('Dies ist ein kleiner aber schöner Test');
    
    $tpl->write($out = $this->newFile('out.docx'));
    
    $this->assertFileExists((string) $out);
  }
}
?>