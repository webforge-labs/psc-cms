<?php

namespace Psc\Data\Type;

use Psc\Form\ValidatorRule;
use Psc\Code\Code;
use Psc\Code\Generate\GClass;

/**
 * Mappt einen Typ zu einer ValidatorRule
 *
 * dies ist ein Low-Level Type mapper. Für Validierungen von Formularen aus dem CMS sollte der ComponentRuleMapper benutzt werden, der passend zu den Componenten validieren kann
 */
class TypeRuleMapper extends \Psc\SimpleObject implements \Webforge\Types\Adapters\TypeRuleMapper {
  
  public function getRule(Type $type) {
    if (!($type instanceof ValidationType)) {
      throw new TypeExportException($type.' muss das Interface ValidationType implementieren');
    }
    
    return $type->getValidatorRule($this);
  }
  
  public function createRule($class, Array $constructorParams = array()) {
    if ($class instanceof ValidatorRule) {
      return $class;
    }
    
    if (!\Webforge\Common\String::endsWith($class, 'ValidatorRule')) {
      $class .= 'ValidatorRule';
    }
    
    $class = Code::expandNamespace($class, 'Psc\Form');
    
    if (count($constructorParams) === 0) {
      return new $class;
    } else {
      return GClass::newClassInstance($class, $constructorParams);
    }
  }
}
