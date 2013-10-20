<?php

namespace Psc\Code\Compile;

use Webforge\Types\Type;
use Webforge\Types\TraversableType;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;

/**
 * Erstellt für ein Property, welches ein Array ist Methoden die es erlauben auf das Property wie auf eine Collection zuzugreifen
 * 
 * ein Objekt aus dem Universum der collection wird als item bezeichnet
 * 
 * add$PropertySingular($propertySingular) {
 *   $this->$propertyPlural[] = $propertySingular;
 * }
 * remove$PropertySingular($propertySingular) {
 *   $key = array_search($propertySingular, $this->$propertyPlural);
 * }
 * 
 * set$PropertyPlural()
 * get$PropertyPlural()
 */
class ArrayCollectionInterfaceExtension extends Extension {
  
  const UPCASED = 'upcased';
  
  /**
   * @var string
   */
  protected $propertyPlural;
  
  /**
   * @var string
   */
  protected $propertySingular;
  
  /**
   * @var string
   */
  protected $itemType;
  
  /**
   * @var Psc\Data\Type\Type
   */
  protected $collectionType;
  
  /**
   * Aller PHP-Template-Code für alle getter/setter/remover/checker für die collectionTypes
   *
   * initialisierung siehe in getImplementations
   * @var array
   */
  protected $implementations = NULL;
  
  public function __construct($propertyPlural, $propertySingular, $itemType = NULL) {
    $this->propertyPlural = $propertyPlural;
    $this->propertySingular = $propertySingular;
    $this->collectionType = Type::create('Array');
    
    if (isset($itemType)) {
      $this->setItemType(Type::create($itemType));
    }
  }
  
  public function compile(GClass $gClass, $flags = 0) {
    $this->createClassBuilder($gClass);
    
    // collectionProperty
    $collection = $this->createPropertyIfNotExists($this->propertyPlural, $this->collectionType);
    if ($this->collectionType->hasScalarDefaultValue())
      $collection->setDefaultValue($this->collectionType->getDefaultValue());
    //else: eigentlich in den Constructor hacken
    
    $this->classBuilder->generateGetter($collection);
    $this->classBuilder->generateSetter($collection);
    
    $item = $this->getItem();
    $this->codeVars = array('collection'=>$collection->getName(),
                            'item'=>$item->lcSingular,
                            'ucItem'=>$item->ucSingular,
                            'strict'=>'TRUE',
                           );
    $paramAnnotation = 'param '.$item->docType.' $'.$item->lcSingular;

    $gClass->createMethod('get'.$item->ucSingular,
                          array(
                            new GParameter('key')
                          ),
                          $this->getImplementation('itemGetter')
                          )
      ->createDocBlock()
        ->addSimpleAnnotation('param integer $key 0-based')
        ->addSimpleAnnotation('return '.$item->docType.'|NULL')
    ;

    $gClass->createMethod('add'.$item->ucSingular,
                          array(
                            new GParameter($item->lcSingular, $item->hint)
                          ),
                          $this->getImplementation('itemAdder')
                          )
      ->createDocBlock()
        ->addSimpleAnnotation($paramAnnotation)
        ->addSimpleAnnotation('chainable')
    ;
    
    $gClass->createMethod('remove'.$item->ucSingular,
                          array(
                            new GParameter($item->lcSingular, $item->hint)
                          ),
                          $this->getImplementation('itemRemover')
                          )
      ->createDocBlock()
        ->addSimpleAnnotation($paramAnnotation)
        ->addSimpleAnnotation('chainable')
    ;
    
    $gClass->createMethod('has'.$item->ucSingular,
                          array(
                            new GParameter($item->lcSingular, $item->hint)
                          ),
                          $this->getImplementation('itemChecker')
                          )
      ->createDocBlock()
        ->addSimpleAnnotation($paramAnnotation)
        ->addSimpleAnnotation('return bool')
    ;
    
    $this->classBuilder->generateDocBlocks();
  }
  
  protected function getImplementation($type) {
    if (!isset($this->implementations)) {
      $this->implementations['Array'] = Array(
        'itemGetter'=>array(
          'return array_key_exists($key, $this->%collection%) ? $this->%collection%[$key] : NULL;'
        ),
        'itemAdder'=>array(
          '$this->%collection%[] = $%item%;',
          'return $this;'
        ),
        'itemChecker'=>array(
          'return in_array($%item%, $this->%collection%);'
        ),
        'itemRemover'=>array(
          'if (($key = array_search($%item%, $this->%collection%, %strict%)) !== FALSE) {',
          '  array_splice($this->%collection%, $key, 1);',
          '}',
          'return $this;'
        )
      );

      $this->implementations['Collection'] = Array(
        'itemGetter'=>array(
          'return $this->%collection%->containsKey($key, $this->%collection%) ? $this->%collection%->get($key) : NULL;'
        ),
        'itemAdder'=>array(
          '$this->%collection%->add($%item%);',
          'return $this;'
        ),
        'itemChecker'=>array(
          'return $this->%collection%->contains($%item%);'
        ),
        'itemRemover'=>array(
          'if ($this->contains($%item%)) {',
          '  $this->removeElement($%item%);',
          '}',
          'return $this;'
        )
      );
    }

    $vars = $this->codeVars;
    $code = array_map(
              function ($line) use ($vars) {
                return \Psc\TPL\TPL::miniTemplate($line, $vars);
              },
              $this->implementations[ $this->collectionType->getName() ][ $type ]
            );
    
    return $code;
  }
  
  protected function getItem() {
    return (object) array(
      'hint'=>$this->itemType ? $this->itemType->getPHPHint() : NULL,
      'docType'=>$this->itemType ? $this->itemType->getDocType() : 'undefined',
      'lcSingular'=>$this->getPropertySingular(),
      'ucSingular'=>$this->getPropertySingular(self::UPCASED)
    );
  }
  
  /**
   * @param string $propertyPlural
   */
  public function setPropertyPlural($propertyPlural) {
    $this->propertyPlural = $propertyPlural;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getPropertyPlural() {
    return $this->propertyPlural;
  }
  
  /**
   * @param string $propertySingular
   */
  public function setPropertySingular($propertySingular) {
    $this->propertySingular = $propertySingular;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getPropertySingular($type = NULL) {
    if ($type == self::UPCASED) {
      return ucfirst($this->propertySingular);
    }
    return $this->propertySingular;
  }
  
  /**
   * @param string $itemType
   */
  public function setItemType($itemType) {
    $this->itemType = $itemType;
    $this->collectionType->setType($this->itemType);
    return $this;
  }
  
  /**
   * @return string
   */
  public function getItemType() {
    return $this->itemType;
  }
  
  /**
   * @param Psc\Data\Type\EnclosingType $collectionType
   * @chainable
   */
  public function setCollectionType(TraversableType $collectionType) {
    $this->collectionType = $collectionType;
    if ($this->itemType) {
      $this->collectionType->setType($this->itemType);
    }
    return $this;
  }

  /**
   * @return Psc\Data\Type\TraversableType
   */
  public function getCollectionType() {
    return $this->collectionType;
  }
}
