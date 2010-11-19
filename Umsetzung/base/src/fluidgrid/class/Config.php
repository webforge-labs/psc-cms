<?php
/**
 * 
 * Alle nicht statischen Variablen dieser Klasse können mit:
 * $GLOBALS['conf']['FluidGrid'][<variablenName>] gesetzt werden (bevor diese Klasse das erste mal instantiiert wird (Bootstrapper)
 */
class FluidGrid_Config extends FluidGrid_Object {
  protected static $instance;

  /**
   * 
   * @var string der gesamte Pfad zu den FluidGrid PHP Dateien
   */
  protected $path = FLUIDGRID_PATH;
  
  /**
   * Der Array der Variablen die aus den GLOBALS Variablen gelesen werden
   * @var array
   */
  protected $globalVars = array();

  protected function __construct() {
    $cvars = array();

    if (isset($GLOBALS['conf']['FluidGrid'])) {
      $this->globalVars = (array) $GLOBALS['conf']['FluidGrid'];
    }

    foreach ($cvars as $cvar) {
      if (is_string($cvar) && isset($this->globalVars[$cvar]))
        $this->$cvar = $this->globalVars[$cvar];
    }
  }

  /**
   * 
   * @return FluidGrid_Config
   */
  public static function instance() {
    if (!isset($this->instance))
      $this->instance = new FluidGrid_Config();
    
    return $this->instance;
  }
}

?>