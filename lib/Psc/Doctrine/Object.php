<?php

namespace Psc\Doctrine;

use Psc\A,
    Doctrine\ORM\Mapping\ClassMetadataFactory,
    Psc\Doctrine\Helper as DoctrineHelper,
    Doctrine\ORM\EntityManager,
    Psc\Code\Code,
    Psc\URL\Helper AS URLHelper,
    \Psc\CMS\TabsContentItem
;

abstract class Object extends \Psc\JS\JSONObject implements TabsContentItem, \Psc\Form\Item, \Doctrine\Common\Comparable, \Psc\Data\Exportable { /* wir schauen mal ob das mit unseren auto-gettern und settern so funktioniert */
  
  protected $doctrineMetadata;
  
  public function __construct() {
  }
  
  protected function getJSONFields() {
    return array();
  }
  
  public function export() {
    return $this->exportJSON();
  }
  
  public static function factory() {
    $args = func_get_args();
    return parent::factory(get_called_class(), $args, self::REPLACE_ARGS);
  }
  
  /**
   * @return EntityManager
   */
  public function getEM() {
    return \Psc\PSC::getProject()->getModule('Doctrine')->getEntityManager();
  }
  
  /**
   * @return \Doctrine\EntitiyRepository
   */
  public function getRepository() {
    return $this->getEM()->getRepository($this->getEntityName());
  }
  
  /**
   * weil get_class() hier auch sowas wie entitiesProxy sein kann, machen wir das hier save
   * und strings zurückgeben ist auch schneller ..
   *
   * und wir müssen es EINMAL im Projekt machen und wir können ein Template dafür bauen ..
   * @return string voll qualifizierter Name des Entities (mit Namespace mit \ getrennt, aber nicht am Anfang)
   */
  abstract public function getEntityName();
  
  /**
   * Persist UND flush für dieses Objekt
   *
   * danach ist das Objekt auf jeden Fall in der Datenbank. Führ globales flush() des EntitiyManagers aus!
   */
  public function save() {
    $this->getEM()->persist($this);
    $this->getEM()->flush();
    return $this;
  }

  /**
   * Markiert das Objekt im EM für den nächsten flush() als Delete
   */
  public function remove() {
    $this->getEM()->remove($this);
    return $this;
  }

  /**
   * Speichert das Object nur, führt aber kein flush() aus
   */
  public function persist() {
    $this->getEM()->persist($this);
    return $this;
  }
  
  
  
  /**
   * @return array|INT
   */
  abstract public function getIdentifier();
  
  /**
   * Gibt die Spalten-Namen des Identifiers zurück
   *
   * Rückgabe ist immer ein array, die keys sind ist nicht nach den spaltennamen alphabetisch sortiert, auch wenn es nur einer ist
   * @return array
   */
  public function getIdentifiers() {
    return $this->getDoctrineMetaData()->getIdentifier();
  }
  
  /**
   * Gibt den Identifier als SkalarenWert zurück
   * 
   * Kann overloaded werden um Performance zu sparen
   */
  public function getIdentifierHash() {
    return self::_getIdentifierHash($this->getIdentifier());
  }
  
  
  public function getDoctrineMetadata() {
    if (!isset($this->doctrineMetadata))
      $this->doctrineMetadata = $this->getEM()->getMetadataFactory()->getMetadataFor(ltrim($this->getEntityName(),'\\'));
    return $this->doctrineMetadata;
  }
  
  /**
   *
   * für 2 neue Objekte wird objectEquals aufgerufen, dies kann dann notfalls von der Subklasse überschrieben werden
   * ansonsten wird auf identität geprüft
   * @return bool
   */
  public function equals(\Psc\Doctrine\Object $otherObject = NULL) {
    if ($otherObject == NULL) return FALSE;
    
    if ($this->getIdentifier() === NULL && $otherObject->getIdentifier() === NULL) {
      return $this->objectEquals($otherObject);
    }
    
    return $this->getIdentifier() === $otherObject->getIdentifier();
  }
  
