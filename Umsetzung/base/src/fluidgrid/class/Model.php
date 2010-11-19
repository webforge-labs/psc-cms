<?php

/**
 * Model ist ein reale-Welt Objekt (z.b. ein HTML Element)
 * abstrakte Elemente wie z.b. ein FluidGrid_Config sind keine Models
 * 
 * ** Konstrukor:
 * Für alle wichtigen Klassen sollte es einen statische factory() Methode geben
 * diese statische Methode sollte dieselbe sein wie bei fg::$className() 
 * d.h.: construktor an den 3 Stellen verändern
 */
class FluidGrid_Model extends FluidGrid_Object {

  /**
   * 
   * @var FluidGrid_Object
   */
  protected $content;

  /**
   * Das Level der Einrückung des Objektes
   * @var int 0 basierend
   */
  protected $level = 0;


  public function __construct(FluidGrid_Object $content = NULL) {
    $this->setContent($content);
  }
  

  public function __toString() {
    try {
      return (string) $this->getContent();
    } catch (Exception $e) {
      print $e;
    }
  }

  /**
   * LineBreak
   * 
   * Überall verwenden statt "\n"
   */
  public function lb() {
    return "\n";
  }

  public function indent() {
    //return '-'.($this->level*2).'-';
    return str_repeat(' ',$this->level*2);
  }
}

?>