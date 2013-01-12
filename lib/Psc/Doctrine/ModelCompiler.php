<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;
use Psc\Data\Type\Type;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Code;

/**
 * @TODO addRelation in ein Objekt kapseln, sodass man die onDelete cascade oder propertyNames oder andere Identifier als 'id' konfigurieren kann
 * @TODO DocBlocks für Methoden (siehe entity bzw classbuilder)
 * @TODO Entity und TargetEntity müssten mit mehr meta-informationen versehen werden (z. B. identifier für addRelation)
 */ 
class ModelCompiler extends \Psc\SimpleObject {
  
  const NO_SET_META_GETTER =     0x000001;
  
  /**
   * Flags für den compile() aufruf
   *
   * können mit dem helper $flag() gesetzt werden (oder hier mit addFlag())
   * @var int
   */
  protected $flags = 0;
  
  /**
   * @var Psc\Doctrine\Module
   */
  protected $module;
  
  /**
   * @var Psc\Code\Generate\ClassWriter
   */
  protected $classWriter;
  
  /**
   * @var const|NULL
   * @see setOverwriteMode()
   */
  protected $overwrite;
  
  /**
   * @var string
   */
  protected $originalEntityName; // unsere EntityKlassen heißen ja immer z. b. compiledPerson das hier wäre dann nur "Person"
  
  /**
   * @var array
   */
  protected $languages = array();
  
  public function __construct(Module $module = NULL, ClassWriter $classWriter = NULL) {
    $this->classWriter = $classWriter; // wird dann als NULL an entityBuilder weitergegeben per Default
    $this->module = $module; // siehe createEntityBuilder
  }
  
  /**
   * Der Aktuelle EntityBuilder
   *
   * wird bei $entity() (ClosureHelper) erstellt
   */
  protected $entityBuilder;
  
