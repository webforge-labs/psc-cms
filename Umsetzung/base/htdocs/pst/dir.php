<?php
require_once 'psc/bootstrap.php';
require_once SRC_PATH.'psc/vendor/FirePHPCore-0.3.1/lib/FirePHPCore/FirePHP.class.php';

$firephp = FirePHP::getInstance(true);

$var = array('i'=>10, 'j'=>20);
 
$dir = new Dir(SRC_PATH);
$dir->ignores[] = '.svn';

foreach ($dir->getFiles() as $file) {
  print $file."<br />\n";
}


?>