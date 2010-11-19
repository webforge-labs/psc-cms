<?php

class A extends Object {

  /**
   * Fügt einen Array zu einem String zusammen
   * 
   * Aus einem Array ein geschriebenes Array machen:
   * print '$GLOBALS'.A::join(array('conf','default','db'), "['%s']");
   * // $GLOBALS['conf']['default']['db']
   * das erste %s ist der Wert aus dem Array das zweite %s ist der Schlüssel aus dem Array.
   * Diese können mit %1$s (wert) %2$s (schlüssel) addressiert werden
   * @param array $pieces die Elemente die zusammengefügt werden sollen
   * @param string $glue der String welcher die Elemente umgeben soll. Er muss %s enthalten. An dieser Stelle wird das Array Element ersetzt
   * @param string $property wenn gesetzt werden die Elemente in $pieces für Objekte gehalten und das Element mit index i mit $pieces[i]->$property gejoined
   */  
  public function join(Array $pieces, $glue, $property = NULL) {

    $s = NULL;
    foreach ($pieces as $key =>$piece) {
      if (isset($property))
        $piece = $piece->$property;

      $s .= sprintf($glue,$piece,$key);
    }
    
    return $s;
  }
}

?>