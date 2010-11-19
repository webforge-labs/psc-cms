<?php

/**
 * Die Row ist ein Bestandteil eines Containers
 * 
 * In jeder Zeile sind mehrere Grids die Als Breiten-Summe die größe des beeinhaltenden Containers sind
 */
class FluidGrid_Row extends FluidGrid_Collection implements FluidGrid_IGridContainer {

  protected $modelClass = 'FluidGrid_Grid';

  /**
   * 
   * Wir sind ein virtueller Container, deshalb rücken wir die Elemente in uns nicht ein
   */
  protected $indentItems = 0;

  /**
   * Der Container in dem die Row mit den Grids liegt
   * 
   * Der Container gibt die verfügbare Breite der Grids an
   * @var FluidGrid_Container
   */
  protected $container;

  /**
   * Die Berechneten (dynamischen) Breiten der Grids, die keine feste Größe haben
   * @var array
   */
  protected $calculatedWidths;
  
  public function setContainer(FluidGrid_Container $container) {
    $this->container = $container;
    
    $this->calculateWidths(); // dies macht den sum check
  }

  public function addItem(FluidGrid_Grid $grid) {
    parent::addItem($grid);
    $grid->setParent($this);
    
    return $this;
  }

  protected function calculateWidths() {
    if (!isset($this->container))
      throw new Exception('Es ist kein Container gesetzt, die Breiten können nicht berechnet werden');
    
    $cWidth = $this->container->getWidth();
    $usedWidth = 0;
    $nullWidths = array();
    foreach ($this->items as $key => $grid) {
      $w = $grid->getWidth();
      if ($w == NULL)
        $nullWidths[] = $key;
      else
        $usedWidth += $w;
    }

    if ($usedWidth > $cWidth)
      throw new Exception('Fehler: Die Zeile ist '.$usedWidth.' Grids breit der Container ist aber '.$cWidth.' breit. Die Grids sind zu breit.');
    
    if ($usedWidth < $cWidth && is_array($nullWidths)) {
      $grids = count($nullWidths);
      $avaibleWidth = $cWidth - $usedWidth;
      
      if ($avaibleWidth % $grids == 0)
        $this->calculatedWidths = array_combine($nullWidths,array_fill(0,$grids,(int) $avaibleWidth/$grids));
      else {
        $avgWidth = (int) ceil($avaibleWidth/$grids);
        $this->calculatedWidths = array_combine($nullWidths,array_merge(array_fill(0,$grids-1,$avgWidth),array($avaibleWidth - $avgWidth * ($grids-1))));
      }
    }
  }

  /**
   * Gibt die Größe eines Grids in der Zeile zurück
   * 
   * Hat das Grid keine feste Größe angegeben, wird die dynamisch errechnete zurückgegeben
   * @return int
   */
  public function getGridWidth(FluidGrid_Grid $grid) {
    $key = array_search($grid,$this->items,TRUE);
    if ($key === FALSE)
      throw new Exception('Grid in der betreffenden Zeile nicht gefunden.');
      
    if (isset($this->calculatedWidths[$key]))
      return $this->calculatedWidths[$key];
    else
      return $grid->getWidth();
  }

  public function __toString() {
    $this->calculateWidths();
    $content = $this->getContent(); // das sind alle items verbunden 
    $content .= $this->indent().'<div class="clear"></div>'.$this->lb(); // zeile abschließen
    
    return $content;
  }
}
?>