  /**
   * @return int 0|-1|1
   */
  public function compareTo($other) {
    if ($this->equals($other)) return 0;
    
    return $this->getIdentifier() > $other->getIdentifier() ? 1 : -1;
  }
  
  protected function objectEquals(\Psc\Doctrine\Object $otherObject) {
    return $this === $otherObject;
  }
  
  public function __toString() {
    return '[class Psc\Doctrine\Object<'.$this->getEntityName().'> id:'.$this->getIdentifier().'/'.ltrim(\Psc\SimpleObject::getObjectId($this),'0').']';
  }
  
  /**
   * @param array ein identifier array mit columnname => value der id-werte
   * @return string|id gibt den Identifier als Scalar Value zurück
   */
  public static function _getIdentifierHash(Array $identifiers) {
    if (count($identifiers) == 1) {
      return A::peek($identifiers);
    } elseif(count($identifiers) > 0) {
      ksort($identifiers);
      return implode('.',$identifiers);
    } else {
      throw new Exception('Kein Hash für leere identifiers möglich');
    }
  }
  
  // ich weiß noch nicht genau, das ist auch in doctrine unter FIXME, da warte ich glaub ich lieber auf die Doctrine Lösung
  //**
  // *
  // * Die Rückgabe kann dann in find() benutzt werden
  // * 
  // * @param string $entityName der volle Name zum Objekt z.b. Entities\User (so wie man ihn bei getRepository() benutzen würde
  // * @param mixed $identifier die Ausprägungen des identifiers. Bei mehreren müssen diese in alphabetischer key-Reihenfolge sein
  // * @return array keys sind alphabetisch sortiert und werden mit $identifier zusammengefügt
  // */
  //public static function getIdentifierCriteria($entityName, $identifier, EntityManager $em = NULL) {
  //  if (!isset($em)) $em = DoctrineHelper::em();
  //  
  //  $identifier = (array) $identifier;
  //  $meta = $em->getMetadataFactory()->getMetadataFor($entityName);
  //  
  //  $o = $meta->newInstance();
  //  $idDef = $o->getIdentifiers();
  //
  //  /* short: die identifierCriteria ist schon ganz okay so */
  //  if (array_keys($identifier) == $idDef) {
  //    return $identifier;
  //  }
  //  
  //  if (count($idDef) != count($identifier)) {
  //    throw new Exception('Es kann kein Kriterium für den identifier '.Code::varInfo($identifier) .' erstellt werden für die Definition: '.Code::varInfo($idDef));
  //  }
  //  
  //  sort($idDef);
  //  
  //  return array_combine($idDef,$identifier);
  //}
  
  /* INTERFACE: TabsContentItem */
  public function getTabsLabel() {
    return $this->__toString();
  }
  
  public function getTabsFullLabel() {
    return $this->__toString();
  }
  
  public function getTabsURL(Array $qvars = array()) {
    list ($typeName, $id) = $this->getTabsId();
    
    return URLHelper::getURL('/ajax.php', URLHelper::MY_QUERY_VARS | URLHelper::RELATIVE,
                             array_merge(
                                            array('todo'=>'tabs',
                                                  'ctrlTodo'=>'content.data',
                                                  'ajaxData'=>array(
                                                                    'type'=>$typeName,
                                                                    'identifier'=>$id,
                                                                    'data'=>$this->getTabsData()
                                                                  )
                                                ),
                                          $qvars)
                               );
  }
  
  public function getTabsId() {
    $p = explode('\\',$this->getEntityName());
    return array(mb_strtolower(array_pop($p)),$this->getIdentifier());
  }
  
  public function getTabsAction() {
    $id = $this->getTabsId();
    if (count($id) == 3)
      return $id[2];
    else
      return 'edit';
  }
  
  public function getTabsData() {
    return array();
  }
  
  public function setTabsData(Array $data) {
    return $this;
  }
  
  /* END INTERFACE: TabsContentItem */
  
  /* INTERFACE: \Psc\Form\Item */
  public function getFormLabel() {
    return $this->getTabsLabel();
  }

  public function getFormValue() {
    list ($typeName, $id) = $this->getTabsId();
    return $id;
  }
  /* END INTERFACE: \Psc\Form\Item */
}