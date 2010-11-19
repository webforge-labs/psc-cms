<?php

if (!defined("FLUIDGRID_PATH")) define("FLUIDGRID_PATH", dirname(__FILE__));

require_once FLUIDGRID_PATH.DIRECTORY_SEPARATOR.'AutoLoader.php';

/* load autoloader to spl auto register */
FluidGrid_Autoloader::register();

/* shortname Klasse */
require_once FLUIDGRID_PATH.DIRECTORY_SEPARATOR.'class.fg.php';

?>