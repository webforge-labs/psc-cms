<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Psc\CMS\Project;

/**
 * Kopiert das erstellte Deployment in das git-Deployment ($this->gitDir) - directory und pushed das Paket nach remote $this->remote
 *
 * (git push $this->remote)
 */
class CommitGitDeploymentTask extends \Psc\SimpleObject implements Task {
  
  /**
   * @var Psc\CMS\Project
   */
  protected $targetProject;
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $gitDir;
  
  /**
   * Das remote welches zum Pushen genutzt werdne soll
   * @var string default: origin
   */
  protected $remote;
  
  public function __construct(Project $targetProject) {
    $this->targetProject = $targetProject;
    $this->gitDir = new \Webforge\Common\System\Dir('D:\www\\deployments.git\\'.$targetProject->getName().'\\');
    $this->message = NULL;
    $this->remote = 'origin';
  }
  
  public function run() {
    if (!$this->gitDir->exists()) {
      return 1;
    }
    
    $this->copyAll();
    $this->commit();
  }

  protected function copyAll() {
    $root = $this->targetProject->getRoot();
    
    // leeren: alle in root außer .git
    foreach ($this->gitDir->getContents(NULL, array('/^\.git$/'), NULL, FALSE) as $dirOrFile) {
      $dirOrFile->delete();
    }
    
    $root->copy($this->gitDir);
  }
  
  
  protected function commit() {
    $target = $this->gitDir;
    
    $exec = function ($cmd) use ($target) {
      //print('  exec: '.$cmd);
      system('cd '.$target.' && '.$cmd);
    };
    
    //print('  add/commit/push to master');
    $exec('git add -A');
    $exec(sprintf('git commit -m"%s"', $this->message ?: 'automated deploy'));
    $exec('git push '.$this->remote.' master:master');  
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
  
  /**
   * @param Webforge\Common\System\Dir $gitDir
   * @chainable
   */
  public function setGitDir(\Webforge\Common\System\Dir $gitDir) {
    $this->gitDir = $gitDir;
    return $this;
  }

  /**
   * @return Webforge\Common\System\Dir
   */
  public function getGitDir() {
    return $this->gitDir;
  }
  
  /**
   * @param string $remote
   * @chainable
   */
  public function setRemote($remote) {
    $this->remote = $remote;
    return $this;
  }

  /**
   * @return string
   */
  public function getRemote() {
    return $this->remote;
  }
}
?>