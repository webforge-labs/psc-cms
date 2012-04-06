<?php

namespace Psc;

// damit die module sich an den pharautoloader hängen
PSC::getProjectsFactory()->getProject('psc-cms')->bootstrap()
  ->getModule('Doctrine')->bootstrap()->getProject()
  ->getModule('PHPExcel')->bootstrap()->getProject()
  ->getModule('Hitch')->bootstrap()->getProject()
  ->getModule('Imagine')->bootstrap()->getProject()
;

?>