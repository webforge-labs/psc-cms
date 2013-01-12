<?php

namespace Psc\Form;

use \Psc\GPC;

/**
 * Wrapper um $_POST und $_GET herum
 *
 * Setzt die Variablen um und transformierte leere in NULL usw
 * 
 */
class DataInput extends \Psc\DataInput {
  
  protected function clean($value) {
    if(trim($value) == '') {
      $value = NULL;
    } else {
      $value = GPC::clean($value);
    }
    return $value;
  }
  
  /**
   * Gibt das Feld mit einem Defaultwert belegt zurück
   *
   * Im FormDataInput ist der dritte Parameter wirkungslos (er wird auf dasselbe wie $do gesetzt)
   * Deshalb ist hier der dritte Parameter unwirksam denn hier gilt $do = $default
   * zu beachten ist auch, dass der Defaultwert im FormDataInput auch genommen wird wenn der Wert trim() === '' ist
   *
   * also der $default -Wert wird genommen wenn:
   * - im Array die Schlüssel $keys nicht vorkommen
   * - der Wert der Schlüssel $keys im Array entweder === NULL oder trim() == '' ist
   * @see DataInput::get();
*/
  public function get($keys, $do = self::RETURN_NULL, $default = NULL) {
    // der default NULL für exception ist eigentlich uninteressant, da es zur Auswertung dieses Wertes vor der Exception nie kommt
    return parent::get($keys, $do, ($do === self::RETURN_NULL || $do === self::THROW_EXCEPTION) ? NULL : $do);
  }
  
}

class DataInputException extends \Psc\DataInputException {
  
}

?>