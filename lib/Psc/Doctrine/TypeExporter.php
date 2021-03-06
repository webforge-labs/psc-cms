<?php

namespace Psc\Doctrine;

use Webforge\Types\Type;
use Doctrine\DBAL\Types\Type AS DC;
use Webforge\Types\TypeExportException;
use Webforge\Types\DoctrineExportableType;
use Webforge\Types\TypeConversionException;
use Webforge\Types\Exporter;

/**
 * Wandelt einen Typ in den String um der in @Doctrine\ORM\Mapping\Column(type="%s")  benutzt werden kann
 *
 * Wandelt auch eine DC::Type Konstante in den PscType um (siehe getPscType)
 */
class TypeExporter extends \Psc\SimpleObject implements Exporter {
    // Doctrine Types!
    //const TARRAY = 'array';
    //const BIGINT = 'bigint';
    //const BOOLEAN = 'boolean';
    //const DATETIME = 'datetime';
    //const DATETIMETZ = 'datetimetz';
    //const DATE = 'date';
    //const TIME = 'time';
    //const DECIMAL = 'decimal';
    //const INTEGER = 'integer';
    //const OBJECT = 'object';
    //const SMALLINT = 'smallint';
    //const STRING = 'string';
    //const TEXT = 'text';
    //const BLOB = 'blob';
    //const FLOAT = 'float';
  
  protected $casts = array(
    'Array'=>DC::TARRAY,
    'String'=>DC::STRING,
    'Integer'=>DC::INTEGER,
    'Boolean'=>DC::BOOLEAN,
    'SmallInteger'=>DC::SMALLINT,
    'Text'=>DC::TEXT,
    'Float'=>DC::FLOAT
  );
  
  public function exportType(Type $type) {
    
    if (array_key_exists($tn = $type->getName(), $this->casts)) {
      return $this->casts[$tn];
    }
    
    // Explicit Interface in der Type-Klasse selbst
    if ($type instanceof DoctrineExportableType) {
      // keinen dynamischen cache einbauen für z.b. DCEnumType,
      // wir machen den ganz aus, denn der performance overhead sollte minimal sein
      return $type->getDoctrineExportType(); 
    }
    
    throw new TypeExportException(sprintf("Es konnte kein DoctrineExportType für: '%s' gefunden werden. Dieser Typ sollte \Webfoge\Types\DoctrineExportableType implementieren.",$tn));
    // YAGNI?
  }
  
  /**
   * Wandelt einen Doctrine Type in einen Psc Type um
   * 
   * @return Webforge\Types\Type
   */
  public function getPscType($dcTypeConstant) {
    if ($dcTypeConstant === NULL) throw new TypeConversionException('dcTypeConstant kann nicht NULL sein');
    
    $flip = array_flip($this->casts);
    
    if (!array_key_exists($dcTypeConstant, $flip)) {
      throw TypeConversionException::typeTarget('Doctrine-Type: '.$dcTypeConstant, 'Psc-Type');
    }
    
    return Type::create($flip[$dcTypeConstant]);
  }
}