  /**
   *
   * - $entity($name, $parentClass)
   *   erstellt das Entity mit ClassDocBlock und einigen Methoden aus dem Interface
   *
   * - $property($name, $type(), $flag1, $flag2, ...) // $flag kann z.b. $nullable sein
   *   erstellt ein Property mit dem Namen und dem DatenTyp $type. Erstellt Setter + Getter für dieses Property
   *   Properties, die in einer Relation sind, müssen nicht extra erstellt werden
   * - $setIdentifier($name)  setzt den Identifier der Klasse als string (wird für alle Relations benötigt die nicht 'id' als identifier haben)
   * - $relation($targetEntity, $relationType, $direction = EntityRelation::BIDIRECTIONAL, $whoIsOwningSide = NULL, $source = NULL)
   * - $oneToMany($targetEntity, $propertyNamePlural = NULL, $targetPropertyName = NULL, $settings = array())
   * - $manyToOne($targetEntity, $propertyNameSingular = NULL, $targetPropertyName = NULL, $settings = array())
   * - $manyToMany($targetEntity, bool|NULL $isOwningSide, $propertyNamePlural = NULL, $targetPropertyName = NULL, $settings = array())
   *
   *   Erstellt eine Relation zwischen dem aktuellen $entity() und $targetEntity. Ist $propertyName nicht angegeben wird $targetEntity zum Plural-Property gemacht (wenn Many-Seite)
   *   ist $isOwningSide NULL bei ManyToMany wird die Relation unidirektional angelegt
   *
   * - $flag(string $constant) die Klassenkonstante (nur der Name als String)
   *
   * HelpersHelpers
   * - $entityClass() erweitert einen Shortname zu einer Klasse
   * - $extends gibt eine Klasse zurück. Ist dies ein String ohne \ wird angenommen, dass dies ein EntityName ist
   * - $type() erstellt einen Type mit Type::create()
   * - $enumType(string $class) erstellt einen Doctrine Enum Type
   * - $targetMeta($entityName) erstellt eine EntityRelationMeta (als parameter für relation) um z. B. die Property-Namen zu ändern
   */
  public function getClosureHelpers() {
    $mc = $this;
    
    // helperHelpers
    $entityClass = function ($class) use ($mc) {
      if ($class instanceof GClass) return $class;
      $gclass = new \Psc\Code\Generate\GClass($class);
      if ($gclass->getNamespace() === NULL) 
        $gclass->setNamespace($mc->getDefaultNamespace()); // geht nicht weil argumente ja zuerst ausgewertet werden, dann ist entity() noch nicht gesetzt
      return $gclass;
    };
    
    $undefined = function () {
      return GParameter::UNDEFINED;
    };

    $type = function ($name) {
      if ($name instanceof \Psc\Data\Type\Type)
        return $name;
      
      return \Psc\Data\Type\Type::createArgs($name, array_slice(func_get_args(), 1));
    };
    

    //entity
    $entity = function ($entityName, \Psc\Code\Generate\GClass $baseEntity = NULL, $tableName = NULL) use ($mc) {
      $mc->setEntityBuilder($entityBuilder = $mc->createEntityBuilder($entityName));
      
      if (isset($baseEntity)) {
        $entityBuilder->setBaseEntity($baseEntity);
      } else {
        $entityBuilder->setBaseEntity(new \Psc\Code\Generate\GClass('Psc\CMS\AbstractEntity'));
      }
      
      if (isset($tableName)) {
        $entityBuilder->setTableName($tableName);
      }
      
      return $entityBuilder;
    };
    
    $getGClass = function () use ($mc) {
      return $mc->getEntityBuilder()->getGClass();
    };

    $relationTypeConstant = function ($relation) {
      switch ($relation) {
        case 'ManyToMany':
          return EntityRelation::MANY_TO_MANY;
        case 'OneToMany':
          return EntityRelation::ONE_TO_MANY;
        case 'ManyToOne':
          return EntityRelation::MANY_TO_ONE;
        case 'OneToOne':
          return EntityRelation::ONE_TO_ONE;
        
        default:
          throw new \InvalidArgumentException('String als relation ist nicht bekannt: '.$relation);
      }
    };
    
    $directionConstant = function ($direction) {
      switch($direction) {
        case 'bidirectional':
          return EntityRelation::BIDIRECTIONAL;
        case 'unidirectional':
          return EntityRelation::UNIDIRECTIONAL;
        case 'self':
        case 'self-referencing':
        case 'selfreferencing':
        case 'self_referencing':
          return EntityRelation::SELF_REFERENCING;
        
        default:
          throw new \InvalidArgumentException('String als direction ist nicht bekannt: '.$direction);
      }
    };
    
    // relations
    $targetMeta = function ($targetEntity, $propertyName = NULL) use ($entityClass) {
      if ($targetEntity instanceof EntityRelationMeta) {
        return $targetEntity;
      }
      
      $erm = new EntityRelationMeta($entityClass($targetEntity), 'target');
      
      if (isset($propertyName)) {
        $erm->setPropertyName($propertyName);
      }
      
      return $erm;
    };
    
    $sourceMeta = function ($source, $propertyName = NULL) use ($mc) {
      if ($source instanceof EntityRelationMeta) {
        return $source;
      }
      
      $erm = $mc->getEntityBuilder()->getEntityRelationMeta();
      $erm->setGClass($mc->getOriginalEntityClass());
      
      if (isset($propertyName)) {
        $erm->setPropertyName($propertyName);
      }

      return $erm;
    };
    
    $defaultSettings = function (Array $settings = array()) {
      $defaults = array(
        'updateOtherSide'=>TRUE,
        'bidirectional'=>TRUE
      );
      
      // $settings / $defaults == 0
      if (count($unknownSettings = array_diff(array_keys($settings), array_keys($defaults))) > 0) {
        throw new \InvalidArgumentException('Das Setting / Die Settings: '.implode(',', $unknownSettings).' sind nicht bekannt. Möglich sind: '.implode(",", array_keys($defaults)));
      }
      
      return (object) array_merge($defaults, $settings);
    };
    
    /* wir müssen hier etwas mit dem letzten parameter für createRelation rumtricksen
      da wir als propertyNamen hier nicht $this->compiledPersons sondern $this->persons erzeugen wollen */
    $OneToMany = $oneToMany = function ($targetEntity, $propertyNamePlural = NULL, $targetPropertyName = NULL, array $settings = array()) use ($mc, $entityClass, $sourceMeta, $targetMeta, $defaultSettings) {
      $settings = $defaultSettings($settings);
      
      return $mc->getEntityBuilder()->createRelation(
          $rt = EntityBuilder::RELATION_ONE_TO_MANY,
          $sourceMeta($targetPropertyName, $targetPropertyName),
          $targetMeta($targetEntity, $propertyNamePlural),
          $settings->bidirectional ? EntityBuilder::SIDE_INVERSE : NULL,
          $settings->updateOtherSide
      );
    };

    $ManyToOne = $manyToOne = function ($targetEntity, $propertyNameSingular = NULL, $targetPropertyName = NULL, Array $settings = array()) use ($mc, $entityClass, $sourceMeta, $targetMeta, $defaultSettings) {
      $settings = $defaultSettings($settings);

      return $mc->getEntityBuilder()->createRelation(
          $rt = EntityBuilder::RELATION_MANY_TO_ONE,
          $sourceMeta($targetPropertyName, $targetPropertyName), // der targetPropertyName gehört zu source, weil das Property von target in der Sourceclass ist
          $targetMeta($targetEntity, $propertyNameSingular),
          $settings->bidirectional ? EntityBuilder::SIDE_OWNING : NULL,
          $settings->updateOtherSide
      );
    };
    
    $ManyToMany = $manyToMany = function($targetEntity, $isOwningSide, $propertyNamePlural = NULL, $targetPropertyName = NULL, Array $settings = array()) use ($mc, $entityClass, $targetMeta, $sourceMeta, $defaultSettings) {
      $settings = $defaultSettings($settings);
      
      return $mc->getEntityBuilder()->createRelation(
          $rt = EntityBuilder::RELATION_MANY_TO_MANY,
          $sourceMeta($targetPropertyName, $targetPropertyName),
          $targetMeta($targetEntity, $propertyNamePlural),
          ($isOwningSide !== NULL ? ($isOwningSide ? EntityBuilder::SIDE_OWNING : EntityBuilder::SIDE_INVERSE) : NULL),
          $settings->updateOtherSide
      );
    };
    
    $relation = function ($targetEntity, $relationType, $direction = EntityRelation::BIDIRECTIONAL, $whoIsOwningSide = NULL, $source = NULL) use($sourceMeta, $targetMeta, $relationTypeConstant, $directionConstant) {
      return new EntityRelation($sourceMeta($source), $targetMeta($targetEntity), $relationTypeConstant($relationType), $directionConstant($direction), $whoIsOwningSide);
    };
    
    // builded 1 oder mehrere items
    $build = function () use ($mc) {
      foreach (func_get_args() as $item) {
        if ($item instanceof EntityRelation) {
          return $mc->getEntityBuilder()->buildRelation($item);
        } else {
          throw new \InvalidArgumentException('Dont Know how to build: '.Code::varInfo($item));
        }
      }
    };
    


    // constructor
    $constructor = function () use ($mc) {
      return $mc->getEntityBuilder()->generatePropertiesConstructor(func_get_args());
    };
    
    $argument = function ($propertyName, $default = GParameter::UNDEFINED, $propertyType = NULL) use ($mc, $type) {
      if (!$mc->getEntityBuilder()->hasProperty($propertyName)) {
        throw new \InvalidArgumentException("Das Property: '".$propertyName."'".' existiert nicht in der kompilierten Klasse: '.$mc->getEntityBuilder()->getGClass()->getName().'.'."\n".'Wenn das Property durch eine Relation hinzugefügt wurde, muss die Relation vor $argument() hinzugefügt und build() werden: $build($relation(...) benutzen');
      }
      
      $arg['property'] = $mc->getEntityBuilder()->getProperty($propertyName);
      if (isset($propertyType)) {
        $arg['property']->setType($type($propertyType));
      }
      
      if ($default !== GParameter::UNDEFINED) {
        $arg['default'] = $default;
      }
      
      return $arg;
    };
    
    
    
    // properties
    $property = function ($name, \Psc\Data\Type\Type $type) use ($mc) {
      $flags = 0x000000;
      foreach (array_slice(func_get_args(),2) as $flagSetter) {
        $flagSetter($flags);
      }
      
      $eb = $mc->getEntityBuilder();
      return $eb->createProperty($name, $type, $flags);
    };
    
    $isId = function () {
      return function (&$flags) {
        $flags |= \Psc\Doctrine\EntityBuilder::IDENTIFIER;
      };
    };
    
    $setIdentifier = function($name) use ($mc) {
      return $mc->getEntityBuilder()->setIdentifier($name);
    };
    
    $nullable = function () {
      return function (&$flags) {
        $flags |= \Psc\Doctrine\EntityBuilder::NULLABLE;
      };
    };

    $unique = function () {
      return function (&$flags) {
        $flags |= \Psc\Doctrine\EntityBuilder::UNIQUE;
      };
    };

    $i18n = function () {
      return function (&$flags) {
        $flags |= \Psc\Doctrine\EntityBuilder::I18N;
      };
    };
    
    $defaultId = function () use ($mc) {
      return $mc->getEntityBuilder()->createDefaultId();
    };
    
    // helpers
    $extends = function ($class) use ($entityClass) {
      if ($class instanceof \Psc\Code\Generate\GClass) return $class;
      if (mb_strpos($class,'\\')) {
        return new \Psc\Code\Generate\GClass($class);
      } else {
        return $entityClass($class);
      }
    };
    
    $enumType = function ($class) {
      return new \Psc\Data\Type\DCEnumType(new GClass($class));
    };
    
    $flag = function ($constant) use ($mc) {
      $constant = mb_strtoupper($constant);
      if (!defined($c = 'Psc\Doctrine\ModelCompiler::'.$constant)) {
        throw new \InvalidArgumentException('Konstante: '.$c.' ist unbekannt');
      }
      
      $mc->addFlag(constant($c));
      return $mc->getFlags();
    };
    
    return compact(
                   'entity', 'entityClass', 'extends', 'getGClass',
                   'property', 'enumType', 'flag', 'nullable', 'unique', 'type', 'undefined', 'i18n',
                   'constructor','argument',
                   'setIdentifier','isId', 'defaultId',
                   
                   'relation','addRelation', 'build',
                   'oneToMany','manyToMany','manyToOne',
                   'OneToMany','ManyToMany','ManyToOne',
                   'targetMeta','sourceMeta', 'defaultSettings','directionConstant','relationTypeConstant'
                   );
  }
  
