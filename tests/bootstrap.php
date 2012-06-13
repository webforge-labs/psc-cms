<?php

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__.$ds.'..'.$ds.'..'.$ds.'auto.prepend.php';

ini_set('memory_limit', '-1');

\Psc\PSC::getProject()->setTests(TRUE);

?>