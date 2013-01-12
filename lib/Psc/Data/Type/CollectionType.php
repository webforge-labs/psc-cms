<?php

namespace Psc\Data\Type;

use Psc\Code\Code;
use Psc\Code\Generate\GClass;

class CollectionType extends \Psc\Data\Type\InterfacedObjectType implements MappedComponentType, TraversableType {
  
  const PSC_ARRAY_COLLECTION = 'Psc\Data\ArrayCollection';
  const DOCTRINE_ARRAY_COLLECTION = 'Doctrine\Common\Collections\ArrayCollection';

  /**
   * @var bool|NULL
   */
  protected $isList = NULL;
  
  /**
   * @param const $implementation
   */
  public function __construct($implementation = NULL, Type $innerType = NULL) {
    if (!($implementation instanceof GClass) && $implementation !== NULL) {
      Code::value($implementation, self::PSC_ARRAY_COLLECTION, self::DOCTRINE_ARRAY_COLLECTION);
      $implementation = new GClass($implementation);
    }
    
    parent::__construct($implementation);
    
    if (isset($innerType)) {
      $this->setType($innerType);
    }
  }
  
  /**
   * Collection zu Component-Mapping
   *
   */
  public function getMappedComponent(\Psc\CMS\ComponentMapper $componentMapper) {
    return $componentMapper->createComponent('ComboDropBox');
  }
  

  /**
   * @return bool
   */
  public function isTyped() {
    return isset($this->type);
  }
  
  /**
   * @return Psc\Data\Type\Type
   */
  public function getType() {
    if (!isset($this->type)) throw new NotTypedException('Kann den Type der Collection nicht zurückgeben.');
    return $this->type;
  }
  
  
  /**
   *
   * wird der Parameter NULL Übergeben ist der Array nicht mehr getyped
   * @param Psc\Data\Type\Type|NULL
   */
  public function setType(Type $type = NULL) {
    $this->type = $type;
    return $this;
  }
  
  /**
   * Gibt den PHP-DokumentationsTyp der Collection zurück
   *
   * Doctrine\Common\Collections\Collection<Psc\Doctrine\Entity> z. B.
   */
  public function getPHPType() {
    return $this->getInterface().($this->isTyped() ? sprintf('<%s>',$this->getType()->getPHPType()) : NULL);
  }
  
  public function getInterface() {
    return 'Doctrine\Common\Collections\Collection';
    
    // das geht leider nicht, weil wir dann die doctrine arraycollection nicht als implementierung benutzen können
    // yagni?
    return $this->getInterfaceDefinition('Collection');
  }

  public function getDefaultValue() {
    // beide collectio interfaces lassen sich mit new und einem leeren array erstellen
    $c = $this->getClassFQN();
    return new $c(array());
  }

  public function hasScalarDefaultValue() {
    return FALSE;
  }
  

  /**
   * @return bool|NULL wenn es === NULL ist, gibt es keine Aussage explizite Aussage darüber ob das Array eine Liste ist
   */
  public function isList() {
    return $this->isList;
  }
  
  /**
   * @param bool $list
   */
  public function setIsList($list) {
    $this->isList = $list == TRUE;
    return $this;
  }
}
?>