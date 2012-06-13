<?php

namespace Psc\Doctrine;

use Psc\Doctrine\Annotation;

/**
 * @group class:Psc\Doctrine\Annotation
 * @group entity-building
 */
class AnnotationTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\Annotation';
    parent::setUp();
  }
  
  public function testClosureHelpers_ManyToMany() {
    extract(Annotation::getClosureHelpers());
    
    $m2m = $this->createORMAnnotation('ManyToMany');
    $m2m->targetEntity = 'Person';
    $m2m->mappedBy = 'addresses';
    $m2m->cascade = array();
    $m2m->fetch = 'EAGER';
    
    $this->assertEquals($m2m, $manyToMany('Person', $mappedBy('addresses'), array(), 'EAGER'));
    return $m2m;
  }

  public function testClosureHelpers_OneToMany() {
    extract(Annotation::getClosureHelpers());
    
    $m2m = $this->createORMAnnotation('OneToMany');
    $m2m->targetEntity = 'Person';
    $m2m->mappedBy = 'address';
    
    $this->assertEquals($m2m, $oneToMany('Person', $mappedBy('address')));
    return $m2m;
  }

  public function testClosureHelpers_ManyToOne() {
    extract(Annotation::getClosureHelpers());
    
    $m2m = $this->createORMAnnotation('ManyToOne');
    $m2m->targetEntity = 'Address';
    $m2m->inversedBy = 'persons';
    
    $this->assertEquals($m2m, $manyToOne('Address', $inversedBy('persons')));
    return $m2m;
  }

  public function testClosureHelpers_joinColumn() {
    extract(Annotation::getClosureHelpers());
    
    $jc = $this->createORMAnnotation('joinColumn');
    $jc->name = 'product_id';
    $jc->referencedColumnName = 'id';
    $jc->onDelete = 'cascade';
    
    $this->assertEquals($jc, $joinColumn('product_id','id','cascade'));
    return $jc;
  }
  
  /**
   * @depends testClosureHelpers_joinColumn
   */
  public function testClosureHelpers_joinTable($jc) {
    extract(Annotation::getClosureHelpers());
    
    $table = $this->createORMAnnotation('joinTable');
    $table->name = 'users_products';
    $table->joinColumns = array($jc);
    
    $ijc = $this->createORMAnnotation('joinColumn');
    $ijc->name = 'user_email';
    $ijc->referencedColumnName = 'email';
    $ijc->onDelete = 'cascade';
    $table->inverseJoinColumns = array($ijc);
    
    $this->assertEquals($table, $joinTable('users_products', $jc, $ijc));
    return $table;
  }
  
  /**
   * @depends testClosureHelpers_joinTable
   * das ist nicht der gleiche test wie oben testClosureHelpers_joinTable da hier alles per helpers constructed wird
   */
  public function testTableAcceptance($table) {
    /* @JoinTable(name="users_products",
    *     joinColumns={@JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")},
    *     inverseJoinColumns={@JoinColumn(name="user_email", referencedColumnName="email", onDelete="cascade")})
    */
    extract(Annotation::getClosureHelpers());
   
    $fastTable = $joinTable('users_products',
                  $joinColumn('product_id', 'id', 'cascade'),
                  $joinColumn('user_email', 'email', 'cascade')
                );
   
    $this->assertEquals($table, $fastTable);
    
    return $fastTable;
  }
  
  /**
   * @depends testTableAcceptance
   */
  public function testToStringAcceptance($table) {
    $this->assertEquals('@ORM\JoinTable(name="users_products", joinColumns={@ORM\JoinColumn(name="product_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="user_email", referencedColumnName="email", onDelete="cascade")})',
          $table->toString()
        );
    // in der ersten joinColumn wird hier referencedColumnName nicht im string ausgegeben, da es der default für die annotation ist
  }
  
  public function testNullableRegression() {
    $column = $this->createORMAnnotation('Column');
    $column->type = 'string';
    $column->nullable = true;
    
    $this->assertEquals('@ORM\Column(nullable=true)', $column->toString());
  }
  
  public function testCreateFactory() {
    $expected = $this->createORMAnnotation('ManyToMany');
    $actual = Annotation::create('Doctrine\ORM\Mapping\ManyToMany');
    $this->assertEquals($expected, $actual);
  }
  
  public function testPropertiesSetting() {
    $m2mp = $this->createORMAnnotation('ManyToMany');
    
    $this->assertChainable($m2mp->setProperties(array(
      'targetEntity'=>'Person',
      'mappedBy'=>'addresses',
      'cascade'=>array()
    )));

    $m2m = $this->createORMAnnotation('ManyToMany');
    $m2m->targetEntity = 'Person';
    $m2m->mappedBy = 'addresses';
    $m2m->cascade = array();

    $this->assertEquals($m2m->unwrap(), $m2mp->unwrap());
  }
  
  
  public function testGetGettingAndSettingMagic() {
    $manyToMany = $this->createORMAnnotation('ManyToMany');
    $manyToMany->setTargetEntity('Psc\Doctrine\TestEntities\Tag');
    
    $this->assertEquals('Psc\Doctrine\TestEntities\Tag', $manyToMany->getTargetEntity());
  }
  
  /**
   * @expectedException Psc\MissingPropertyException
   */
  public function testSettingWrongProperty() {
    $m2m = $this->createORMAnnotation('ManyToMany');
    $m2m->blubb = 'fail';
  }

  /**
   * @expectedException Psc\MissingPropertyException
   */
  public function testSettingWrongProperty_inSetProperties() {
    $m2m = $this->createORMAnnotation('ManyToMany');
    $m2m->setProperties(array(
     'targetEntity'=>'ok',
     'blubb'=>'fail'
    ));
  }
  
  /**
   * @expectedException Psc\MissingPropertyException
   */
  public function testGettingWrongProperty() {
    $m2m = $this->createORMAnnotation('ManyToMany');
    $m2m->blubb;
  }
  
  public function testGettingProperty() {
    $m2m = $this->createORMAnnotation('ManyToMany');
    $m2m->targetEntity = 'Entities\Setted';
    
    $this->assertEquals('Entities\Setted', $m2m->targetEntity);
  }

  /**
   * @expectedException Psc\Exception
   * // irgendeine, nicht sehr schön, aber okay
   */
  public function testClassHasToExist() {
    $m2m = $this->createORMAnnotation('nonExistantClassInDoctrine');
  }

  
  protected function createORMAnnotation($name) {
    return new Annotation('Doctrine\ORM\Mapping\\'.$name);
  }
  
}
?>