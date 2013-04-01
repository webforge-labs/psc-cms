<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Code\Code;
use Psc\Code\Generate\DocBlock;

/**
 * 
 */
class EntityRelation extends \Psc\SimpleObject {
  
  const MANY_TO_MANY = 'ManyToMany';
  
  const ONE_TO_MANY = 'OneToMany';
  
  const MANY_TO_ONE = 'ManyToOne';
  
  const ONE_TO_ONE = 'OneToOne';
  
  const OWNING = 'owning';
  
  const INVERSE = 'inverse';
  
  const NONE = NULL;
  
  const SOURCE = 'source';
  
  const TARGET = 'target';
  
  const BIDIRECTIONAL = 'bidirectional';
  
  const UNIDIRECTIONAL = 'unidirectional';
  
  const SELF_REFERENCING = 'self referencing';
  
  const CASCADE = 'cascade';
  
  const SET_NULL = 'SET NULL';
  
  /**
   * @var Psc\Doctrine\EntityRelationMeta
   */
  protected $source;
  
  /**
   * @var Psc\Doctrine\EntityRelationMeta
   */
  protected $target;
  
  /**
   * Der Relation Type als (MANY|ONE)_TO_(MANY|ONE) Konstante
   * 
   * @var string
   */
  protected $type;
  
  /**
   * Owning / Inverse / NULL
   * 
   * @var const|NULL
   */
  protected $sourceSide;
  
  /**
   * Owning / Inverse / NULL
   * 
   * @var const|NULL
   */
  protected $targetSide;
  
  /**
   * @var const
   */
  protected $direction;
  
  /**
   * JoinTable Annotation bei ManyToMany
   * 
   * @var Psc\Doctrine\Annotation
   */
  protected $joinTable;
  
  /**
   * Die ManyToOne / ManyToMany / OneToMany / OneToOne Annotation
   * 
   * @var Psc\Doctrine\Annotation
   */
  protected $relation;
  
  /**
   * Bei ManyToOne
   * @var Psc\Doctrine\Annotation
   */
  protected $joinColumn;

  /**
   * Zeigt an, ob die JoinColumn nullable sein darf
   *
   * anders als doctrine ist unser default fall hier FALSE
   * siehe isTargetNullable() (wird im RelationInterface ausgelesen)
   * @var bool
   */
  protected $nullable = FALSE;

  /**
   * Bei ManyToMany und OneToMany
   *
   * see setOrderBy()
   * @var Psc\Doctrine\Annotation
   */
  protected $orderBy;
  
  /**
   * Was soll passieren wenn die Target Side der Relation gelöscht wird?
   * 
   * @var string
   */
  protected $onDelete;
  
  /**
   * @var bool
   */
  protected $updateOtherSide;
  
  /**
   * @var array
   */
  protected $otherAnnotations = array();
  
  /**
   * @var bool
   */
  protected $withoutInterface = FALSE;
  
  /**
   * @param const type self::MANY_TO_MANY|self::MANY_TO_ONE|self::ONE_TO_MANY|self::ONE_TO_ONE
   */
  public function __construct(EntityRelationMeta $source, EntityRelationMeta $target, $type, $direction = 'bidirectional', $whoIsOwningSide = 'source') {
    $this->source = $source;
    $this->target = $target;
    
    // hart überschreiben
    if ($this->source->getFQN() === $this->target->getFQN()) { // wichtig ist hier, dass wir FQN vergleichen, denn getName() kann auch mit setAlias() verändert worden sein
      $direction = self::SELF_REFERENCING;
      // this could be a nice notice?
      //('Overwriting to direction: self-referencing. '.$this->source->getFQN().' and '.$this->target->getFQN().' are the same');
    }
    
    $this->setRelationType($type, $direction, $whoIsOwningSide);
  }
  
  /**
   * Fügt einem DocBlock die passenden Annotations für die Source Seite der Relation hinzu
   */
  public function addAnnotationsForSource(DocBlock $docBlock) {
    extract(Annotation::getClosureHelpers());
    
    $docBlock->addAnnotation($this->getRelation());
    
    if ($this->type === self::MANY_TO_MANY && $this->sourceSide !== self::INVERSE) { // bei unidirectional brauchen wir natürlich auch einen JoinTable
      $docBlock->addAnnotation($this->getJoinTable());
    }
    
    if ($this->type === self::MANY_TO_ONE || $this->type === self::ONE_TO_ONE) {
      $docBlock->addAnnotation($this->getJoinColumn());
    }
    
    if (($this->type === self::MANY_TO_MANY || $this->type === self::ONE_TO_MANY) && isset($this->orderBy)) {
      $docBlock->addAnnotation($this->orderBy);
    }
    
    foreach ($this->otherAnnotations as $annotation) {
      $docBlock->addAnnotation($annotation);
    }
  }
  
