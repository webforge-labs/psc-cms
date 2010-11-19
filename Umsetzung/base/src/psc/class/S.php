<?php

class S extends Object {

/**
 * Überprüft ob der String mit einem bestimmen Prefix beginnt
 * 
 * @param string $string Haystack
 * @param string $string Prefix auf welchen überprüft werden soll
 * @param int $offset die Stelle von der an überprüft werden soll
 */
  public static function startsWith($string,$prefix,$offset=0) {
    return (mb_strpos($string,$prefix) === $offset);
  }

/**
 * Überprüft ob der String mit einem bestimmen Suffix endet
 * 
 * @param string $string Haystack
 * @param string $suffix der Suffix auf welchen überprüft werden soll
 */
  public static function endsWith($string, $suffix) {
    return (mb_strrpos($string,$suffix) === mb_strlen($string) - mb_strlen($suffix));
  }

}
?>