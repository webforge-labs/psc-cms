<?php

namespace Psc\UI;

use Psc\Data\Type\Type;
use Psc\Data\Type AS Types;

/*
 * Zeigt beliebige Typ-Daten für den User an
 *
 */
class DataScreener extends \Psc\SimpleObject {
  
  protected $dateTimeFormat = 'd.m.Y H:i';
  protected $dateFormat = 'd.m.Y';
  
  /**
   * @param mixed $value
   */
  public function toString($value, Type $type = NULL) {
    
    if (isset($type)) {
      // wenn wir den typ checken statt der value geht auch (int) $timestamp, new DateTimeType()
      // das ist cooler
      if ($type instanceof Types\DateTimeType) {
        if ($value)
          return $value->i18n_format($this->dateTimeFormat);
      } 
      if ($type instanceof Types\DateType) {
        if ($value)
          return $value->i18n_format($this->dateFormat);
      }
      
      if ($type instanceof Types\EntityType && $value != NULL) {
        return $value->getContextLabel();
      }
      
      $that = $this;
      if ($type instanceof Types\CollectionType) {
        return \Psc\FE\Helper::listObjects(
            $value,
            ', ', // seperator
            function ($innerValue) use ($that, $type) { // ersetzt den getter
              try {
                return $that->toString($innerValue, $type->getType()); 
              } catch (\Psc\Data\Type\NotTypedException $e) {
                // was tun wenn wir den inner type nicht kennen?
                return $that->toString($innerValue);
              }
            }
          );
      }

      if ($type instanceof Types\I18nType) {
        return \Psc\FE\Helper::listObjects(
            $value,
            "<br />", // seperator
            function ($innerValue, $key) use ($that, $type) { // ersetzt den getter
              try {
                return '['.$key.'] '.$that->toString($innerValue, $type->getType()); 
              } catch (\Psc\Data\Type\NotTypedException $e) {
                // was tun wenn wir den inner type nicht kennen?
                return $that->toString($innerValue);
              }
            }
          );
      }
      
      if ($type instanceof Types\BooleanType) {
        if ($value == TRUE)
          return '<span class="ui-icon ui-icon-check"></span>';
        else
          return '';
      }
      
      if ($type instanceof Types\MarkupTextType) {
        return \Psc\TPL\TPL::MiniMarkup($value);
      }
    }
    
    return (string) $value;
  }
}
?>