  public static function create($sourceFQN, $targetFQN, $type, $direction = 'bidirectional', $whoIsOwningSide = 'source') {
    return new static(
      new EntityRelationMeta(new GClass($sourceFQN), 'source'),
      new EntityRelationMeta(new GClass($targetFQN), 'target'),
      $type
    );
  }
  
  /**
   * Setzt alle Eigenschaften der Relation gleichzeitig
   * 
   * $whoIsOwningSide wird bei MANY_TO_ONE und ONE_TO_MANY ignoriert (denn dort ist es immer automatisch die MANY Seite)
   * ist $direction nicht bidirectional wird die inverse side der Relation nicht automatisch gesetzt
   */
  public function setRelationType($type, $direction = 'bidirectional', $whoIsOwningSide = 'source') {
    
    // zuerst setzen wir die direction, denn wenn type nicht gesetzt ist, macht die nichts
    $this->setDirection($direction);
    
    // dann setzen wir den type, dies setzt dann bei one to many und many to one die inverse side und owning side schon korrekt
    // weil ja direction schon unidirectional oder bidirectional ist
    $this->setType($type);
    
    // fails silently bei oneToMany and ManyToOne (oder halt mal nicht, falls sich das ändert)
    if (isset($whoIsOwningSide))
      $this->setOwningSide($whoIsOwningSide);
    
    return $this;
  }
  
  /**
   * Setzt die Owning (und ggf. inverse Side)
   * 
   * ist die relation bidirectional (default), so wird die inverse side automatisch mitgesetzt
   * @param SOURCE|TARGET $sourceOrTarget
   */
  public function setOwningSide($sourceOrTarget) {
    Code::value($sourceOrTarget, self::SOURCE, self::TARGET, TRUE);
    
    if ($this->type === self::MANY_TO_MANY || $this->type === self::ONE_TO_ONE) {
      if ($sourceOrTarget === self::SOURCE || $sourceOrTarget === TRUE) {
        $this->sourceSide = self::OWNING;
        $this->targetSide = $this->isBidirectional() ? self::INVERSE : self::NONE; // many seite
      } else {
        if ($this->isUnidirectional()) {
          throw new \RuntimeException('Da die Relation unidirectional ist, kann die Owning Side nicht Target sein. (sonst gäbe es gar keine Owning Side)');
        }
        
        $this->sourceSide = self::INVERSE;
        $this->targetSide = self::OWNING;
      }
    }
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\EntityRelationMeta
   */
  public function getSource() {
    return $this->source;
  }
  
  public function getSourcePropertyName() {
    return $this->target->getPropertyName(); // immer kreuzen, denn der PropertyName in Source ist der Klassen-Name in klein von target
  }
  
  public function getSourcePropertyType() {
    return $this->target->getPropertyType(); // immer kreuzen
  }
  
  public function getSourceParamName() {
    return $this->target->getParamName(); // immer kreuzen
  }
  
  public function getSourceMethodName($pluralOrSingular = NULL) {
    return $this->target->getMethodName($pluralOrSingular);
  }
  
  /**
   * @return Psc\Doctrine\EntityRelationMeta
   */
  public function getTarget() {
    return $this->target;
  }
  
  public function getTargetPropertyName() {
    return $this->source->getPropertyName(); // immer kreuzen, denn der PropertyName in Target ist der Klassen-Name in klein von Source
  }
  
  public function getTargetPropertyType() {
    return $this->source->getPropertyType(); // immer kreuzen, denn der PropertyName in Target ist der Klassen-Name in klein von Source
  }
  
  public function getTargetParamName() {
    return $this->source->getParamName(); // immer kreuzen, denn der PropertyName in Target ist der Klassen-Name in klein von Source
  }
  
  /**
   * Setzt den (Relation-)Type der Relation (aka (MANY|ONE)_TO_(MANY|ONE) Konstante)
   * 
   * @param const $type
   */
  public function setType($type) {
    Code::value($type, self::MANY_TO_MANY, self::MANY_TO_ONE, self::ONE_TO_MANY, self::ONE_TO_ONE);
    $this->type = $type;
    
    // wir tun immer so, als wären die anderen states schon sinnvoll gesetzt und passen die klasse an unsere ideen an.
    if ($this->type === self::MANY_TO_ONE) {
      $this->sourceSide = self::OWNING; // many seite
      $this->targetSide = $this->isBidirectional() || $this->isSelfReferencing() ? self::INVERSE : self::NONE; // one seite
      
      // eben weil die Properties nicht verdreht sind, ist auch hier single und collection vertauscht
      // denn eigentlich hat die ja die many side die single value
      $this->source->setValueType(EntityRelationMeta::COLLECTION_VALUED);
      $this->target->setValueType(EntityRelationMeta::SINGLE_VALUED);
    } elseif ($this->type === self::ONE_TO_MANY) {
      $this->sourceSide = self::INVERSE; // one seite
      $this->targetSide = $this->isBidirectional() || $this->isSelfReferencing() ? self::OWNING : self::NONE; // many seite
    
      // eben weil die Properties nicht verdreht sind, ist auch hier single und collection vertauscht
      // denn eigentlich hat die ja die many side die single value
      $this->source->setValueType(EntityRelationMeta::SINGLE_VALUED);
      $this->target->setValueType(EntityRelationMeta::COLLECTION_VALUED);
    } elseif ($this->type === self::MANY_TO_MANY) {
      $this->target->setValueType(EntityRelationMeta::COLLECTION_VALUED);
      $this->source->setValueType(EntityRelationMeta::COLLECTION_VALUED);
    } elseif ($this->type === self::ONE_TO_ONE) {
      $this->source->setValueType(EntityRelationMeta::SINGLE_VALUED);
      $this->target->setValueType(EntityRelationMeta::SINGLE_VALUED);
    }
    
    return $this;
  }
  
  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }
  
