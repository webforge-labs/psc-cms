<?php

namespace Psc;

require_once 'psc-cms.phar.gz';

PSC::getProjectsFactory()->getProject('CoC')->bootstrap()
  ->getModule('Doctrine')->bootstrap()
;

?>