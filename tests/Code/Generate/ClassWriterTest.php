<?php

namespace Psc\Code\Generate;

use \Psc\Code\Generate\ClassWriter;

/**
 * @group generate
 * @group class:Psc\Code\Generate\ClassWriter
 */
class ClassWriterTest extends \Psc\Code\Test\Base {
  
  public function testBugWithoutUse() {
    
    $gClass = new GClass('TestClass');
    $gClass->setNamespace('\Psc\habe\keinen');
    
    $writer = new ClassWriter();
    $writer->setClass($gClass);
    $writer->write($f = $this->newFile('buguse.php'),array(),ClassWriter::OVERWRITE);
    
    $this->assertFileExists((string) $f);
    
    $this->assertEquals(<<< 'FILE_CODE'
<?php

namespace Psc\habe\keinen;

class TestClass {
}
?>
FILE_CODE
                      , $f->getContents());
  }
  
  public function testBugWithUse() {
    
    $gClass = new GClass('TestClass');
    $gClass->setNamespace('\Psc\habe\keinen');
    
    $writer = new ClassWriter();
    $writer->setClass($gClass);
    $writer->write($f = $this->newFile('buguse2.php'),array(new GClass('\Psc\using\that')),ClassWriter::OVERWRITE);
    
    $this->assertFileExists((string) $f);
    
    $this->assertEquals(<<< 'FILE_CODE'
<?php

namespace Psc\habe\keinen;

use Psc\using\that;

class TestClass {
}
?>
FILE_CODE
    , $f->getContents());
  }
  
  protected function getUseClassWriter() {
    $gClass = new GClass('TestClass');
    $gClass->setNamespace('\Psc\habe\keinen');
    
    $writer = new ClassWriter();
    $writer->setClass($gClass);
    $writer->addImport(new GClass('Psc\System\File'),'SystemFile');
    $writer->addImport(new GClass('Psc\Doctrine\Helper'),'DoctrineHelper');
    
    return $writer;
    
  }
  
  public function testUseAlias() {
    $writer = $this->getUseClassWriter();
    $imports = Array(array(new GClass('\Psc\using\that'), 'aliasOfThat'));
    $writer->write($f = $this->newFile('usetest.php'),$imports, ClassWriter::OVERWRITE);
    
    $this->assertEquals(<<< 'FILE_CODE'
<?php

namespace Psc\habe\keinen;

use Psc\System\File AS SystemFile,
    Psc\Doctrine\Helper AS DoctrineHelper,
    Psc\using\that AS aliasOfThat;

class TestClass {
}
?>
FILE_CODE
    , $f->getContents());
    
    return $writer;
  }
  
  public function testUseLinesStyle() {
    $writer = $this->getUseClassWriter();
    $writer->setUseStyle('lines');
    
    $imports = Array(array(new GClass('\Psc\using\that'), 'aliasOfThat'));
    $writer->write($f = $this->newFile('usetest.lines.php'),$imports, ClassWriter::OVERWRITE);
    $this->assertEquals(<<< 'FILE_CODE'
<?php

namespace Psc\habe\keinen;

use Psc\System\File AS SystemFile;
use Psc\Doctrine\Helper AS DoctrineHelper;
use Psc\using\that AS aliasOfThat;

class TestClass {
}
?>
FILE_CODE
    , $f->getContents());
  }
  
  /**
   * @expectedException Psc\Code\Generate\Exception
   */
  public function testUseExplicitDoubleAlias() {
    $writer = new ClassWriter();
    $writer->setClass(new GClass('TestClass'));
    $writer->addImport(new GClass('Psc\System\File'),'SystemFile');
    $writer->addImport(new GClass('Psc\Another\File'),'SystemFile');
  }

  /**
   * @expectedException Psc\Code\Generate\Exception
   */
  public function testUseImplicitDoubleAlias() {
    $writer = new ClassWriter();
    $writer->setClass(new GClass('TestClass'));
    $writer->addImport(new GClass('Psc\System\File'));
    $writer->addImport(new GClass('Psc\Another\File'));
  }
}

?>