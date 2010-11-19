<?php

class Debug extends Object {

  /**
   * Notice
   * 
   * Eine Notice ist eine Meldung die zum debuggen hilfreich sein soll, den normalen Programmablauf aber nicht unterbricht.
   * Notices sollten dann benutzt werden, wenn etwas unordentliches passiert, welches beim sauberen programmieren nicht passieren würde
   */ 
  public static function notice($msg) {
    echo "Notice: ".$msg."<br />";
  }

  /**
   * Performance Notice
   * 
   * Soll dafür benutzt werden, dass darauf aufmerksam gemacht wird, dass der Programmierer Performance gewinnen könnte, wenn er seinen Code verändert
   */
  public static function pnotice($msg)
  
}

?>