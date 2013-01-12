<?php

namespace Psc\UI\Component;

use Psc\Data\Type\TypeRuleMapper;
use Psc\CMS\Component;
use Psc\Data\Type\TypeExportException;

class ComponentRuleMapper extends \Psc\SimpleObject {
  
  protected $typeRuleMapper;
  
  public function __construct(TypeRuleMapper $typeRuleMapper = NULL) {
    $this->typeRuleMapper = $typeRuleMapper ?: new TypeRuleMapper();
  }
  
  public function getRule(Component $component) {
    
    if ($component->hasValidatorRule()) {
      return $this->createRule($component->getValidatorRule());
    } elseif (($type = $component->getType()) != NULL) {
      try {
        return $this->typeRuleMapper->getRule($type); // schmeiss exception wenn das nicht geht
      } catch (TypeExportException $e) {
        throw new TypeExportException('Für '.$component.' mit dem Type: '.$type.' kann keine ValidatorRule zurückgegeben werden. Der Type hat ebenfalls keine gemappte ValidatorRule. Die Component sollte $this->validatorRule setzen.',0, $e);
      }
    } else {
      throw new TypeExportException('Für '.$component.' kann keine ValidatorRule zurückgegeben werden, da der Type nicht gesetzt ist und hasValidatorRule() FALSE zurückgibt');
    }
  }
  
  /**
   * @return Psc\Form\ValidatorRule
   */
  public function createRule($class) {
    return $this->typeRuleMapper->createRule($class);
  }
  
  /**
   * @param Psc\Data\Type\TypeRuleMapper $typeRuleMapper
   * @chainable
   */
  public function setTypeRuleMapper(TypeRuleMapper $typeRuleMapper) {
    $this->typeRuleMapper = $typeRuleMapper;
    return $this;
  }

  /**
   * @return Psc\Data\Type\TypeRuleMapper
   */
  public function getTypeRuleMapper() {
    return $this->typeRuleMapper;
  }
}
?>