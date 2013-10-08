<?php

namespace Psc\CMS;

use Psc\PSC;

class Modules {

  public function __construct(\Webforge\Framework\Project $project, $inTests = FALSE) {
    $this->inTests = $inTests;
    $this->project = $project;
  }

  /**
   * @var array
   */
  protected $modules = array();
  protected $avaibleModules = array(
    'PHPWord'=>array('class'=>'Psc\PHPWord\Module'),
    'PHPExcel'=>array('class'=>'Psc\PHPExcel\Module'),
    'Doctrine'=>array('class'=>'Psc\Doctrine\Module'),
    'Symfony'=>array('class'=>'Psc\Symfony\Module'),
    'Imagine'=>array('class'=>'Psc\Image\ImagineModule'),
    'Hitch'=>array('class'=>'Psc\Hitch\Module'),
    'Swift'=>array('class'=>'Psc\Mail\Module')
  );


  /**
   * @return Psc\CMS\Module
   */
  public function get($name) {
    if (array_key_exists($name, $this->modules))
      return $this->modules[$name];
      
    if ($this->isModule($name)) {
      return $this->create($name);
    }

    throw new \Psc\ModuleNotFoundException('Modul nicht bekannt: '.$name);
  }

  /**
   * Erstellt ein neues Modul
   *
   * wird nur einmal aufgerufen pro Request
   * vorher wurde mit isModule ($name) bereits überprüft
   * @return Psc\CMS\Module
   */
  protected function create($name) {
    $c = $this->avaibleModules[$name]['class'];
    $module = new $c($this->project, $this->inTests);
    $module->setName($name);
    
    $this->modules[$name] = $module;
    
    PSC::getEventManager()->dispatchEvent('Psc.ModuleCreated', NULL, $module);
    
    return $module;
  }

  /**
   * Bootstrapps an module (has to be existing)
   * 
   * @return the return from the bootstrap() function from the module
   */
  public function bootstrap($name) {
    return $this->get($name)->bootstrap();
  }
  
  public function bootstrapIfExists($name) {
    if ($this->isExisting($name)) {
      $this->get($name)->bootstrap();
    }
    
    return $this;
  }

  public function isExisting($name) {
    return $this->isModule($name) && class_exists($this->avaibleModules[$name]['class'], true);
  }

  public function isLoaded($name) {
    return array_key_exists($name, $this->modules);
  }

  /**
   * @return bool
   */
  public function isModule($name) {
    return array_key_exists($name, $this->avaibleModules);
  }
}
