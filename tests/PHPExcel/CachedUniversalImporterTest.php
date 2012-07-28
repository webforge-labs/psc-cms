<?php

namespace Psc\PHPExcel;

use Psc\Data\PHPFileCache;

/**
 * @group class:Psc\PHPExcel\CachedUniversalImporter
 */
class CachedUniversalImporterTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\PHPExcel\CachedUniversalImporter';
    parent::setUp();
    \Psc\PSC::getProject()->getModule('PHPExcel')->bootstrap();
  }
  
  public function testSimpleImport() {
    $importer = new CachedUniversalImporter($this->getFile('fixture.xlsx'), 'simple',
                                            new PHPFileCache($this->newFile('storage.simplerowscache.php'))
                                            );
    
    $importer->setUpdateCache(TRUE);
    $importer->setOption('maxColumn','AC');
    $importer->addColumnMapping('A', array('column-a'));
    $importer->addColumnMapping('B', array('column-b'));
    $importer->addColumnMapping('C', array('column-c'));
    $importer->addColumnMapping('AA', array('column-aa'));
    $importer->addColumnMapping('AB', array('column-ab'));
    
    $importer->init();
    $rows = $importer->getRows();
    for ($row = 1; $row <= 14; $row++) {
      $this->assertEquals('a'.$row, $rows[$row]['column-a']);
      $this->assertEquals('b'.$row, $rows[$row]['column-b']);
      $this->assertEquals('c'.$row, $rows[$row]['column-c']); // in der Zelle C2 steht die Value "c2"
      $this->assertEquals('aa'.$row, $rows[$row]['column-aa']);
      $this->assertEquals('ab'.$row, $rows[$row]['column-ab']);
    }
  }
  
  public function testImport() {
    return;
    $importer = new CachedUniversalImporter($this->getFile('fixture.xlsx'), 'Tasks',
                                            new PHPFileCache($this->newFile('storage.rowscache.php'))
                                            );
    
    $importer->setUpdateCache(TRUE);
    $importer->addColumnMapping('A', array('label'));
    //$importer->addColumnMapping('BZ', array(32,'text'));
    $importer->addColumnMapping('F', array('text'));
    $importer->addColumnMapping('G', array('number'));
    $importer->setOption('maxColumn','CM');
    $importer->init();
  }
  
  public function createCachedUniversalImporter() {
    return new CachedUniversalImporter();
  }
}
?>