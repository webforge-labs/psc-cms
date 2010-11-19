<?php

class Errors_Core {

  const OK = 'ok';
  const WARNING = 'warning';
  const ERROR = 'error';

  public $jsonArray;

  public function __construct() {
    $this->jsonArray = array();
  }

  /**
   * 
   * @chainable
   * @see core_add_error()
   */
  public function add_error($msg, $type = self::ERROR) {
    self::core_add_error($this->jsonArray, $msg, $type);
    return $this;
  }

  /**
   * 
   * @see core_render()
   */
  public function __toString() {
    return self::core_render($this->jsonArray);
  }

  public function has_errors() {
    return count($this->jsonArray) > 0;
  }

  /**
   * 
   * @param string $msg die Nachricht des Fehlers
   * @param self::OK|self::WARNING|self::ERROR $type der Typ des Fehlers
   */
  public static function core_add_error(Array &$jsonArray, $msg, $type = self::ERROR) {
    if (!in_array($type,array(self::OK,self::ERROR,self::WARNING))) throw new Exception('unbekannter Typ: '.$type);

    $jsonArray[] = array('msg'=>$msg,'type'=>$type);
    return $jsonArray;
  }
  

  /**
   * Gibt Informationen für die Fehler die angezeigt werden sollen, für das JavaScript zurück
   * 
   * @param array $jsonArray der mit addError erzeugte Array
   * @return string
   */
  public static function core_render(Array $jsonArray) {
    $html = '';
    if (count($jsonArray) > 0) {
      $html .= '<div id="errors-json" style="display:none">'."\n";
      $html .= json_encode($jsonArray);
      $html .= '</div>'."\n";
    }

    return $html;
  }

}
?>