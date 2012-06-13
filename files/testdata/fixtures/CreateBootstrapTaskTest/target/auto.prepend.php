<?php

namespace Psc;

require __DIR__.'psc-cms.phar.gz';

PSC::getProjectsFactory()->getProject('test-project')->bootstrap()
  ->getModule('Doctrine')->bootstrap()->getProject()
  ->getModule('PHPExcel')->bootstrap()->getProject()
  ->getModule('Imagine')->bootstrap()->getProject()

;

?>