<?php

namespace Psc\CMS;

use Psc\CMS\AbstractEntity;
use Psc\Code\Event\Event;

/**
 * @group class:Psc\CMS\AbstractEntity
 */
class AbstractEntityTest extends \Psc\Code\Test\Base {
  
  protected $defaultEntity;
  
  public function setUp() {
    parent::setUp();
    $this->defaultEntity = $this->createInstance(1);
    $this->chainClass = 'Psc\CMS\MyCMSEntity';
  }
  
  public function testExport() {
    $entityName = 'Psc\Doctrine\TestEntities\Article';
    $article = new \Psc\Doctrine\TestEntities\Article('Lorem Ipsum:', 'Lorem Ipsum Dolor sit amet... <more>');
    $article->setId(7);
    
    $object = $article->export();
    
    $this->assertInstanceOf('stdClass',$object);
    $this->assertAttributeEquals(7, 'id', $object);
    $this->assertAttributeEquals('Lorem Ipsum:', 'title', $object);
    $this->assertAttributeEquals('Lorem Ipsum Dolor sit amet... <more>', 'content', $object);
  }
  
  protected function createInstance($identifier = NULL) {
    return new MyCMSEntity($identifier);
  }
}

class MyCMSEntity extends AbstractEntity {
  
  protected $identifier;
  
  protected $birthday;
  
  public function __construct($identifier) {
    $this->identifier = $identifier;
  }
  
  public function getIdentifier() {
    return $this->identifier;
  }

  public function setIdentifier($id) {
    $this->identifier = $id;
    return $this;
  }
  
  public function getBirthday() {
    return $this->birthday;
  }
  
  public function setBirthday(DateTime $birthday) {
    $this->birthday = $birthday;
    return $this;
  }

  public function getTabsURL(Array $qvars = array()) {
    return NULL;
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'identifier' => new \Webforge\Types\IdType(),
      'birthday' => new \Webforge\Types\BirthdayType()
    ));
  }
  
  public function getEntityName() {
    return 'Psc\CMS\MyCMSEntity';
  }
}
