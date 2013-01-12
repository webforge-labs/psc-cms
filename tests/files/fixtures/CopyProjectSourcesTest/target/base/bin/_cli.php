<?php

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__.$ds.'..'.$ds.'src'.$ds.'auto.prepend.php';

$console = new \tiptoi\ProjectConsole($project = \Psc\PSC::getProject(), $project->getModule('Doctrine'));
$console->run();

?>