  /**
   * @param const|NULL $sourceSide
   * @chainable
   */
  public function setSourceSide($sourceSide) {
    $this->sourceSide = $sourceSide;
    return $this;
  }
  
  /**
   * @return const
   */
  public function getSourceSide() {
    return $this->sourceSide;
  }
  
  /**
   * @param const|NULL $targetSide
   * @chainable
   */
  public function setTargetSide($targetSide) {
    $this->targetSide = $targetSide;
    return $this;
  }
  
  /**
   * @return const
   */
  public function getTargetSide() {
    return $this->targetSide;
  }
  
  /**
   * @param string $direction
   */
  public function setDirection($direction) {
    Code::value($direction, self::BIDIRECTIONAL, self::UNIDIRECTIONAL, self::SELF_REFERENCING);
    $this->direction = $direction;
    
    if (isset($this->type) && $this->isUnidirectional()) { // nur dann müssen wir was tun
      $this->targetSide = self::NONE; // denn unidirectionale relations haben keine inverse side
      $this->sourceSide = self::NONE; // und keine owning side
    } elseif (isset($this->type) && $this->isSelfReferencing()) {
      $this->targetSide = self::NONE;
      $this->sourceSide = self::NONE; 
    }
    
    return $this;
  }
  
  /**
   * @return string
   */
  public function getDirection() {
    return $this->direction;
  }
  
  /**
   * @return bool
   */
  public function isBidirectional() {
    return $this->direction === self::BIDIRECTIONAL;
  }
  
  /**
   * @return bool
   */
  public function isUnidirectional() {
    return $this->direction === self::UNIDIRECTIONAL;
  }
  
  /**
   * @return bool
   */
  public function isSelfReferencing() {
    return $this->direction === self::SELF_REFERENCING;
  }
  
  /**
   * Gibt zurück ob die Source-Seite im relation interface die andere Seite mit updaten soll
   * 
   * @return bool
   */
  public function shouldUpdateOtherSide() {
    if (isset($this->updateOtherSide)) {
      return $this->updateOtherSide;
    }
    
    return $this->isBidirectional() && $this->getSourceSide() === EntityRelation::OWNING;
  }
  
