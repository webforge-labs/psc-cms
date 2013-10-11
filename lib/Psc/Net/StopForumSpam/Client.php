<?php

namespace Psc\Net\StopForumSpam;

use Webforge\Common\System\File;
use Psc\System\Console\Process;
use Psc\JS\JSONConverter;
use Webforge\Common\System\ExecutableFinder;

class Client {
  
  /**
   * die bin-datei zur CLI von StopForumSpam
   *
   * @var Webforge\Common\System\File
   */
  protected $daemon;
  
  public function __construct(File $daemon) {
    $this->daemon = $daemon;
  }
  
  public static function create(ExecutableFinder $finder) {
    $daemon = $finder->getExecutable('stop-forum-spam');
    
    return new static($daemon);
  }
  
  /**
   * @returns array $result
   */
  public function queryByEmail($email) {
    $process = Process::build($this->daemon, array('query-email',$email))->end();
    
    if (($exitCode = $process->run()) !== 0) {
      throw new \RuntimeException('Cannot Query Daemon: exit('.$exitCode.') '.$process->getErrorOutput().' '.$process->getOutput().' Running '.$process->getCommandLine());
    }
    
    $json = $process->getOutput();
    
    $result = JSONConverter::create()->parse($json);
    
    return $result;
  }
}
