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

$doctrine = $container->bootstrapModule('Doctrine');
$doctrine->registerEntityClassesMetadataDriver()->getEntityClassesMetadataDriver()
  ->addClass('Psc\Doctrine\TestEntities\Tag')
  ->addClass('Psc\Doctrine\TestEntities\Article')
  ->addClass('Psc\Doctrine\TestEntities\Category')
  ->addClass('Psc\Doctrine\TestEntities\Person')
;  

$container->bootstrapModule('PHPExcel');
$container->bootstrapModuleIfExists('Imagine');

$GLOBALS['env']['container'] = $container;