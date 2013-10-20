<?php

namespace Psc\CMS;

use Webforge\Types\Type;
use Webforge\Types\MappedComponentType;
use Psc\Code\Code;
use Psc\Code\Event\Manager;

/**
 * Der ComponentMapper ermittelt aus einem Typ die passenden Componente, die automatisch angezeigt werden soll
 *
 */
class ComponentMapper extends \Psc\SimpleObject implements \Psc\Code\Event\Dispatcher, \Webforge\Types\Adapters\ComponentMapper {

  /**
   * Wird aufgerufen wenn eine Componente durch den Mapper instanziiert wird
   */
  const EVENT_COMPONENT_CREATED = 'Psc\CMS\ComponentMapper.ComponentCreated';

  /**
   * Wird aufgerufen nachdem die Componente fertig gemapped wurde und zurückgegeben werden soll
   *
   * dieses Event ist das verlässlichere Event, da es auch für MappedComponentType möglich ist die Componente zu erstellen ohne createComponent() vom Mapper zu benutzen
   */
  const EVENT_COMPONENT_MAPPED = 'Psc\CMS\ComponentMapper.ComponentMapped';
  
  public function __construct(Manager $manager = NULL) {
    $this->manager = $manager ?: new Manager();
  }
  
  protected $explicitMappings = array(
  );
  
  /**
   * @return Psc\CMS\Component
   */
  public function inferComponent(Type $type) {
    
    if (array_key_exists($type->getName(), $this->explicitMappings)) {
      $component = $this->createComponent($this->explicitMappings[$type->getName()]);
    
    } elseif ($type instanceof MappedComponentType) {
      $component = $type->getMappedComponent($this);
    
    } else {
      throw NoComponentFoundException::build("Zum Type '%s' konnte keine Komponente ermittelt werden. Das einfachste ist im Type \Psc\Data\Type\MappedComponentType zu implementieren.", $type->getName())
        ->set('type',$type)
        ->end();
    }
    
    $component->setType($type);
    $this->manager->dispatchEvent(self::EVENT_COMPONENT_MAPPED, compact('component','type'), $this);
    
    return $component;
  }
  
  public function createComponent($class) {
    $class = Code::expandNamespace($class, 'Psc\UI\Component');
    
    $component = new $class();
    
    $this->manager->dispatchEvent(self::EVENT_COMPONENT_CREATED, array('component'=>$component,'componentClass'=>$class), $this);
    
    return $component;
  }
  
  /**
   * @param string $typeName der Name ohne Psc\Data\Type\ davor und "Type" dahinter ($type->getName())
   * @param string $componentName der Name Psc\UI\Component\$name oder der FQN
   */
  public function addExplicitMapping($typeName, $componentName) {
    $this->explicitMappings[$typeName] = $componentName;
    return $this;
  }
  
  public function getManager() {
    return $this->manager;
  }
}
