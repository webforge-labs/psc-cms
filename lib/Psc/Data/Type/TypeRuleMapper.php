<?php

namespace Psc\Data\Type;

use Webforge\Types\Type as WebforgeType;
use Psc\Form\ValidatorRule;
use Webforge\Common\ClassUtil;
use Webforge\Types\TypeExportException;
use Webforge\Types\ObjectType;

/**
 * Mappt einen Typ zu einer ValidatorRule
 *
 * dies ist ein Low-Level Type mapper. Für Validierungen von Formularen aus dem CMS sollte der ComponentRuleMapper benutzt werden, der passend zu den Componenten validieren kann
 */
class TypeRuleMapper extends \Psc\SimpleObject implements \Webforge\Types\Adapters\TypeRuleMapper {
  
  public function getRule(WebforgeType $type) {
    if (!($type instanceof \Webforge\Types\ValidationType)) {
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
    
    $class = ClassUtil::expandNamespace($class, 'Psc\Form');
    
    if (count($constructorParams) === 0) {
      return new $class;
    } else {
      return ClassUtil::newClassInstance($class, $constructorParams);
    }
  }
}
