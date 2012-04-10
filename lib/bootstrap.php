<?php

namespace Psc;

PSC::getProjectsFactory()->getProject('psc-cms')->setLoadedWithPhar(TRUE)->bootstrap()
  ->getModule('Doctrine')->bootstrap()->getProject()
  ->getModule('PHPExcel')->bootstrap()->getProject()
  ->getModule('Hitch')->bootstrap()->getProject()
  ->getModule('Imagine')->bootstrap()->getProject()
;

?>