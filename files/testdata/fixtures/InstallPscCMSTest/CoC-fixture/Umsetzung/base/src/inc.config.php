<?php
$GLOBALS['conf']['version'] = '0.1-Beta';

$GLOBALS['conf']['db']['default']['host'] = 'localhost';
$GLOBALS['conf']['db']['default']['user'] = $project->getLowerName();
$GLOBALS['conf']['db']['default']['password'] = 'nHLa4nde5xcw7cFv';
$GLOBALS['conf']['db']['default']['database'] = $project->getLowerName();
$GLOBALS['conf']['db']['default']['port'] = NULL;
$GLOBALS['conf']['db']['default']['charset'] = 'utf8';

$GLOBALS['conf']['db']['tests'] = $GLOBALS['conf']['db']['default'];
$GLOBALS['conf']['db']['tests']['database'] = $project->getLowerName().'_tests';

$GLOBALS['conf']['jquery']['version'] = '1.6.2';
$GLOBALS['conf']['project']['title'] = 'Psc - CMS - '.$project->getName();

$GLOBALS['conf']['doctrine']['entities']['names'] = array(); // lowercasename => CamelCaseName
?>