  /**
   * @return EntityBuilder
   */
  public function compile(EntityBuilder $eb) {
    if ($eb !== $this->entityBuilder)
      throw new \Psc\Exception('Eigentlich sollten Parameter1 und $this->eb identisch sein. Wurde $entity() (setEntityBuilder) benutzt?');

    /* in den Parametern können Relations sein mit $relation erstellt, die wir auch noch hinzufügen wollen */
    foreach (array_slice(func_get_args(),1) as $item) {
      if ($item instanceof EntityRelation) {
        $this->entityBuilder->buildRelation($item);
      }
    }

    $this->entityBuilder->createDefaultClassDocBlock();
    $this->entityBuilder->createGetEntityNameMethod();
    if (!($this->flags & self::NO_SET_META_GETTER)) $this->entityBuilder->buildSetMetaGetter();
    $this->entityBuilder->generateDocBlocks();
    //$this->entityBuilder->createMethod('export',array(), array());
    
    $docBlock = $this->entityBuilder->getGClass()->getDocBlock();
    $docBlock->removeAnnotation(Annotation::createDC('Entity')->getAnnotationName());
    $docBlock->removeAnnotation(Annotation::createDC('Table')->getAnnotationName());
    $docBlock->addAnnotation(Annotation::createDC('MappedSuperclass'));
    
    $this->entityBuilder->write(NULL, $this->overwrite);
    return $this->entityBuilder;
  }
  
