<?php

namespace Psc\Hitch;

use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\GClass;
use Psc\System\File;

class MockGenerator extends Generator {
  
  public $mockDir;
  
  public function getGenerationFile(GClass $class) {
    return new File($this->mockDir, $class->getClassName().'.php');
  }
}

/**
 * @group Hitch
 */
class GeneratorTest extends \Psc\Code\Test\Base {
  
  public function assertPreConditions() {
    $this->assertTrue($this->getFile('Issue.php')->delete());
    $this->assertTrue($this->getFile('Volume.php')->delete());
    $this->assertTrue($this->getFile('Journal.php')->delete());
  }

  public function testXMLGeneration() {
    
    $generator = new MockGenerator(new ClassWriter());
    $generator->mockDir = $this->getTestDirectory();
    $generator->setBaseClass(new GClass('Psc\XML\Object'));
    $generator->setNamespace('ePaper42');
    
    $this->assertInstanceof('Psc\Code\Generate\GClass',
      $generator->addObject('Journal', Array(
        Property::create('title', Property::TYPE_ATTRIBUTE),
        Property::create('key', Property::TYPE_ATTRIBUTE),
        Property::create('volumes',Property::TYPE_LIST)
          ->setXMLName('volume')
          ->setObjectType('Volume')
          ->setWrapper('volumeList'),
      ))
    );
    
    $generator->addObject('Volume', Array(
      Property::create('title', Property::TYPE_ATTRIBUTE),
      Property::create('key', Property::TYPE_ATTRIBUTE),
      Property::create('issues',Property::TYPE_LIST)
        ->setXMLName('issue')
        ->setObjectType('Issue')
        ->setWrapper('issueList')
    ));
    
    $generator->addObject('Issue', Array(
      Property::create('title', Property::TYPE_ATTRIBUTE),
      Property::create('key', Property::TYPE_ATTRIBUTE),
      Property::create('exportDate', Property::TYPE_ATTRIBUTE),
      Property::create('cover', Property::TYPE_ATTRIBUTE),
      Property::create('categories',Property::TYPE_LIST)
        ->setXMLName('category')
        ->setObjectType('Category')
        ->setWrapper('categoryList'),
      Property::create('pages',Property::TYPE_LIST)
        ->setXMLName('page')
        ->setObjectType('Page')
        ->setWrapper('pageList'),
      Property::create('articles',Property::TYPE_LIST)
        ->setXMLName('article')
        ->setObjectType('Article')
        ->setWrapper('articleList'),
      Property::create('clickPages',Property::TYPE_LIST)
        ->setXMLName('clickPage')
        ->setObjectType('ClickPage')
        ->setWrapper('clickPageList')
    ));
    
    $classes = $generator->generate();
    
    foreach ($classes as $file=>$gClass) {
      $fixtureFile = $this->getFile($gClass->getClassName().'.fixture.php');
      $this->assertFileEquals((string) $fixtureFile, (string) $file);
    }
  }
}

?>