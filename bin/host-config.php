<?php
/* General */
$conf['host'] = 'travis-ci';
$conf['root'] = __DIR__.'/../lib/';
$conf['production'] = TRUE;
$conf['uagent-key'] = NULL;
$conf['cms']['user'] = 'travis@ps-webforge.com';
$conf['cms']['password'] = 'dUHnXlXN5ZtWAQ6FCDeA';

/* System Datenbank (für dumps per Console für die Tests) */
$conf['system']['dbm']['user'] = 'root';
$conf['system']['dbm']['password'] = '';
$conf['system']['dbm']['host'] = 'localhost';

/* Host Pattern für automatische baseURLs */
$conf['url']['hostPattern'] = '%s.travis.ps-webforge.net';

/* Project Paths */
$conf['projects']['root'] = getenv('$HOME').'/builds/pscheit/';
$conf['projects']['psc-cms'] = getenv('$HOME').'/builds/pscheit/psc-cms/';

/* Environment */
$conf['defaults']['system']['timezone'] = 'Europe/Berlin';
$conf['defaults']['system']['chmod'] = 0644;
$conf['defaults']['i18n']['language'] = 'de';

/* Executables */
//$conf['defaults']['executables']['rar'] = 'C:\Program Files\WinRAR\rar.exe';

/* Mail */
$conf['defaults']['mail']['smtp']['user'] = NULL;
$conf['defaults']['mail']['smtp']['password'] = NULL;
$conf['defaults']['debug']['errorRecipient']['mail'] = NULL;

/* CMS / HTTP */
$conf['defaults']['js']['url'] = '/js/';
?>