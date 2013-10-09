<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Webforge\Framework\Project;
use Psc\System\Logger;
use Psc\System\BufferLogger;
use Psc\Data\ArrayCollection;
use Psc\Code\Code;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GClass;
use Webforge\Framework\Container AS WebforgeContainer;

/**
 * Mit output Interface verbinden, dass man auch warnings in den tasks machen kann
 */
class Deployer extends \Psc\System\LoggerObject {
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $deploymentsRoot;
  
  /**
   * @var Webforge\Framework\Project
   */
  protected $project;
  
  /**
   * @var Collection<Psc\System\Deploy\Task>
   */
  protected $tasks;
  
  /**
   * Das Zielverzeichnis in das Deployed wird
   *
   * dies ist ein Subverzeichnis von deploymentsRoot
   * @var Webforge\Common\System\Dir
   */
  protected $target;
  
  /**
   * Das Projekt mit den Pfaden im target
   * @var Webforge\Framework\Project
   */
  protected $targetProject;
  
  /**
   * @var Webforge\Framework\Container
   */
  protected $webforgeContainer;
  
  /**
   * @var der Kurzname des Servers auf dem deployed wird unbedingt setzen für apache
   */
  protected $hostName;
  
  /**
   * sowas wie bla.ps-webforge.com
   */
  protected $baseUrl;
  
  /**
   * @var bool
   */
  protected $init = FALSE;
  
  /**
   * Kann z.b. Staging sein
   *
   * dann ist der Target ordner project->name.staging
   */
  protected $variant;
  
  public function __construct(Dir $deploymentsRoot, WebforgeContainer $container, Project $project, $variant = NULL, Logger $logger = NULL) {
    $this->setDeploymentsRoot($deploymentsRoot);
    $this->setProject($project);
    $this->variant = $variant != 'normal' ? $variant : NULL;
    $this->webforgeContainer = $container;
    
    $this->setLogger($logger ?: new BufferLogger);
    $this->tasks = new ArrayCollection();
  }
  
  public function init() {
    if (!$this->init) {
      $this->init = true;
      $this->target = $this->deploymentsRoot->sub($this->project->getName().($this->variant ? '.'.$this->variant : NULL).'/');
      $this->targetProject = clone $this->project;
      $this->targetProject->setRoot($this->getTarget());
    }
    
    return $this;
  }
  
  public function deploy() {
    $this->init();
    
    $this->logf(
      'Starting Deployment [%s%s] to %s',
      $this->project->getName(),
      $this->variant ? '.'.$this->variant : NULL,
      $this->target
    );
    
    foreach ($this->tasks as $task) {
      $this->logf('** Task: '.Code::getClassName(Code::getClass($task)));
      $task->run();
    }
    $this->logf('finished Deployment [%s%s]', $this->project->getName(), ($this->variant ? '.'.$this->variant : NULL));
  }
  
  /**
   * wird automatisch mit dependencies erstellt
   * @param string $name der Name des Tasks ohne Namespace und "Task" dahinter
   */
  public function createTask($name) {
    $this->init();
    $class = Code::expandNamespace(\Webforge\Common\String::expand($name, 'Task'), 'Psc\System\Deploy');
    
    $gClass = GClass::factory($class);
    $params = array();
    if ($gClass->hasMethod('__construct')) {
      $constructor = $gClass->getMethod('__construct');
      foreach ($constructor->getParameters() as $parameter) {
        $params[] = $this->resolveTaskDependency($parameter, $gClass);
      }
    }
    
    return $gClass->newInstance($params);
  }
  
  protected function resolveTaskDependency(GParameter $parameter, GClass $gClass) {
    // zuerst nach Name
    switch ($parameter->getName()) {
      case 'targetProject':
      case 'sourceProject':
      case 'target':
      case 'hostName':
      case 'baseUrl':
      case 'changelog':
      case 'webforgeContainer':
        $getter = 'get'.ucfirst($parameter->getName());
        return $this->$getter();
      
      default:
        if (!$parameter->isOptional())
          throw new \Psc\Exception('Dependency in '.$gClass->getFQN().' '.$parameter->getName().' kann nicht gelöst werden.');
        else
          return $parameter->getDefault();
    }
  }
  
  public function addTask(Task $task) {
    $this->tasks->add($task);
    return $this;
  }
  
  /**
   * @param Webforge\Common\System\Dir $deploymentsRoot
   */
  public function setDeploymentsRoot(Dir $deploymentsRoot) {
    $this->deploymentsRoot = $deploymentsRoot;
    return $this;
  }
  
  /**
   * @return Webforge\Common\System\Dir
   */
  public function getDeploymentsRoot() {
    return $this->deploymentsRoot;
  }
  
  /**
   * @param Webforge\Framework\Project $project
   */
  public function setProject(Project $project) {
    $this->project = $project;
    return $this;
  }
  
  /**
   * @return Webforge\Framwork\Project
   */
  public function getProject() {
    return $this->project;
  }
  
  /**
   * @param Psc\Data\ArrayCollection $tasks
   * @chainable
   */
  public function setTasks(ArrayCollection $tasks) {
    $this->tasks = $tasks;
    return $this;
  }
  
  /**
   * @return Psc\Data\ArrayCollection
   */
  public function getTasks() {
    return $this->tasks;
  }
  
  /**
   * @param Webforge\Common\System\Dir $target
   */
  public function setTarget(Dir $target) {
    $this->target = $target;
    return $this;
  }
  
  /**
   * @return Webforge\Common\System\Dir
   */
  public function getTarget() {
    return $this->target;
  }
  
  public function getTargetProject() {
    return $this->targetProject;
  }

  public function getSourceProject() {
    return $this->project;
  }
  
  /**
   * @return Webforge\Common\System\File
   */
  public function getChangelog() {
    return $this->getSourceProject()->getSrc()->getFile('inc.changelog.php');
  }
  
  /**
   * @param string $hostName
   * @chainable
   */
  public function setHostName($hostName) {
    $this->hostName = $hostName;
    return $this;
  }

  /**
   * @return string
   */
  public function getHostName() {
    return $this->hostName;
  }

  /**
   * @param string $baseUrl
   * @chainable
   */
  public function setBaseUrl($baseUrl) {
    $this->baseUrl = $baseUrl;
    return $this;
  }

  /**
   * @return string
   */
  public function getBaseUrl() {
    return $this->baseUrl;
  }

  /**
   * @return Webforge\Framework\Container
   */
  public function getWebforgeContainer() {
    return $this->webforgeContainer;
  }
}
?>