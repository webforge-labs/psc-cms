<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\GProperty;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GMethod;
use Psc\Code\Generate\ClassBuilderProperty;
use Psc\Code\Code;
use Webforge\Common\System\File;
use Psc\Inflector;
use Psc\Data\Type\Type;
use Psc\Data\Type\I18nType;
use Psc\Data\Type\CollectionType;
use Psc\Data\Type\PersistentCollectionType;
use Psc\Data\Type\ObjectType;
use Psc\Data\Type\EntityType;
use Psc\Data\Type\CodeExporter;

/**
 * 
 * @TODO setter von bool-properties könnte zu bool casten
 * @TODO is* Funktionen für bools
 * @TODO DocBlocks für Getters / Setters / Methods
 * @TODO JSONFields
 * @TODO export
 * @TODO name + "echter" name CompiledTag vs Tag (z.b. bei getEntityName()) und bei allem anderen sonst auch .. (mehr power für settings von außen)
 */
class EntityBuilder extends \Psc\Code\Generate\ClassBuilder {
  
  const WITHOUT_DOCBLOCK = 1;
  
  const NULLABLE = 2;
  
  const IDENTIFIER = 4;
  
  const UNIQUE = 8;
  
  const I18N = 16;
  const WITHOUT_GETTER = 32;
  const WITHOUT_SETTER = 64;
  
  const OVERWRITE = '__overwrite';
  
  const RELATION_MANY_TO_MANY = 'ManyToMany';
  
  const RELATION_ONE_TO_MANY = 'OneToMany';
  
  const RELATION_MANY_TO_ONE = 'ManyToOne';
  
  const RELATION_ONE_TO_ONE = 'OneToOne';
  
  const SIDE_OWNING = 'owning';
  
  const SIDE_INVERSE = 'inverse';
  
  /**
   * @var Psc\Doctrine\Module
   */
  protected $module;
  
  /**
   * @var Psc\Code\Generate\ClassWriter
   */
  protected $classWriter;
  
  protected $writtenFile;
  
  /**
   * @var Psc\Code\Generate\GClass
   */
  protected $baseEntity;
  
  protected $withRepository = true;
  
  /**
   * @var Psc\Doctrine\TypeExporter
   */
  protected $dcTypeExporter;
  
  protected $repositoryGClass;
  
  protected $repositoryBuilder;
  
  protected $identifier;
  
  protected $alreadyBuiltRelations = array();
  
  protected $forcedMetaProperties = array();
  
  /**
   * var string@
   */
  protected $tableName;
  
  /**
   * @var array
   */
  protected $languages;
  
  /**
   * @param string $entityName der name des Entities ohne den EntitiesNamespace davor
   */
  public function __construct($entity, Module $doctrineModule = NULL, ClassWriter $classWriter = NULL, TypeExporter $DCTypeExporter = NULL, Array $languages = array()) {
    $this->module = $doctrineModule ?:  \Psc\PSC::getProject()->getModule('Doctrine');
    
    if (!($entity instanceof GClass)) {
      $entity = new GClass($this->getDefaultNamespace().'\\'.$entity);
    }
    parent::__construct($entity);
    $this->class->setAbstract(TRUE);
    
    $this->classWriter = $classWriter ?: new ClassWriter();
    $this->dcTypeExporter = $DCTypeExporter ?: new TypeExporter();
    $this->setLanguages($languages);
  }
  
