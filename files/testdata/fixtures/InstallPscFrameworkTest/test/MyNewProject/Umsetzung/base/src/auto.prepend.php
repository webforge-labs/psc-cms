<?php

namespace Psc;

require_once 'psc-cms.phar.gz';

PSC::getProjectsFactory()->getProject('MyNewProject')->bootstrap()
  ->getModule('Doctrine')->bootstrap()
;

?>