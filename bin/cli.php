<?php

namespace Psc;
use Psc\Boot\BootLoader;

require 'package.boot.php';
$bootLoader = new BootLoader(__DIR__);
$bootLoader->init();

$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('symfony'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('doctrine'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('hitch'));
//$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('phpword'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('imagine'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('swift'));
//$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('phpexcel'));

$bootLoader
  ->setProjectPath('psc-cms','tests', './tests/')
  ->setProjectPath('psc-cms','src', './lib/')
  ->setProjectPath('psc-cms','files', './files/')
  ->setProjectPath('psc-cms',PSC::PATH_HTDOCS, './files/htdocs')
  ->setProjectPath('psc-cms',PSC::PATH_TESTDATA, './files/testdata/')
;

require $bootLoader->getPath('../lib/', BootLoader::RELATIVE).'bootstrap.php';

PSC::getProject()->setTests(TRUE);

$console = new \Psc\System\Console\Console();
exit($console->run());
?>