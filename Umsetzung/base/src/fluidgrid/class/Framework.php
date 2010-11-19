<?php
/**
 * Kleine Basisklasse für so Krams der Framework Klassen
 * 
 */
class FluidGrid_Framework {
  
  /**
   * Hilfsfunction die call_user_func simuliert
   * 
   * Hier kann man später mal den switch-trick machen für performance
   */
  public static function call_func($callback, Array $args) {
    $argsNum = count($args);

    return call_user_func_array($callback, $args);
  }


  public static function string2id($string) {
    $separator = '-';

		// Remove all characters that are not the separator, a-z, 0-9, or whitespace
		$string = preg_replace('/[^'.$separator.'a-z0-9\s]+/u', '', strtolower($string));

		// Replace all separator characters and whitespace by a single separator
		$string = preg_replace('/['.$separator.'\s]+/u', $separator, $string);

    return $string;
  }
}
?>