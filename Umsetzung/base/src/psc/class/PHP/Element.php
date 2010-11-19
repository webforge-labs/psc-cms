<?php

class PHPElement extends Object {

  /**
   * 
   * @var int die erste Zeile ist 1
   */  
  protected $line;

  /**
   * Wenn das Element nicht einzeilig ist, kann hier die letzte Zeile angegeben sein
   */
  protected $lastLine;
  
  protected $value;

  protected $type;

  /**
   * 
   * Der aller erste (im Sourcecode oben stehende), steht am Anfang des Arrays
   * @var PHPDocComment[]
   */
  protected $docComments;

  /**
   * 
   * @param array $token ein Token wie er aus dem PHP Lexer kommt
   */
  public function __construct(Array $token) {
    $this->line = (int) $token['line'];
    $this->value = $token['value'];
    $this->type = $token['type'];

    $this->docComments = array();
  }

  
  /**
   * Gibt den obersten DocComment des Elements zurück
   * 
   * @return PHPDocComment|NULL
   */
  public function getFirstDocComment() {
    if (count($this->docComments) > 0) {
      return $this->docComments[0];
    }
  }

  /**
   * 
   * 
   */
  public function getFirstLine() {
    return $this->line;
  }
  /**
   * 
   * wenn lastLine nicht gesetzt ist gibt diese funktion line aus
   */
  public function getLastLine() {
    if (isset($this->lastLine))
      return $this->lastLine;
    else
      return $this->line;
  }
}

?>