  public function addFlag($flag) {
    $this->flags |= $flag;
    return $this;
  }
  
  public function compileRepository(File $file = NULL, $overwrite = NULL) {
    $this->entityBuilder->writeRepository($file, $overwrite);
    return $this;
  }
  
  public function createEntityBuilder($entityName) {
    if ($entityName instanceof \Psc\Code\Generate\GClass) {
      $entityClass = $entityName;
      $entityName = $entityClass->getClassName();
      $entityClass->setClassName('Compiled'.ucfirst($entityName));
    } elseif(mb_strpos($entityName, '\\') !== FALSE) {
      $parts = explode('\\', $entityName);
      $entityName = array_pop($parts);
      array_push($parts, 'Compiled'.ucfirst($entityName));
      $entityClass = implode("\\", $parts);
      // wird dann von entityBuilder expanded zu default Namespace
    } else {
      $entityClass = 'Compiled'.ucfirst($entityName);
      // wird dann von entityBuilder expanded zu default Namespace
    }
    
    $this->originalEntityName = $entityName;      
    $eb = new EntityBuilder($entityClass, $this->module, $this->classWriter, NULL, $this->getLanguages());
    $file = $eb->inferFile($eb->getGClass());
    if ($file->exists()) $file->delete();
    
    return $eb;
  }
  
  public function getOriginalEntityClass() {
    return new GClass($this->getDefaultNamespace().'\\'.$this->originalEntityName);
  }
  
  public function setEntityBuilder(EntityBuilder $eb) {
    $this->entityBuilder = $eb;
    return $this;
  }
  
  public function getEntityBuilder() {
    return $this->entityBuilder;
  }
  
  public function getDefaultNamespace() {
    $eb = new EntityBuilder('notRelevant', $this->module, $this->classWriter);
    return $eb->getDefaultNamespace();
  }
  
  public function setOverwriteMode($bool) {
    if ($bool)
      $this->overwrite = EntityBuilder::OVERWRITE;
    else 
      $this->overwrite = NULL;
    return $this;
  }
  
  public function getFlags() {
    return $this->flags;
  }
  
  /**
   * @param array $languages
   * @chainable
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
}
?>