<?php

namespace Psc;
use Psc\Boot\BootLoader;

require 'package.boot.php';
$bootLoader = new BootLoader(__DIR__);
$bootLoader->init();

$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('symfony'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('doctrine'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('hitch'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('phpword'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('imagine'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('swift'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('phpexcel'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('gedmo'));

$bootLoader
  ->setProjectPath('psc-cms','tests', './tests/')
  ->setProjectPath('psc-cms','src', './lib/')
  ->setProjectPath('psc-cms','files', './files/')
  ->setProjectPath('psc-cms',PSC::PATH_TPL, './files/tpl')
  ->setProjectPath('psc-cms',PSC::PATH_HTDOCS, './files/htdocs')
  ->setProjectPath('psc-cms',PSC::PATH_TESTDATA, './files/testdata/')
;

PSC::getProjectsFactory()->getProject('psc-cms')->setLoadedWithPhar(TRUE)->bootstrap()
  ->getModule('Doctrine')->bootstrap()->getProject()
  ->getModule('Gedmo')->bootstrap(\Psc\Gedmo\Module::BOOT_NAVIGATION)->getProject()
  ->getModule('PHPExcel')->bootstrap()->getProject()
  ->getModule('PHPWord')->bootstrap()->getProject()
  ->getModule('Hitch')->bootstrap()->getProject()
  ->getModule('Imagine')->bootstrap()->getProject()
;

PSC::getProject()->setTests(TRUE);
ini_set('memory_limit', '-1');
?>