<?php

namespace Psc;
use Psc\Boot\BootLoader;

require 'package.boot.php';
$bootLoader = BootLoader::createRelative('../bin/');
$bootLoader->setHostConfigFile(getenv('PSC_CMS'));
$bootLoader->init();

// hack host-config
$bootLoader->getHostConfig()->set(array('projects', 'test-project', 'root'),
  $bootLoader->getPath('../../', BootLoader::RELATIVE | BootLoader::VALIDATE)
);

$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('doctrine'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('phpexcel'));
$bootLoader->getAutoLoader()->addPhar($bootLoader->getPhar('imagine'));


PSC::getProjectsFactory()
  ->getProject('test-project')
    
    ->setLibsPath($bootLoader->getPharBinaries())
    ->bootstrap()
    ->getModule('Doctrine')->bootstrap()->getProject()
    ->getModule('PHPExcel')->bootstrap()->getProject()
    ->getModule('Imagine')->bootstrap()->getProject()

  ->getConfiguration()->set(array('url','base'), 'testproject.ps-webforge.com')
;

?>