<?php

namespace Psc\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;


class DateTimeType extends \Doctrine\DBAL\Types\DateTimeType {

  public function convertToPHPValue($value, AbstractPlatform $platform) {
    if ($value === null) {
      return null;
    }

    $val = \Psc\DateTime\DateTime::parse($platform->getDateTimeFormatString(), $value);
    if (!$val) {
      throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
    }
    
    return $val;
  }
  
  public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
    return sprintf("%s COMMENT '%s' ",
                   parent::getSQLDeclaration($fieldDeclaration, $platform),
                   $platform->getDoctrineTypeComment($this)
                  );
  }
  
  public function getName() {
    return 'PscDateTime';
  }
}
?>