<?php

if (!defined("PSC_PATH")) define("PSC_PATH", str_replace('\\','/',dirname(__FILE__).DIRECTORY_SEPARATOR));
if (!defined("SRC_PATH")) define("SRC_PATH", str_replace('\\','/',realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR));

require_once PSC_PATH.'class'.DIRECTORY_SEPARATOR.'AutoLoader.php';

/* load autoloader to spl auto register */
AutoLoader::register();

?>