  /**
   * @param Type $type wenn nicht gesetzt wird Psc\Data\Type\StringType genommen
   */
  public function createProperty($name, Type $type = NULL, $flags = 0, $upperCaseName = NULL, $columnAlias = NULL) {
    if (!isset($type)) $type = Type::create('String');
    
    if ($flags & self::I18N) {
      // wir nennen unser property um:
      $originalName = $name;
      $originalUpperCaseName = $upperCaseName;
      $name = 'i18n'.($upperCaseName ?: ucfirst($name));
      $upperCaseName = NULL;
      $type = new I18nType($type, $this->getLanguages());
      $flags |= self::WITHOUT_DOCBLOCK;
    }
    
    $property = $this->addProperty($name)
      ->setType($type);

    if ($flags & self::NULLABLE) {
      $property->setNullable(TRUE);
    }
      
    if (isset($upperCaseName)) {
      $property->setUpcaseName($upperCaseName);
    }
    
    if (!($flags & self::WITHOUT_DOCBLOCK)) {
      $column = array('type'=>$this->dcTypeExporter->exportType($type));
      
      if ($flags & self::NULLABLE) {
        $column['nullable'] = true;
      }
      
      if ($flags & self::UNIQUE) {
        $column['unique'] = true;
      }

      if ($columnAlias) {
        $column['name'] = $columnAlias;
      }
    
      $property
        ->createDocBlock()
          ->addAnnotation(Annotation::createDC('Column', $column))
        ;
    }
    
    if ($flags & self::IDENTIFIER) {
      $this->setIdentifier($name);
    }
    
    if (!($flags & self::WITHOUT_GETTER))
      $this->generateGetter($property, NULL, self::INHERIT); // per default: schreibe methoden auch von interfaces, etc
      
    if (!($flags & self::WITHOUT_SETTER))
      $this->generateSetter($property, NULL, self::INHERIT);
    
    if ($flags & self::I18N) {
      if ($type instanceof \Psc\Data\Type\DefaultValueType) {
        if ($type->hasScalarDefaultValue()) {
          $property->setDefaultValue($type->getDefaultValue());
        } else {
          $this->appendConstructorBody(array(
            sprintf('$this->%s = %s;', $property->getName(), $this->codeWriter->exportValue($type->getDefaultValue()))
          ));
        }
      }
      
      $this->setForcedMetaProperty($property, TRUE);
      
      // wir fügen noch einiges hinzu
      $this->generateI18nAPI($property, $originalName, $flags, $originalUpperCaseName);
    }
    
    return $property;
  }
  
  /**
   * Erstellt die i18nAPI für ein i18nProperty
   *
   * i18nPropertyName ist title:
   * 
   * erstellt einen virtuellen Getter für den OriginalNamen des Properties (getTitle($lang))
   * erstellt einen virtuellen Setter für den OriginalNamen des Properties (setTitle($value, $lang))
   *
   * setI18nTitle(Array $titles);
   * getI18nTitle() -> array
   * gibt es schon
   *
   * zusätzlich müssen wir die Properties (welche nicht im MetaSet sind) erstellen:
   * titleDe, titleEn, usw je nach $this->languages
   * alle ohne getter
   */
  public function generateI18nAPI(ClassBuilderProperty $property, $originalName, $originalFlags = 0x000000, $originalUpperCaseName = NULL) {
    $originalType = $property->getType()->getType();
    
    $properties = array();
    foreach ($this->getLanguages() as $lang) {
      $properties[$lang] = $this->createProperty(
                    $originalName.ucfirst($lang),
                    $originalType, // des inner types
                    // vererbe vom type, aber nur nullable+unique, alle anderen flags maskieren, die virtuelleln properties haben keine setter/getter und keinen docblock
                    ($originalFlags & (self::NULLABLE | self::UNIQUE)) | self::WITHOUT_GETTER | self::WITHOUT_SETTER, 
                    ($originalUpperCaseName) ? $originalUpperCaseName.ucfirst($lang) : NULL
                  );
      $this->setForcedMetaProperty($properties[$lang], FALSE); // nicht in die meta exportieren, weil wir sie da nicht wollen, das i18n-property schon!
    }
    
    // wir müssen den initialisierungs code für den i18n array in den getter injecten
    $initCode = array();
    $initCode[] = sprintf('if (count($this->%s) == 0) {', $property->getName());
    foreach ($this->getLanguages() as $lang) {
    $initCode[] = sprintf('  $this->%s[\'%s\'] = $this->%s;', $property->getName(), $lang, $properties[$lang]->getName());
    }
    $initCode[] = '}';
    $this->getMethod($property->getGetterName())->beforeBody($initCode);
    
    // wir müssen den setter für den i18n Array injecten (der soll die virtuellen properties setzen)
    $updateCode = array();
    foreach ($this->getLanguages() as $lang) {
      $updateCode[] = sprintf('$this->%s = $this->%s[\'%s\'];', $properties[$lang]->getName(), $property->getName(), $lang);
    }
    $this->getMethod($property->getSetterName())->insertBody($updateCode, -1); // vor: return $this;
    
    
    // dann müssen wir getField
    $getCode = array();
    $getCode[] = sprintf('$title = $this->%s();', $property->getGetterName());
    $getCode[] = 'return $title[$lang];';
    
    $this->addMethod(new GMethod(
      'get'.($originalUpperCaseName ?: ucfirst($originalName)),
      array(new GParameter('lang', NULL)),
      $getCode
    ));
    
    
    // und setField bauen
    $setCode = array();
    // wir könnten uns noch überlegen bei else: eine exception zu schmeissen?
    foreach ($this->getLanguages() as $key=>$lang) {
      if ($key === 0) {
        $setCode[] = sprintf('if ($lang === \'%s\') {', $lang);
      } else {
        $setCode[] = sprintf('} elseif ($lang === \'%s\') {', $lang);
      }
    
      // setze klassenVariable und i18nVariable auf den neuen Wert
      $setCode[] = sprintf('  $this->%s = $this->%s[\'%s\'] = $value;', $properties[$lang]->getName(), $property->getName(), $lang);
    }
    $setCode[] = '}';
    $setCode[] = 'return $this;';

    $this->addMethod(new GMethod(
      'set'.($originalUpperCaseName ?: ucfirst($originalName)),
      array(new GParameter('value', $originalType->getPHPHint()), new GParameter('lang', NULL)),
      $setCode
    ));
  }
  
