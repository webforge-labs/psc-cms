<?php

namespace Psc;

require __DIR__.DIRECTORY_SEPARATOR.'psc-cms.phar.gz';

PSC::getProject()->setTests(TRUE);

$console = new \Psc\System\Console\Console();
$console->run();
?>