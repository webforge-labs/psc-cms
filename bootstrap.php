<?php

use Psc\PSC;
use Psc\CMS\Container;

$ds = DIRECTORY_SEPARATOR;

// autoload project dependencies and self autoloading for the library
$vendor = __DIR__.$ds.'vendor'.$ds;

// are we loaded as dependency?
if (!file_exists($vendor.'autoload.php')) {
  $vendor = __DIR__ . '/../../';
}

require $vendor.'autoload.php';

$container = new Container(__DIR__);
$container->init();
$container->initErrorHandlers();

$container->bootstrapModule('Doctrine');
$container->bootstrapModule('PHPExcel');
$container->bootstrapModuleIfExists('Imagine');

$GLOBALS['env']['container'] = $container;