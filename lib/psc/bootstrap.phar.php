<?php

namespace Psc;

/* bootstrap innerhalb des PHARs */

$class2path = /*%%CLASS2PATH%%*/;

/* Verbose? */
if (defined('PSC_CMS_PHAR_VERBOSE') && PSC_CMS_PHAR_VERBOSE == TRUE) {
  $v = function ($msg) {
    print '[Phar] '.$msg."\n";
  };
  
  /* hier muss man höllisch aufpassen beim kopieren,
     aber alles andere ist käse (später mal: compilieren mit DEBUG_FLAG)
  */
  $v('Beginne Bootstrap (bootstrap.phar.php) '.__DIR__);

  $v('execute: pre-boot');
  /*%%PRE-BOOT%%*/
  
  /* AutoLoader */
  $v('require "'.'Psc'.DIRECTORY_SEPARATOR.'PharAutoLoader.php'.'" include_path="'.get_include_path().'"');
  
  require_once 'Psc'.DIRECTORY_SEPARATOR.'PharAutoLoader.php';
  require_once 'Psc'.DIRECTORY_SEPARATOR.'PharVerboseAutoLoader.php';

  $v('instance VerboseAutoLoader');
  $phal = new PharVerboseAutoLoader();
  $v('setPaths: '.count($class2path).' files');
  $phal->setPaths(array_map(function ($path) {
                         return \Phar::running(TRUE).$path;
                       }, $class2path));
  $v('init');
  $phal->init();
  
  $v('Versuche Host-Config zu laden');
  
  if (!isset($conf)) { // erlaube das überschrieben in pre-boot
    try {
      $v('require: '.PSC::getRoot().'host-config.php');
      require_once PSC::getRoot().'host-config.php';
    } catch (MissingEnvironmentVariableException $e) {
      $v('FEHLER: '.$e->getMessage());
    }
      
    if (!isset($conf)) $conf = array();
  } else {
    $v('skippe require host-config da conf schon geladen ist');
  }
  
  $v('$conf ist: '.print_r($conf,true));

  $v('setzte ProjectsFactory in PSC');
  PSC::setProjectsFactory(new CMS\ProjectsFactory(new CMS\Configuration($conf)));
  PSC::setAutoLoader($phal);
  
  $v('execute: post-boot');
  /*%%POST-BOOT%%*/
  
  $v('cleanup und finish..');
  unset($v);
} else {
  
  /*%%PRE-BOOT%%*/
  
  /* AutoLoader */
  require_once 'Psc'.DIRECTORY_SEPARATOR.'PharAutoLoader.php';
  $phal = new PharAutoLoader();
  $phal->setPaths(array_map(function ($path) {
                         return \Phar::running(TRUE).$path;
                       }, $class2path));
  $phal->init();
  
  if (!isset($conf)) { // erlaube das überschreiben in pre-boot
    try {
      require_once PSC::getRoot().'host-config.php';
    } catch (MissingEnvironmentVariableException $e) {
      
    }
    if (!isset($conf)) $conf = array();
  }

  PSC::setProjectsFactory(new CMS\ProjectsFactory(new CMS\Configuration($conf)));
  PSC::setAutoLoader($phal);
  
  /*%%POST-BOOT%%*/
}
/* bootstrap ende */
?>