<?php

$conf['js']['url'] = '/js/';
$conf['jquery']['version'] = '1.7.1';
$conf['psc-cms']['version'] = '1.0-DEV';
$conf['languages'] = array('de');

$conf['db']['default']['host'] = 'localhost';
$conf['db']['default']['user'] = 'psc-cms';
$conf['db']['default']['password'] = 'L6W2vHEbKLjeUEr2';
$conf['db']['default']['database'] = 'psc-cms';
$conf['db']['default']['port'] = NULL;
$conf['db']['default']['charset'] = 'utf8';
$conf['db']['tests'] = $conf['db']['default'];
$conf['db']['tests']['database'] = 'psc-cms_tests';

$conf['doctrine']['entities']['names'] = array();

$conf['project']['cmsOnly'] = TRUE;
$conf['project']['title'] = 'Psc - CMS';

$conf['fixture']['config']['variable'] = 'valueinpsc-cms';

$conf['ContactForm']['recipient'] = 'info@ps-webforge.com';
$conf['mail']['from'] = $conf['mail']['envelope'] = 'info@ps-webforge.com';
?>