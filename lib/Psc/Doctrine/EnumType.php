<?php

namespace Psc\Doctrine;

use Doctrine\DBAL\Types\Type,
    Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Klasse für einen OO-getypten Enum eines Entities
 *
 * in der extended-Klasse sind dann values gesetzt und name gesetzt. Kann auch die Konstanten beeinhalten - wenn man welche will - fertig
 * dann muss
 * \Doctrine\DBAL\Types\Type::addType($this->name, __CLASS__); gemacht werden
 *
 * z. B:
 * \Doctrine\DBAL\Types\Type::addType('serienloader_status', 'SerienLoader\Status');
 *
 * im Entity müsste dann für das Status-Beispiel angegeben werden:
 *
 * /**
 *  * @Column(type="serienloader_status")
 *  * @var SerienLoader\Status
 *  *
 * protected $status;
 * 
 */
abstract class EnumType extends Type {

  protected $name;
  protected $values = array();

  public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
    $values = array_map(function($val) { return "'".$val."'"; }, $this->values);
    
    return sprintf("ENUM(%s) COMMENT '%s'", implode(', ', $values), $platform->getDoctrineTypeComment($this));
  }

  public function convertToPHPValue($value, AbstractPlatform $platform) {
    return $value;
  }

  public function convertToDatabaseValue($value, AbstractPlatform $platform) {
    if (!in_array($value, $this->getValues())) {
      throw new \InvalidArgumentException("Invalid '".$this->name."' value ".\Psc\Code\Code::varInfo($value).' Erlaubt sind: '.\Psc\Code\Code::varInfo($this->values));
    }
    
    return $value;
  }

  public function getName() {
    return $this->name;
  }

  public function getValues() {
    return $this->values;
  }
  
  public function validate($value) {
    return in_array($value, $this->values);
  }
  
  /**
   * @return Doctrine\DBAL\Types\Type
   */
  public static function instance() {
    throw new Exception('Implement in subclass()');
  }
}

?>