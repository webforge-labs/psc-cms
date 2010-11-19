<?php

class Preg extends Object {

  /**
   * Sucht innerhalb eines Strings anhand eines regulären Ausdruckes.
   * 
   * Sucht innerhalb eines Strings anhand eines regulären Ausdruckes. Wrapper für die PHP-Funktionen preg_match und preg_match_all. 
   * Modifier für UTF-8 wird automatisch gesetzt.
   * Der (in PHP nicht bekannte) Modifier "g" unterscheidet, ob preg_match oder preg_match_all ausgeführt wird. 
   * Bei preg_match_all wird PREG_SET_ORDER als default eingesetzt (dies ist ein anderer Default als bei PHP!).
   * @param string $subject Der String, in dem gesucht werden wird
   * @param mixed $pattern Der reguläre Ausdruck (string oder array aus strings)
   * @param mixed &$matches Die Ergebnisse der Suche
   * @param int $flags Flags (vgl. http://www.php.net/manual/en/function.preg-match-all.php)
   * @param int $offset An diesem offset wird begonnen zu matchen.
   * @return mixed Entweder int mit Anzahl der Matches oder FALSE im Fehlerfall
   * @see PHP::preg_match
   * @see PHP::preg_match_all
   */
  public static function match ($subject, $pattern, &$matches=NULL, $flags=NULL, $offset=NULL) {
    $pattern = self::set_u_modifier($pattern, TRUE);
    
    $delimiter = mb_substr($pattern,0 ,1);
    $modifiers = mb_substr($pattern, mb_strrpos($pattern, $delimiter));
    if (mb_strpos ($modifiers, 'g') !== FALSE) {
      // remove g modifier but use preg_match_all
      $modifiers = str_replace('g', '',$modifiers);

      $pattern = mb_substr($pattern, 0, mb_strrpos($pattern, $delimiter)) . $modifiers;
      
      if ($flags === NULL) $flags = PREG_SET_ORDER; // default to PREG_SET_ORDER
      $ret = preg_match_all($pattern, $subject, $matches, $flags, $offset);
    } else {
      $ret = preg_match($pattern, $subject, $matches, $flags, $offset);
    }

    if ($ret === FALSE) {
      throw new Exception('Pattern Syntax Error: '.$pattern);
    }

    return $ret;
  }

  /**
   * Führt eine Ersetzung anhand eines regulären Ausdruckes durch 
   * 
   * Wrapper für die PHP-Funktion preg_replace. Modifier für UTF-8 wird automatisch gesetzt.
   * @param string $subject Der String, in dem ersetzt werden wird
   * @param mixed $pattern Der reguläre Ausdruck (string oder array aus strings)
   * @param mixed $replace Ersetzungen (string oder array aus strings)
   * @param int $limit Optionale maximale Ersetzungsanzahl (-1 == No limit)
   * @param int $count Wird mit der Anzahl der Ersetzungsaktionen befüllt werden
   * @return string|array
   * @see PHP::preg_replace
   */
  public static function replace ($subject, $pattern, $replace, $limit = -1, &$count = NULL) {
    $pattern = self::set_u_modifier($pattern, TRUE);
    return (preg_replace($pattern, $replace, $subject, $limit, $count));
  }


  /**
   * So wie replace allerdings mit einer Callback Funktion mit einem Parameter
   * 
   * Callback bekommt als Parameter1 den Array des Matches von $pattern welches ersetzt werden soll
   * Der Rückgabestring wird dann in $subject ersetzt. 
   * <code>
   * function callback(Array $matches) {
   *  // as usual: $matches[0] is the complete match
   *  // $matches[1] the match for the first subpattern
   *  // enclosed in '(...)' and so on
   *  return $matches[1].($matches[2]+1);
   * }
   * </code>
   * @see PHP::preg_replace_callback
   */
  public static function replace_callback ($subject, $pattern, $callback, $limit = -1) {
    return preg_replace_callback(self::set_u_modifier($pattern, TRUE), $callback, $subject, $limit);
  }


  /**
   * Fügt einem Regex-Pattern den u-Modifier hinzu oder entfernt ihn
   * 
   * Gibt das Regex-Pattern mit u modifier zurück oder ohne u modifier zurück
   * @param string $pattern Das Regex-Pattern
   * @param bool $add wenn true wird der modifier hinzugefügt, ansonsten entfernt
   * @return string 
   */
  protected static function set_u_modifier($pattern, $add = TRUE) {
    /* aufsplitten */
    $delimiter = mb_substr($pattern, 0, 1);
    $modifiers = mb_substr($pattern, mb_strrpos($pattern, $delimiter));
  
    if ($add) {
      if (mb_strpos ($modifiers, 'u') === FALSE)
        $pattern .= "u"; // modifier hinzufügen, da er nicht existiert
    } else {


      if (mb_strpos($modifiers, 'u') !== FALSE) {
        $modifiers = str_replace('u', '',$modifiers);
        $pattern = mb_substr($pattern, 0, mb_strrpos($pattern, $delimiter)+1) . $modifiers;
      }
    }
    
    return $pattern;
  }
}

?>