<?php

namespace Psc\Data;

use Psc\Data\Walker;
use Psc\Code\Generate\GClass;
use Psc\Code\Code;

/**
 * @group class:Psc\Data\Walker
 */
class WalkerTest extends \Psc\Code\Test\Base {

  public function testWalkerAcceptance() {
    $walkable = $this->createWalkable();
    
    $walker = new DocWalker();
    
    $return = $walker->walkWalkable($walkable);
    
    $this->assertEquals('@Psc\Data\Set(name="oids_pages", joinColumns={@Psc\Data\Set(name="oid", referencedColumnName="oid", onDelete="cascade"), @Psc\Data\Set(name="page_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@Psc\Data\Set(name="page_id", referencedColumnName="id", onDelete="cascade"), @Psc\Data\Set(name="oid", referencedColumnName="oid", onDelete="cascade")})',
                        $return
                        );
  }
  
  public function testWalkerBoolean() {
    $walker = new DocWalker();
    $this->assertEquals('TRUE', $walker->walk(TRUE, $this->getType('Boolean')));
  }

  public function testWalkerInteger() {
    $walker = new DocWalker();
    $this->assertEquals('7', $walker->walk(7, $this->getType('Integer')));
  }
  
  public function testWalkableHintType() {
    $this->markTestIncomplete('Type Mock');
    $walker = new DocWalker();
    // gaga muss auch hint sein
    $walkableHintType = $this->getMock('Psc\Data\Type\WalkableHintType', array('getWalkableHint'));
    $walkableHintType->expects($this->once())->method('getWalkableHint')->will($this->returnValue('String'));
    
    $this->assertEquals('someString', $walker->walk('someString', $walkableHintType));
  }
  
  public function createWalkable() {
    $t = function ($type) { return \Psc\Data\Type\Type::create($type); };
    
    $set = new Set(); // set implements Walkable
    $set->set('name','oids_pages', $t('String'));
    
    $j1 = new Set();
    $j1->set('name', 'oid', $t('String'));
    $j1->set('referencedColumnName', 'oid', $t('String'));
    $j1->set('onDelete', 'cascade', $t('String'));

    $j2 = new Set();
    $j2->set('name', 'page_id', $t('String'));
    $j2->set('referencedColumnName', 'id', $t('String'));
    $j2->set('onDelete', 'cascade', $t('String'));
    
    $set->set('joinColumns', array($j1, $j2), new Type\ArrayType(new Type\ObjectType(new GClass('Psc\Data\Walkable'))));
    $set->set('inverseJoinColumns', array($j2, $j1), new Type\ArrayType(new Type\ObjectType(new GClass('Psc\Data\Walkable'))));
    
    return $set;
  }
}

class DocWalker extends \Psc\Data\Walker {
  
  public function decorateString($string) {
    return sprintf('"%s"', $string);
  }

  public function decorateArrayEntry($walkedEntry, $key) {
    if (is_string($key))
      return sprintf('"%s"=%s', $key, $walkedEntry);
    else  
      return $walkedEntry;
  }
  
  public function decorateArray($walkedEntries, $arrayType) {
    return '{'.implode(", ",$walkedEntries).'}';
  }
  
  public function decorateField($field, $walkedValue, $fieldType) {
    return sprintf("%s=%s",$field, $walkedValue);
  }
  
  public function decorateWalkable($walkable, $walkedFields) {
    return '@'.Code::getClass($walkable).'('.implode(', ',$walkedFields).')';
  }
}
?>