  /**
   * @return EntityRelationMeta
   */
  public function getEntityRelationMeta() {
    $erm = new EntityRelationMeta($this->class, 'source', EntityRelationMeta::SINGLE_VALUED);
    if ($this->getIdentifier() != NULL)
      $erm->setIdentifier($this->getIdentifier());
    return $erm;
  }
  
  /**
   * Legacy Interface für den ModelCompiler (für den ersten Wurf)
   * 
   * erstellt eine Relation und ruft direkt buildRelation auf
   * @return EntityRelation
   */
  public function createRelation($relationType, EntityRelationMeta $source, EntityRelationMeta $target, $side = NULL, $updateOtherSide = true) {
    Code::value($relationType, self::RELATION_MANY_TO_MANY, self::RELATION_MANY_TO_ONE, self::RELATION_ONE_TO_MANY, self::RELATION_ONE_TO_ONE);
    Code::value($side, self::SIDE_OWNING, self::SIDE_INVERSE, NULL);
    
    // Parameter übersetzen in constructor parameter von relation
    if ($side !== NULL) {
      $direction = EntityRelation::BIDIRECTIONAL;
      $whoIsOwningSide = $side === self::SIDE_OWNING ? EntityRelation::SOURCE : EntityRelation::TARGET;
    } else {
      $direction = EntityRelation::UNIDIRECTIONAL;
      $whoIsOwningSide = NULL;
    }
    
    $relation = new EntityRelation($source, $target, $relationType, $direction, $whoIsOwningSide);
    $this->buildRelation($relation);
    
    return $relation;
  }
  
  public function buildRelation(EntityRelation $relation) {
    if (in_array($relation, $this->alreadyBuiltRelations)) {
      return $this;
    }
    
    // zuerst müssen wir ein Property in dieser Klasse (die source ist), erstellen
    $property = $this->createProperty($relation->getSourcePropertyName(),
                                      $relation->getSourcePropertyType(),
                                      self::WITHOUT_DOCBLOCK,
                                      $relation->getSourceMethodName() // gibt automatisch singular / plural zurück
                                     );
    
    // für diese fälle müssen wir sicherstellen, dass auf jeden Fall ein setter in dieser klasse generiert wird,
    // damit das relation interface sich da injecten kann
    if ($relation->getType() === EntityRelation::MANY_TO_ONE || $relation->getType() === EntityRelation::ONE_TO_ONE) {
      $this->generateSetter($property, NULL, self::INHERIT);
    }
    
    /* Property Annotations erstellen */
    $relation->addAnnotationsForSource($property->createDocBlock());
    
    $this->buildRelationInterface($relation);
    
    // initialisatoren für die collection dem constructor hinzufügen
    if ($relation->getTarget()->isCollectionValued()) {
      $this->appendConstructorBody(Array(
        '$this->'.$relation->getSourcePropertyName().' = new \Psc\Data\ArrayCollection();'
      ));
    }
    
    $this->alreadyBuiltRelations[] = $relation;
    
    return $this;
  }
  
  /**
   * 
   * zu beachten:
   * 
   * Aufrufe auf der InverseSide updaten niemals die Connection!
   * Aurufe auf der OwningSide updated die Collection der InverseSide "in Memory"
   * (im moment nur hinzufügen, nicht entfernen bei ManyToOne)
   * 
   * generell könnten wir mal überlegen ob wir im relation-Interface auf beiden Seiten sowas bauen:
   * function addUser(User $user, $updateOtherSide = TRUE) {
   *   //..
   *   if ($updateOtherSide) {
   *     $user->addProduct($this, FALSE); // vermeide recursion durch flag
   *   }
   * }
   * 
   * dadurch dass wir jetzt das RelationInterface immer auto generieren habe ich nicht mehr so Angst davor dies so zu bauen
   */
  protected function buildRelationInterface(EntityRelation $relation) {
    $interface = new EntityRelationInterfaceBuilder($relation);
    $interface->buildSource($this->class);
    
    return $this;
  }
  
