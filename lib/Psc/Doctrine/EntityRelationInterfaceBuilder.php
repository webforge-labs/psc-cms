<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GMethod;

/**
 * 
 */
class EntityRelationInterfaceBuilder extends \Psc\SimpleObject {
  
  /**
   * @var Psc\Doctrine\EntityRelation
   */
  protected $relation;
  
  public function __construct(EntityRelation $relation) {
    $this->setRelation($relation);
  }
  
  /**
   * Erstellt alle Methoden des RelationInterfaces in der Angegebenen Klasse für die Source Seite
   */
  public function buildSource(GClass $gClass) {
    if ($this->relation->getType() === EntityRelation::ONE_TO_MANY || $this->relation->getType() === EntityRelation::MANY_TO_MANY) {
      $this->createCollectionAdder($gClass);
      $this->createCollectionRemover($gClass);
      $this->createCollectionChecker($gClass);
    }
    
    if ($this->relation->getType() === EntityRelation::MANY_TO_ONE || $this->relation->getType() === EntityRelation::ONE_TO_ONE) {
      $this->injectToSetter($gClass);
    }
  }
  
  protected function createCollectionAdder(GClass $gClass) {
    $paramName = $this->relation->getTarget()->getParamName();
    $method = $gClass->createMethod('add'.$this->relation->getTarget()->getMethodName('singular'),
                          array(new GParameter($paramName, $this->relation->getTarget()->getFQN())),
                              \Psc\TPL\TPL::miniTemplate(
                                'if (!$this->%propertyName%->contains($%paramName%)) {'."\n".
                                '  $this->%propertyName%->add($%paramName%);'."\n".
                                '}'."\n".
                                ($this->relation->shouldUpdateOtherSide() ?
                                  '$%paramName%->add%otherMethodName%($this);'."\n"
                                  : NULL
                                ).
                                'return $this;',
                                array('propertyName'=>$this->relation->getTarget()->getPropertyName(),
                                      'otherMethodName'=>$this->relation->getSource()->getMethodName('singular'),
                                      'paramName'=>$paramName
                                )
                              )
                            );
    if (!$method->hasDocBlock()) {
      $method->createDocBlock();
    }
    $method->getDocBlock()
      ->addSimpleAnnotation('param '.$this->relation->getTarget()->getFQN().' $'.$paramName)
      ->addSimpleAnnotation('chainable');
  }

  protected function createCollectionRemover(GClass $gClass) {
    $paramName = $this->relation->getTarget()->getParamName();
    $method = $gClass->createMethod('remove'.$this->relation->getTarget()->getMethodName('singular'),
                          array(new GParameter($paramName, $this->relation->getTarget()->getFQN())),
                              \Psc\TPL\TPL::miniTemplate(
                                'if ($this->%propertyName%->contains($%paramName%)) {'."\n".
                                '  $this->%propertyName%->removeElement($%paramName%);'."\n".
                                '}'."\n".
                                ($this->relation->shouldUpdateOtherSide() ?
                                  '$%paramName%->remove%otherMethodName%($this);'."\n"
                                  : NULL
                                ).
                                'return $this;',
                                array('propertyName'=>$this->relation->getTarget()->getPropertyName(),
                                      'otherMethodName'=>$this->relation->getSource()->getMethodName('singular'),
                                      'paramName'=>$paramName
                                )
                              )
                            );
    if (!$method->hasDocBlock()) {
      $method->createDocBlock();
    }
    $method->getDocBlock()
      ->addSimpleAnnotation('param '.$this->relation->getTarget()->getFQN().' $'.$paramName)
      ->addSimpleAnnotation('chainable');
  }

  protected function createCollectionChecker(GClass $gClass) {
    $paramName = $this->relation->getTarget()->getParamName();
    $method = $gClass->createMethod('has'.$this->relation->getTarget()->getMethodName('singular'),
                          array(new GParameter($paramName, $this->relation->getTarget()->getFQN())),
                              \Psc\TPL\TPL::miniTemplate(
                                'return $this->%propertyName%->contains($%paramName%);',
                                array('propertyName'=>$this->relation->getTarget()->getPropertyName(),
                                      'paramName'=>$paramName
                                )
                              )
                          );
    if (!$method->hasDocBlock()) {
      $method->createDocBlock();
    }
    $method->getDocBlock()
      ->addSimpleAnnotation('param '.$this->relation->getTarget()->getFQN().' $'.$paramName)
      ->addSimpleAnnotation('return bool');
  }
  
  protected function injectToSetter(GClass $gClass) {
    $setter = 'set'.$this->relation->getSourceMethodName();
    if (!$gClass->hasOwnMethod($setter)) {
      throw new \RuntimeException('in der Klasse kann '.$setter.' nicht gefunden werden. Womöglich muss der Setter vererbt werden');
    } else {
      $setter = $gClass->getMethod($setter);
    }
    if ($this->relation->isTargetNullable()) {
      $firstParam = current($setter->getParameters());
      $firstParam->setOptional(TRUE)->setDefault(NULL);
    }

    if ($this->relation->shouldUpdateOtherSide()) {
      // wenn die Relation bidrektional ist muss der normale setter auf der Inverse side addXXX auf der aufrufen
      // das ist aber gar nicht so trivial, weil wie wird removed?
        
      // before('return $this').insert(...)
      $body = $setter->getBodyCode();
      $lastLine = array_pop($body);
      if (\Psc\Preg::match($lastLine,'/^\s*return \$this;$/') <= 0) {
        throw new \Psc\Exception('EntityRelationInterfaceBuilder möchte den Setter: '.$setter->getName().' für '.$gClass.' ergänzen, aber der Body code scheint kein autoGenererierter zu sein.');
      }
        
      $body[] = \Psc\TPL\TPL::miniTemplate(
                                           ($this->relation->isTargetNullable()
                                            ? 'if (isset($%paramName%)) '
                                            : NULL
                                           ). // kein umbruch weil $body[]
                                           '$%paramName%->add%otherMethodName%($this);'."\n",
                                           array('paramName'=>$this->relation->getTarget()->getParamName(),
                                                 'otherMethodName'=>$this->relation->getSource()->getMethodName('singular')
                                                 )
                                           );
      $body[] = $lastLine;
      $setter->setBodyCode($body);
    }
  }

  
  /**
   * @param Psc\Doctrine\EntityRelation $relation
   */
  public function setRelation(EntityRelation $relation) {
    $this->relation = $relation;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\EntityRelation
   */
  public function getRelation() {
    return $this->relation;
  }
}
?>