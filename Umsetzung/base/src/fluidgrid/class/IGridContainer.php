<?php

interface FluidGrid_IGridContainer {


  /**
   * Gibt die Größe eines Grids in der Zeile zurück
   * 
   * Hat das Grid keine feste Größe angegeben, wird die dynamisch errechnete zurückgegeben
   * @return int
   */
  public function getGridWidth(FluidGrid_Grid $grid);
}

?>