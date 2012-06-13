<?php
/* Travis-config */

$GLOBALS['conf']['js']['url'] = '/js/';
$GLOBALS['conf']['jquery']['version'] = '1.7.1';
$GLOBALS['conf']['psc-cms']['version'] = '0.2-DEV';

$GLOBALS['conf']['db']['default']['host'] = 'localhost';
$GLOBALS['conf']['db']['default']['user'] = 'root';
$GLOBALS['conf']['db']['default']['password'] = '';
$GLOBALS['conf']['db']['default']['database'] = 'psc-cms';
$GLOBALS['conf']['db']['default']['port'] = NULL;
$GLOBALS['conf']['db']['default']['charset'] = 'utf8';

if (getenv('PEGASUS_CI')) {
  $GLOBALS['conf']['db']['default']['user'] = 'psc-cms';
  $GLOBALS['conf']['db']['default']['password'] = 'L6W2vHEbKLjeUEr2';
}

if (getenv('TRAVIS_CI') === 'true') {
  $GLOBALS['conf']['db']['default']['user'] = 'root';
  $GLOBALS['conf']['db']['default']['password'] = '';
}

$GLOBALS['conf']['db']['tests'] = $GLOBALS['conf']['db']['default'];
$GLOBALS['conf']['db']['tests']['database'] = 'psc-cms_tests';

$GLOBALS['conf']['doctrine']['entities']['namespace'] = 'Entities';
$GLOBALS['conf']['doctrine']['entities']['names'] = array();

$GLOBALS['conf']['project']['title'] = 'Psc - CMS';

$GLOBALS['conf']['fixture']['config']['variable'] = 'valueinpsc-cms';

$GLOBALS['conf']['ContactForm']['recipient'] = 'info@ps-webforge.com';
$GLOBALS['conf']['mail']['from'] = $GLOBALS['conf']['mail']['envelope'] = 'info@ps-webforge.com';
?>