  /**
   * @param Psc\Doctrine\Annotation $joinTable
   */
  public function setJoinTable(Annotation $joinTable) {
    $this->joinTable = $joinTable;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\Annotation
   */
  public function getJoinTable() {
    if (!isset($this->joinTable)) {
      extract($this->help());
      
      $sourceTable = $sourceColumn = mb_strtolower($this->source->getName()); 
      $targetTable = $targetColumn = mb_strtolower($this->target->getName()); // da ist bei self-referencing ein alias
      $joinTableName = $sourceTable.'2'.$targetTable;
      
      if ($this->direction === self::SELF_REFERENCING && $sourceColumn === $targetColumn) {
        // falls hier kein alias ist, machen wir auf jeden Fall doctrine heile
        $targetColumn .= '2';
      }

      $this->joinTable =
          $joinTable($joinTableName,
                     $joinColumn($sourceColumn.'_'.$this->source->getIdentifier(), $this->source->getIdentifier(), 'cascade'),
                     $joinColumn($targetColumn.'_'.$this->target->getIdentifier(), $this->target->getIdentifier(), 'cascade')
                    );
    }
    return $this->joinTable;
  }
  
  /**
   * Setz den Namen der JoinTable-Annotation
   *
   * Achtung: erstellt + cached den JoinTable in der Klasse. Es sollte also vorher schon type + direction correct gesetzt sein
   * @chainable
   */
  public function setJoinTableName($name) {
    $this->getJoinTable()->setName($name);
    return $this;
  }
  
  /**
   * @param Psc\Doctrine\Annotation $relation
   */
  public function setRelation(Annotation $relation) {
    $this->relation = $relation;
    return $this;
  }
  
  /**
   * 
   * nur die Annotation für source
   * @return Psc\Doctrine\Annotation
   */
  public function getRelation() {
    if (!isset($this->relation)) {
      extract($this->help());
      
      $relationName = lcfirst($this->type);
      
      if ($this->targetSide === self::INVERSE) {
        $this->relation = $$relationName($this->target->getFQN(), $inversedBy($this->getTargetPropertyName()));
      } elseif ($this->targetSide === self::OWNING) {
        $this->relation = $$relationName($this->target->getFQN(), $mappedBy($this->getTargetPropertyName()));
      } else {
        $this->relation = $$relationName($this->target->getFQN());
      }
    }
    
    return $this->relation;
  }
  
  
  /**
   * Setzt das Cascade der Doctrine Relation (NICHT database-level)
   * 
   * Achtung dies erzeugt die Relation Annotation
   * @param array $cascades ein array von parametern die jeweils eine aktion bedeuten (persist|remove| etc)
   */
  public function setRelationCascade(Array $cascades) {
    $this->getRelation()->setCascade($cascades);
    return $this;
  }

  /**
   * Setzt den FETCH Type der Relation
   * 
   * Achtung dies erzeugt die Relation Annotation
   * @param string $type z.b. EXTRA_LAZY
   */
  public function setRelationFetch($type) {
    $this->getRelation()->setFetch($type);
    return $this;
  }
  
  /**
   * 
   * extract($this->help());
   * 
   * $manyToMany(...)
   * @return array
   */
  protected function help() {
    static $closures = NULL;
    
    if (!$closures)
      $closures = Annotation::getClosureHelpers();
    
    return $closures;
  }
  
  /**
   * @param Psc\Doctrine\Annotation $joinColumn
   */
  public function setJoinColumn(Annotation $joinColumn) {
    $this->joinColumn = $joinColumn;
    return $this;
  }
  
  /**
   * Achtung dies erzeugt die JoinColumn und sollte nur benutzt werdne, wenn die Relation schon korrekt gesetzt ist
   */
  public function setJoinColumnNullable($bool) {
    $this->nullable = $bool;
    $this->getJoinColumn()->setNullable($bool);
    return $this;
  }

  /**
   * Achtung dies erzeugt die JoinColumn und sollte nur benutzt werdne, wenn die Relation schon korrekt gesetzt ist
   */
  public function setJoinColumnName($name) {
    $this->getJoinColumn()->setName($name);
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\Annotation
   */
  public function getJoinColumn() {
    if (!isset($this->joinColumn)) {
      extract($this->help());
      
      if ($this->type === self::MANY_TO_ONE || $this->type == self::ONE_TO_ONE) {
        $name = NULL; // default nehmen
        //$name = mb_strtolower($this->source->getName()).'_'.$this->source->getIdentifier()
        
        $this->joinColumn = $joinColumn($name, // name
                                      $this->target->getIdentifier(), // referencedColumnName
                                      $this->getOnDelete()
                                     );
        $this->joinColumn->setNullable($this->nullable);
      }
    }
    
    return $this->joinColumn;
  }
  
  
  /**
   * @param array $orders dbField => ASC|DESC, dbField2 => ASC|DESC usw
   */
  public function setOrderBy(Array $orders) {
    //@OrderBy({"name" = "ASC"})
    $this->orderBy = Annotation::createDC('OrderBy')->setValue($orders);
    return $this;
  }
  
  /**
   * @param const $onDelete
   */
  public function setOnDelete($onDelete) {
    Code::value($onDelete, self::CASCADE, self::SET_NULL);
    $this->onDelete = $onDelete;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getOnDelete() {
    if (isset($this->onDelete)) {
      return $this->onDelete;
    }
    
    if ($this->isBidirectional()) {
      return self::CASCADE;
    } else {
      return self::SET_NULL;
    }
  }
  
  /**
   * @param bool $updateOtherSide
   */
  public function setUpdateOtherSide($updateOtherSide) {
    $this->updateOtherSide = $updateOtherSide;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getUpdateOtherSide() {
    return $this->updateOtherSide;
  }
  
  public function isTargetNullable() {
    return $this->nullable;
  }
  
  /**
   * @param bool $nullable
   * @chainable
   */
  public function setNullable($nullable) {
    $this->nullable = $nullable;
    return $this;
  }

  /**
   * @return bool
   */
  public function getNullable() {
    return $this->nullable;
  }


  public function addAnnotation(\Psc\Code\Annotation $annotation) {
    $this->otherAnnotations[] = $annotation;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function isWithoutInterface() {
    return $this->withoutInterface;
  }
  
  public function buildWithoutInterface() {
    $this->withoutInterface = TRUE;
    return $this;
  }
}
?>