  /**
   * 
   * - numerische auto-increment id Feldname: "id"
   * - getIdentifier
   * - setIdentifier (jetzt neu wegen delete)
   * - getId
   */
  public function buildDefault() {
    $this->setBaseEntity(new GClass('Psc\Doctrine\Object'));
    
    $this->createGetEntityNameMethodException();
    $this->createDefaultId();
    $this->createDefaultClassDocBlock();
    return $this;
  }
  
  /**
   * 
   */
  public function buildDefaultV2() {
    $baseClass = clone $this->class;
    $baseClass->setClassName('Compiled'.$baseClass->getClassName());
    $this->setBaseEntity($baseClass);
    
    $this->class->setAbstract(FALSE);
    $this->createContextLabelMethod();
    $this->createGetEntityNameMethod();
    $this->createDefaultClassDocBlock();
    return $this;
  }
  
  /**
   * Erstellt eine Funktion die für das Entity eine SetMeta mit allen Properties zurückgibt
   * 
   * @chainable
   */
  public function buildSetMetaGetter(CodeExporter $codeExporter = NULL) {
    if (!isset($codeExporter)) $codeExporter = new CodeExporter();
    $code = array();
    
    $code[] = 'return new \Psc\Data\SetMeta(array(';
    foreach ($this->getProperties() as $property) {
      if ($property->getWasElevated()) continue;
      if (!$this->isMetaProperty($property)) continue;

      if ($property->getType() === NULL)
        throw new EntityBuilderException('Property '.$property->getName().' hat keinen Typ!');
      
        
      $code[] = sprintf("  '%s' => %s,", $property->getName(), $codeExporter->exportType($property->getType()));
    }
    $code[] = '));';
    
    $this->createMethod(
      'getSetMeta',
      array(),
      $code,
      GMethod::MODIFIER_STATIC | GMethod::MODIFIER_PUBLIC
    );
    return $this;
  }
  
  /**
   * Soll dsa Property z.b. im SetMetaGetter genannt werden?
   */
  public function isMetaProperty($property) {
    if (array_key_exists($property->getName(), $this->forcedMetaProperties)) return $this->forcedMetaProperties[$property->getName()];
    
    return TRUE;
  }
  
  public function createGetEntityNameMethod() {
    $this->class->createMethod('getEntityName', array(), 
      sprintf("return '%s';",
              $this->class->getFQN()) // das hier muss  der "echte" name sein
    );
  }
  
  public function createContextLabelMethod() {
    $this->class->createMethod('getContextLabel', array(new GParameter('context', NULL, \Psc\CMS\Entity::CONTEXT_DEFAULT)),
    array(
      "/*",
      "if (\$context === self::CONTEXT_DEFAULT) {",
      "  return parent::getContextLabel(\$context);",
      "}",
      '*/',
      "return parent::getContextLabel(\$context);",
    ));
  }
  
  public function createGetEntityNameMethodException() {
    $this->class->createMethod('getEntityName', array(), 
      sprintf("throw new \Psc\Exception('getEntityName() überschreiben. Denn hier würde nur %1\$s stehen.');\nreturn '%1\$s';",
              $this->class->getFQN()) // das hier muss  der "echte" name sein
    ); 
  }
  
  public function createDefaultId() {
    $this->addProperty('id')
        ->setType(Type::create('Id'))
        ->createDocBlock()
          ->addAnnotation(Annotation::createDC('Id'))
          ->addAnnotation(Annotation::createDC('GeneratedValue'))
          ->addAnnotation(Annotation::createDC('Column',array('type'=>'integer')))
    ;
    $this->generateGetter($this->getProperty('id'));
    $this->generateGetter($this->getProperty('id'),'getIdentifier', self::INHERIT); // weil uns da gerade die abstract method getIdentifier reinfunkt (fixme)
    $this->generateSetter($this->getProperty('id'),'setIdentifier', self::INHERIT); 
    
    $this->identifier = 'id';
    
    return $this;
  }
  
