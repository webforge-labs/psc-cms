<?php

/**
 * 
 * 
 */
class PscCMS {


  public static function menu(Array $menuData) {
    return FluidGrid_Grid::factory(
	    FluidGrid_Box::factory('Section Menu',
				   new FluidGrid_Menu($menuData)
		    )->addClass('menu')
	    );
  }

}

?>