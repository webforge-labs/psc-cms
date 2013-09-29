<?php

namespace Psc\System\Console;

use Webforge\Common\System\File;

class Process extends \Symfony\Component\Process\Process {
  
  const UNIX = ProcessBuilder::UNIX;
  const WINDOWS = ProcessBuilder::WINDOWS;
  
  /**
   * Use the Builder to create a environment process
   */
  public function __construct($commandline, $cwd = null, array $env = null, $stdin = null, $timeout = 60, array $options = array()) {    
    $env = array_replace(array('USERPROFILE'=>getenv('HOME')), (array) $env);
    $inherits = array('PATH','SystemRoot','LOCALAPPDATA','SystemDrive','SSH_AUTH_SOCK','CommonProgramFiles',
                      'APPDATA','COMPUTERNAME','TEMP','TMP','USERNAME',
                      'PHPRC', 'PHP_PEAR_BIN_DIR', 'PHP_PEAR_PHP_BIN', 'PSC_CMS',
                      'XDEBUG_CONFIG', 'WEBFORGE'
                     );
    foreach ($inherits as $inherit) {
      $env[$inherit] = getenv($inherit);
    }
    
    parent::__construct($commandline, $cwd, $env, $stdin, $timeout, $options);
  }
  
  /**
   */
  public static function build($bin, Array $cmdArgs = array(), Array $cmdOptions = array(), $escapeFor = NULL) {
    return ProcessBuilder::create($bin, $cmdArgs, $cmdOptions, $escapeFor);
  }
}