  public function createDefaultClassDocBlock() {
    $entityProperties = array();
    if ($this->withRepository) {
      $entityProperties['repositoryClass'] = $this->getRepositoryGClass()->getFQN();
    }
    
    $docBlock = $this->class->getDocBlock(TRUE); // autocreate aber get, damit wir vorher schon annotations hinzufügen können
    
    $entity = Annotation::createDC('Entity', $entityProperties);
    if (!$docBlock->hasAnnotation($entity->getAnnotationName())) {
      $docBlock->addAnnotation($entity);
    }
      
    $table = Annotation::createDC('Table', array('name'=>$this->getTableName()));
    if (!$docBlock->hasAnnotation($table->getAnnotationName())) {
      $docBlock->addAnnotation($table);
    }
    
    return $this;
  }
  
  /**
   * 
   * 
   * wird die Datei nicht übergeben, wird diese in den EntitiesPath des Modules geschrieben ($this->getGClass()->getClassName().php)
   * auch wenn $this->withRepository gesetzt ist, schreibt dies nur das Entity (wegen overwrite)
   * 
   * @return File
   */
  public function write(File $file = NULL, $overwrite = NULL) {
    $this->classWriter->setClass($this->class);  
    $this->classWriter->addImport(new GClass('Psc\Data\ArrayCollection'));
    $this->classWriter->setUseStyle('lines');
    
    $this->classWriter->addImport(new GClass('Doctrine\ORM\Mapping'),'ORM');
    
    if (!isset($file)) {
      $file = $this->inferFile($this->class);
    }
    $this->classWriter->write($file, array(), $overwrite);
    $this->classWriter->syntaxCheck($file);
    
    $this->writtenFile = $file;
    return $file;
  }
  
  public function inferFile(GClass $class) {
    if ($class->getNamespace() === $this->module->getEntitiesNamespace()) {
      $file= $this->module->getEntitiesPath()->getFile($class->getClassName().'.php');
    } else {
      $file = $this->module->getProject()->getClassFile($class);
    }
    
    return $file;
  }
  
  /**
   * @return File
   */
  public function writeRepository(File $file = NULL, $overwrite = NULL) {
    $gClass = $this->getRepositoryGClass();
    
    $this->classWriter->setClass($gClass);
    if (!isset($file)) {
      $autoLoadRoot = $this->module->getEntitiesPath()
        ->sub(
          str_repeat(
            '../',
            count(explode('\\', $this->module->getEntitiesNamespace()))
          )
        )
        ->resolvePath();

      $file = Code::mapClassToFile($gClass->getFQN(), $autoLoadRoot);
    }

    $this->classWriter->write($file, array(), $overwrite);
    $this->classWriter->syntaxCheck($file);
    
    return $file;
  }
  
  /**
   * @return GClass
   */
  public function getBaseEntity() {
    return $this->baseEntity;
  }
  
  public function setBaseEntity(GClass $gClass) {
    $this->baseEntity = $gClass;
    $this->setParentClass($this->baseEntity);
    return $this;
  }
  
  public function getEntityName() {
    return $this->gClass->getClassName();
  }
  
  public function getEntityClass() {
    return $this->gClass->getFQN();
  }
  
  public function getRepositoryGClass() {
    if (!isset($this->repositoryGClass)) {
      $this->repositoryGClass = new GClass($this->getEntityName().'Repository');
      $this->repositoryGClass->setNamespace($this->class->getNamespace());
      $this->repositoryGClass->setParentClass(new GClass('Psc\Doctrine\EntityRepository'));
      
      //$this->repositoryBuilder = new ClassBuilder($this->repositoryGClass);
      //$this->repositoryBuilder->setParentClass(new GClass('Psc\Doctrine\EntityRepository'));
    }
    return $this->repositoryGClass;
  }
  
  public function getDefaultNamespace() {
    return $this->module->getEntitiesNamespace();
  }
  
  /**
   * ist erst nach write() gesetzt
   */
  public function getWrittenFile() {
    return $this->writtenFile;
  }
  
  public function getIdentifier() {
    return $this->identifier;
  }
  
  /**
   * @param string $property
   */
  public function setIdentifier($property) {
    $this->identifier = $property;
    return $this;
  }
  
  /**
   * param string $tableName@
   */
  public function setTableName($tableName) {
    $this->tableName = $tableName;
    return $this;
  }
  
  /**
   * return string@
   */
  public function getTableName() {
    if (!isset($this->tableName)) {
      return \Doctrine\Common\Util\Inflector::tableize($this->getEntityName()).'s';
    }
    return $this->tableName;
  }
  
  /**
   * @param array $languages
   */
  public function setLanguages(Array $languages) {
    $this->languages = $languages;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getLanguages() {
    return $this->languages;
  }
  
  public function setForcedMetaProperty($property, $force = TRUE) {
    $this->forcedMetaProperties[$property->getName()] = $force;
    return $this;
  }
}
?>