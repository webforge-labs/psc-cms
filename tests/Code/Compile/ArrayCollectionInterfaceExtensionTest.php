<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\GClass;
use Psc\Data\Column;
use Psc\Data\Type\Type;

/**
 * @group class:Psc\Code\Compile\ArrayCollectionInterfaceExtension
 */
class ArrayCollectionInterfaceExtensionTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Compile\ArrayCollectionInterfaceExtension';
    parent::setUp();
    $this->extension = new ArrayCollectionInterfaceExtension('columns','column','Object<Psc\Data\Column>');
  }
  
  public function testAcceptance() {
    $gClass = new GClass(__NAMESPACE__.'\Table'.uniqid('table'));
    
    $this->extension->compile($gClass);
    
    // quicktest
    $this->test->gClass($gClass)
      ->hasProperty('columns')

      ->hasMethod('getColumns')
      ->hasMethod('setColumns', array('columns'))
      
      ->hasMethod('removeColumn', array('column'))
      ->hasMethod('addColumn', array('column'))
      ->hasMethod('hasColumn', array('column'))
    ;
    
    // acceptance
    $classWriter = new ClassWriter($gClass);
    $classWriter->write($file = $this->newFile('out.php'),array(), ClassWriter::OVERWRITE);
    
    require $file;
    $table = $gClass->newInstance();
    
    $c1 = new Column('c1', Type::create('String'),'Spalte 1');
    $c2 = new Column('c2', Type::create('Integer'), 'Spalte 2');
    
    $this->assertInternalType('array', $table->getColumns(), 'getColumns gibt keinen Array zurück');
    $this->assertCount(0, $table->getColumns());
    
    $table->setColumns(array($c1,$c2));
    $this->assertEquals(array($c1,$c2), $table->getColumns(), 'setColumns() funktioniert nicht richtig');
    
    $this->assertTrue($table->hasColumn($c1), 'hasColumn(c1) gibt nicht true zurück');
    $this->assertTrue($table->hasColumn($c2), 'hasColumn(c2) gibt nicht true zurück');
    
    $table->removeColumn($c1);
    $this->assertEquals(array($c2), $table->getColumns(), 'c2 wurde nicht entfernt');
    $this->assertFalse($table->hasColumn($c1), 'hasColumn(c1) muss false sein nach removen');
    $this->assertTrue($table->hasColumn($c2), 'hasColumn(c2) muss noch true sein nach dem removen von c1');
  }
}
?>