<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Psc\CMS\Project;

class CommitSVNTask extends \Psc\SimpleObject implements Task {

  /**
   * @var Psc\CMS\Project
   */
  protected $sourceProject;
  
  public function __construct(Project $sourceProject) {
    $this->sourceProject = $sourceProject;
    //$this->gitDir = new \Webforge\Common\System\Dir('D:\www\deployments.git\\'.$targetProject->getName().'\\');
    $this->message = NULL;
  }
  
  public function run() {
    $this->commit();
  }
  
  protected function commit() {
    $target = $this->sourceProject->getRoot(); // Umsetzungs-Ordner mainly
    
    $exec = function ($cmd) use ($target) {
      //print('  exec: '.$cmd);
      system('cd '.$target.' && '.$cmd);
    };
    
    //$exec('svn add *');
    $exec(sprintf('svn commit --message %s', escapeshellarg($this->message ?: 'automated deploy')));
    //$exec('git push origin master:master');  
  }
  
  protected function copyAll() {
    $base = $this->targetProject->getBase();
    
    $base->copy($this->gitDir->sub('base/')->create());
  }
  
  /**
   * @param string $message
   * @chainable
   */
  public function setMessage($message) {
    $this->message = $message;
    return $this;
  }

  /**
   * @return string
   */
  public function getMessage() {
    return $this->message;
  }


}
?>