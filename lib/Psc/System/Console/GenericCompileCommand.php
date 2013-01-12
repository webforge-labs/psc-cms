<?php

namespace Psc\System\Console;

use Psc\Code\Generate\GClass;
use Psc\Code\Code;

class GenericCompileCommand extends CompileCommand {
  
  protected function configure() {
    $this
      ->setName('compile:generic')
      ->setDescription(
        'Generisches Interface für das Aufrufen von Compile Extensions'
      );
    
    parent::configure();
    
    $this->addArgument('name', self::REQUIRED, 'Der Name der Compile Extension die ausgeführt werden soll. (ohne Namespace möglich)');
    $this->addArgument('parametersJSON', self::REQUIRED, 'Die Parameter als JSON Objekt für den Constructor / die Properties der Extension. Muss ein assoziatives Array sein');
  }
  
  protected function doExecute($input, $output) {
    $extensionGClass = $this->parseExtensionGClass($input);
    $inputParams = $this->parseParams($input);
    
    $constructParams = array();
    foreach ($this->getConstructorFields($extensionGClass) as $name) {
      if (array_key_exists($name, $inputParams)) {
        $constructParams[] = $inputParams[$name];
        unset($inputParams[$name]);
      }
    }
    
    $extension = $extensionGClass->newInstance($constructParams);
    
    foreach ($inputParams as $name => $value) {
      $set = Code::castSetter($name);
      $set($extension, $value);
    }
    
    $compiler = $this->getCompiler();
    $compiler->addExtension($extension);
    
    $compiler->compile($this->getOutFile());
  }
  
  /**
   * @return array
   */
  protected function parseParams($input) {
    $params = json_decode($json = $input->getArgument('parametersJSON'));
    
    if ($params === NULL) {
      throw new \InvalidArgumentException(sprintf("parametersJSON muss valides JSON sein: '%s' wurde übergeben", $json));
    }
    
    $params = (array) $params;
    
    if (\Psc\A::isNumeric($params)) {
      throw new \InvalidArgumentException(sprintf("parametesrJSON muss ein object sein, mit constructorParameterName => value oder propertyName => value"));
    }
    
    return $params;
  }
  
  protected function parseExtensionGClass($input) {
    $extensionClass = Code::expandNamespace(\Psc\String::expand($input->getArgument('name'), 'Extension'), 'Psc\Code\Compile');
    return GClass::factory($extensionClass);
  }
  
  /**
   * Gibt die Felder zurück, die dem Entity per Constructor übergeben werden
   * @return string[]
   */
  public function getConstructorFields($gClass) {
    if (!isset($this->constructorFields)) {
      $this->constructorFields = array();
      
      if ($gClass->hasMethod('__construct')) {
        foreach ($gClass->getMethod('__construct')->getParameters() as $parameter) {
          $this->constructorFields[] = $parameter->getName();
        }
      }
    }
    
    return $this->constructorFields;
  }  
}
?>