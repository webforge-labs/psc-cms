<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'auto.prepend.php';

$console = new \MyNewProject\ProjectConsole($project = \Psc\PSC::getProject(), $project->getModule('Doctrine'));
$console->run();

?>