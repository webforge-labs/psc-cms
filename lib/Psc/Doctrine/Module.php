<?php

namespace Psc\Doctrine;

use Psc\CMS\Project;
use Psc\PSC;
use Psc\Config;
use Psc\Code\Code;
use Webforge\Common\System\Dir;
use Psc\Doctrine\Helper as DoctrineHelper;
use Psc\Code\Event\Manager as PscEventManager;

use Doctrine\ORM\Configuration;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\Driver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;


/**
 * Doctrine Module
 *
 * - Das Doctrine Module hilft beim bootstrappen des gesamten Doctrine Apparates
 * - Es verwaltet die EntityMeta - Klassen
 * - normalisiert EntityNamen
 * - verwaltet EntityManager (mehrere) und Connections
 */
class Module extends \Psc\CMS\Module implements \Psc\Code\Event\Dispatcher {
  
  const EVENT_BOOTSTRAPPED = 'Psc.Doctrine.ModuleBootstrapped';
  
  const CACHE_APC = 'apc';
  const CACHE_ARRAY = 'array';
  
  /**
   * @var string ohne \ am Anfang
   */
  protected $entitiesNamespace;
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $entitiesPath;
  
  /**
   * @var Psc\Symfony\Module
   */
  protected $symfonyModule;
  
  protected $cache;
  protected $resultCache;
  
  protected $driverChain;
  
  protected $defaultAnnotationReader;
  protected $annotationDriver;
  
  /**
   * Ein Treiber um Entities einzeln zu laden
   *
   * $this->registerEntityClassesMetadataDriver();
   * $this->getEntityClassesMetadataDriver->addClass('Psc\Doctrine\TestEntities\Tag');
   *
   * @var Psc\Doctrine\MetadataDriver
   */
  protected $entityClassesMetadataDriver;
  
  
  /**
   * @var array
   */
  protected $entityMetas = array();
  
  /**
   * @var array
   */
  protected $entityManagers;

  /**
   * Doctrine\ORM\Configuration
   */
  protected $configuration;
  
  /**
   * Der Name der Config Variablen für die Verbindung
   *
   * wenn das Projekt tests = TRUE hat ist dies hier nicht "default" als default sondern "tests"
   * @var string
   */
  protected $connectionName;

  /**
   * @var string
   */
  protected $navigationNodeClass;
  
  /**
   * @var Psc\Code\Event\Manager
   */
  protected $manager;
  
  public function __construct(Project $project) {
    parent::__construct($project);
    
    $this->entitiesNamespace = Config::get('doctrine.entities.namespace') ?: $this->project->getNamespace().'\Entities';
    $this->manager = PSC::getEventManager();    
  }

  /**
   * Gibt den Namespace des Modules zurück
   * @return string
   */
  public function getNamespace() {
    return 'Doctrine';
  }

  /**
   * Gibt den Default-Namespace für Entities des Projektes zurück
   *
   * @return string
   */
  public function getEntitiesNamespace() {
    return $this->entitiesNamespace;
  }

  /**
   * Gibt die Klasse (den vollen Namen) eines Entities zurück
   * 
   * speaker => 'projectNamespace\Entities\Speaker'
   * oid => 'projectNamespace\Entities\OID'
   *
   * Dies wird z.B. für die Umwandlung von entity-bezeichnern in URLs in echte Klassen gebraucht.
   * Irreguläre Namen (sowas wie OID) können in $this->getEntityNames() eingetragen werden
   *
   * @param string $input kann ein Name in LowerCase sein, eine volle Klasse oder auch ein TabsContentItem2::getTabsResourceName() sein
   */
  public function getEntityName($input) {
    if (is_string($input)) {
      
      if (array_key_exists($input,$names = $this->getEntityNames())) {
        $name = $names[$input];
      } elseif (mb_strpos($input,'\\') === FALSE) {
        $name = \Webforge\Common\String::ucfirst($input);
      } else {
        $name = $input;
      }
      
      return Code::expandNamespace($name, $this->getEntitiesNamespace());
    }
    
    throw new \Psc\Exception('unbekannter Fall für getEntityName. Input ist: '.Code::varInfo($input));
  }

  /**
   * Gibt ein Mapping von lowercase-Namen zu Entity-Klassen-Namen zurück
   *
   * @return array
   */
  public function getEntityNames() {
    return $this->project->getConfiguration()->req(array('doctrine','entities','names'));
  }
  
  /**
   * Gibt ein Entity-Meta für ein Entity zurück
   *
   * ein EntityMeta enthält jede Menge Labels, URLs, und weitere Infos über das Entity im CMS (in verschiedenen Contexten)
   *
   * Ich habe lange überlegt, wo die "zentrale" Stelle für die EntityMetas sein soll und mich für das Modul entschieden, da es sowohl im DCPackage ist (und damit im Controller eines EntityServices)
   * sowohl auch in einer "Main" vorhanden sein muss (und man es damit injecten kann)
   * 
   * EntityMetas die hier erzeugt werden haben die ClassMetadata von Doctrine aus der übergebenen Connection bzw EntityManager
   * @param string|NULL|EntityManager|Doctrine\ORM\Mapping\ClassMetadata $classMetadataInjection der Manager nach dem nach der ClassMetadata gefragt wird angeben durch $con oder durch einen EntityManager, oder die ClassMetadata selbst
   */
  public function getEntityMeta($entityName, $classMetadataInjection = NULL) {
    $entityClass = $this->getEntityName($entityName); // normalize
    
    // cache
    if (!array_key_exists($entityClass, $this->entityMetas)) {
      if ($classMetadataInjection instanceof EntityManager) {
        $classMetadata = $classMetadataInjection->getClassMetadata($entityClass);
      } elseif ($classMetadataInjection instanceof \Doctrine\ORM\Mapping\ClassMetadata) {
        $classMetadata = $classMetadataInjection;
      } else {
        $classMetadata = $this->getEntityManager($classMetadataInjection)->getClassMetadata($entityClass);
      }
      
      // erstelle ein "doofes" EntityMeta ohne viele Infos
      $meta = new \Psc\CMS\EntityMeta($entityClass, $classMetadata, $entityName);
      $this->entityMetas[$entityClass] = $meta;
      
      $this->manager->dispatchEvent('Psc.Doctrine.initEntityMeta', (object) array('module'=>$this), $meta);
    }
    
    return $this->entityMetas[$entityClass];
  }
  
  
  
  /**
   * Lädt alle für Doctrine wichtigen Klassen und Objekte
   * 
   */
  public function bootstrap($bootFlags = 0x000000) {
    $src = $this->project->getSrc();
    
    /* Symfony auch laden */
    $this->symfonyModule = $this->project->getModule('Symfony')->bootstrap();

    if (!isset($this->configuration))
      $this->configuration = new Configuration();
    
    /* Custom Functions */
    $this->configuration->addCustomDatetimeFunction('month', 'Psc\Doctrine\Functions\Month');
    $this->configuration->addCustomDatetimeFunction('year', 'Psc\Doctrine\Functions\Year');
    $this->configuration->addCustomDatetimeFunction('day', 'Psc\Doctrine\Functions\Day');
    $this->configuration->addCustomStringFunction('soundex', 'Psc\Doctrine\Functions\Soundex');

    /* Annotations */
    // Register the ORM Annotations in the AnnotationRegistry
    // new: we always do treat doctrine loaded with composer, now
    AnnotationRegistry::registerFile(
      $this->project->getVendor()
        ->getFile('doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php')
    );
    //@TODO psc annotations laden AnnotationRegistry::registerAutoloadNamespace('Psc\Code\Compile\Annotations\\');

    if (!isset($this->driverChain))
      $this->driverChain = new MappingDriverChain();

    $this->driverChain->addDriver(
      $this->getAnnotationDriver(),
      $this->entitiesNamespace
    );
    $this->configuration->setMetadataDriverImpl($this->driverChain);

    // Metadatadriver für einzelne Entities
    $this->registerEntityClassesMetadataDriver(array(), 'Psc', $this->getDefaultAnnotationReader());      
    
    /* Ci-Ca-Caches */
    if (($cache = $this->project->getConfiguration()->get('doctrine.cache')) != NULL) {
      $this->useCache($cache);
    }
    $this->configuration->setMetadataCacheImpl($this->getCache());
    $this->configuration->setQueryCacheImpl($this->getCache());
    $this->configuration->setResultCacheImpl($this->getResultCache()); // das ist wichtig, damit dieselben abfragen pro result gecached werden
    
    $this->configuration->setProxyDir($src->sub('Proxies/')->create());
    $this->configuration->setProxyNamespace('Proxies');
    $this->configuration->setAutoGenerateProxyClasses(TRUE);
    
    $this->registerDefaultTypes();
    $this->registerCustomTypes();
    
    $this->dispatchBootstrapped();
    
    return $this;
  }
  
  /**
   * @param bool $resetConnection bedeutet hier das DBAL Object Connection. die PDO Connection wird wiederverwendet (wenn möglich)
   * @return Doctrine\ORM\EntityManager
   */
  public function getEntityManager($con = NULL, $reset = FALSE, $resetConnection = FALSE) {
    if (!isset($con)) $con = $this->getConnectionName();
    
    if (!isset($this->entityManagers[$con]) || $reset) {
      // wenn reset ist und wir noch eine offene connection haben, wollen wir diese nehmen
      if ($resetConnection) {                
        $connection = $this->getConnectionOptions($con);
        
        if (isset($this->entityManagers[$con])) { // benutzt die alte PDO low-level connection
          $dbalConnection = $this->entityManagers[$con]->getConnection();
          
          if ($dbalConnection->isTransactionActive()) {
            $dbalConnection->rollback();
          }
          
          $connection['pdo'] = $dbalConnection->getWrappedConnection();
        }
      
      } elseif ($reset && isset($this->entityManagers[$con])) {
        $connection = $this->entityManagers[$con]->getConnection(); // benutze das DBAL/Connection Object der "alten" Verbindung
      } else {
        $connection = $this->getConnectionOptions($con);
      }

      $entityManager = $this->entityManagers[$con] = EntityManager::create($connection, $this->getConfiguration());
      $platform = $entityManager->getConnection()->getDatabasePlatform();
      $platform->registerDoctrineTypeMapping('enum','string');
      
      if (($cset = $this->project->getConfiguration()->get(array('db',$con,'charset'),'utf8')) != NULL) {
        // take NAMES not SET CHARACTER SET
        // http://dev.mysql.com/doc/refman/5.1/de/charset-connection.html
        // set character set, sets the character_set_connection to the collation of the db (when this is wrong everything does not go well)
        $entityManager->getConnection()->query("SET NAMES '".$cset."'");
      }

      $this->manager->dispatchEvent('Psc.Doctrine.initEntityManager', (object) array('module'=>$this), $entityManager);
    }
    
    return $this->entityManagers[$con];
  }
  
  /**
   * Gibt die Credentials + Optionen für eine Connection zurück
   *
   * Credentials werden in der ProjektConfiguration eingetragen
   * @return array
   */
  public function getConnectionOptions($con) {
    if (!isset($con)) $con = $this->getConnectionName();
    $conf = $this->project->getConfiguration();
  
    $connectionOptions = array(
      'dbname' => $conf->req(array('db',$con,'database')),
      'user' => $conf->req(array('db',$con,'user')),
      'password' => $conf->req(array('db',$con,'password')),
      'host' => $conf->req(array('db',$con,'host')),
      'driver' => 'pdo_mysql',
    );
    
    return $connectionOptions;
  }
  
  /**
   * Gibt ein EntityRepository für ein Entity zurück
   *
   * wird keine name der Connection übergeben ($con), wird die aktuelle Verbindung genutzt (und somit der aktuelle EntityManager)
   * @return Psc\Doctrine\Repository
   */
  public function getRepository($entityClass, $con = NULL) {
    return $this->getEntityManager($con)->getRepository($entityClass);
  }
  
  /**
   * Registriert einen Custom Mapping Type für Doctrine
   * 
   * beim Registieren wird der Name der TypeClass umgewandelt. Jeder \\ wird gelöscht und der Gesamte FQN benutzt
   *
   * z. B.:
   * SerienLoader\Status => SerienLoaderStatus
   * 
   * @param $class unbedingt ohne \ davor
   */
  public function registerType($class) {
    $name = str_replace('\\','',$class);
    \Doctrine\DBAL\Types\Type::addType($name, $class);
    return $this;
  }

  /**
   * Registriert die Common Custom Mapping Types (fürs Psc-CMS)
   *
   */
  protected function registerDefaultTypes() {
    foreach (array('Psc.DateTime'=>'Psc\Doctrine\DateTimeType', // legacy wegen dem .
                   'PscDateTime'=>'Psc\Doctrine\DateTimeType', // das ist das neue "richtige" Format
                   'PscDate'=>'Psc\Doctrine\DateType'
                   ) as $name => $class) {
      \Doctrine\DBAL\Types\Type::addType($name, $class);
    }
    return $this;
  }
  
  protected function registerCustomTypes() {
    foreach ($this->project->getConfiguration()->get(array('doctrine','types'), array()) as $typeClass) {
      $this->registerType($typeClass);
    }
  }

  /**
   * Fügt einen weiteren MetadataDriver hinzu
   *
   * muss nach bootstrap() aufgerufen werden
   * MetadataDriver verwalten MetaDaten zu Entities. Dies sind die MetaDaten von Doctrine und haben mit den EntityMetas dieses Modules wenig zu tun.
   * in den Tests wird ein weiterer MetadataDriver (this->getEntityClassesMetadataDriver) benutzt um TestEntities zur Laufzeit laden zu können (da die Doctrine Treiber per Default alle alle alle Klassen nach Entities durchsuchen)
   * 
   * @param Doctrine\ORM\Mapping\Driver\Driver $driver
   * @param null|string $driverNamespace
   */
  public function registerMetadataDriver(\Doctrine\Common\Persistence\Mapping\Driver\MappingDriver $driver, $driverNamespace = NULL) {
    $this->driverChain->addDriver($driver, $driverNamespace);
    return $this;
  }
  
  /**
   * Gibt den Psc\Doctrine\MetadataDriver zurück
   *
   * in den Tests wird ein dieser MetadataDriver benutzt um TestEntities zur Laufzeit laden zu können (da die Doctrine Treiber per Default alle alle alle Klassen nach Entities durchsuchen)
   * 
   * es geht $this->getEntityClassesMetadataDriver()->addClass($FQN);
   * fertig
   *
   * @return Psc\Doctrine\MetadataDriver|NULL
   */
  public function getEntityClassesMetadataDriver() {
    return $this->entityClassesMetadataDriver;
  }
  
  /**
   * Erstellt den EntityClassesMetadataDriver
   *
   * mehrmals aufrufen ist nicht tragisch, jedoch wird dann natürlihc $classes und $namspace sowie $reader keinen effect haben
   * @see getEntityClassesMetadataDriver
   * @chainable
   */
  public function registerEntityClassesMetadataDriver(Array $classes = array(), $namespace = 'Psc', \Doctrine\Common\Annotations\Reader $reader = NULL) {
    if (!isset($this->entityClassesMetadataDriver)) {
      if (!isset($reader)) {
        $reader = $this->createAnnotationReader();
      }
      $this->entityClassesMetadataDriver = new \Psc\Doctrine\MetadataDriver($reader, NULL, $classes);
      $this->registerMetadataDriver($this->entityClassesMetadataDriver, $namespace);
    }
    
    return $this;
  }
  
  /**
   * Gibt einen neuen SimpleAnnotationReader zurück
   *
   * der Namespace Doctrine\ORM\Mapping wird automatisch hinzugefügt
   * es wird der interne Cache benutzt
   * @return Doctrine\Common\Annotations\SimpleAnnotationReader()
   */
  public function createAnnotationReader($simple = TRUE, Array $ignoredAnnotations = array()) {
    if ($simple) {
      $reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
      $reader->addNamespace('Doctrine\ORM\Mapping');
    } else {
      $reader = new \Doctrine\Common\Annotations\AnnotationReader();
    }

    foreach ($ignoredAnnotations as $ignore) {
      $reader->addGlobalIgnoredName($ignore);
    }

    $reader = new \Doctrine\Common\Annotations\CachedReader($reader, $this->getCache());

    return $reader;
  }

  /**
   * Ein (Simple-)Annotation Reader benutzt von Doctrine
   *
   * @v2: AnnotationReader
   */
  public function getDefaultAnnotationReader() {
    if (!isset($this->defaultAnnotationReader)) {
      $this->defaultAnnotationReader = $this->createAnnotationReader(FALSE, array('todo','TODO','compiled')); // nie simple: jetzt neu
    }
    
    return $this->defaultAnnotationReader;
  }

  /**
   * Der AnnotationDriver für Doctrine, Psc, Whatever
   *
   * @return Doctrine\ORM\Mapping\Driver\AnnotationDriver
   */
  public function getAnnotationDriver() {
    if (!isset($this->annotationDriver)) {
      $this->annotationDriver = new AnnotationDriver($this->getDefaultAnnotationReader(),
                                                     array((string) $this->getEntitiesPath())
                                                    );
    }
    
    return $this->annotationDriver;
  }
  
  /**
   * Gibt Pfad für den Namespace der Entities zurück
   *
   * @return Webforge\Common\System\Dir
   */
  public function getEntitiesPath() {
    if (!isset($this->entitiesPath))
      $this->entitiesPath = Code::namespaceToPath($this->entitiesNamespace, $this->project->getClassPath()->up());
    
    return $this->entitiesPath;
  }

  /**
   * @var string
   */
  public function getConnectionName() {
    if (!isset($this->connectionName)) {
      return $this->project->getTests() ? 'tests' : 'default';
    }
    
    return $this->connectionName;
  }
  
  /**
   * @param string
   */
  public function setConnectionName($con) {
    $this->connectionName = $con;
    return $this;
  }
  
  /**
   * Gibt den internen Cache für Queries + Meta zurück
   *
   * (wenn APC irgendwann mal so richtig stabil wäre, könnte man hier in Production einen APCCache benutzen)
   */
  public function getCache() {
    if (!isset($this->cache))
      $this->cache = new \Doctrine\Common\Cache\ArrayCache;
      
    return $this->cache;
  }
  
  /**
   * Overwrites the current cache
   */
  public function useCache($type = self::CACHE_APC) {
    if ($type === self::CACHE_APC) {
      $this->setCache(new \Doctrine\Common\Cache\ApcCache);
    } else {
      $this->setCache(new \Doctrine\Common\Cache\ArrayCache);
    }
  }
  
  public function setCache(\Doctrine\Common\Cache\Cache $cache) {
    $this->cache = $cache;
    return $this;
  }
  
  /**
   * Gibt den internen Cache für Results zurück
   *
   * @return Doctrine\Common\Cache\Cache
   */
  public function getResultCache() {
    if (!isset($this->resultCache)) 
      $this->resultCache = new \Doctrine\Common\Cache\ArrayCache;
      
    return $this->resultCache;
  }
  
  /**
   * Gibt die Doctrine Configuration zurück
   *
   * das ist nicht die Projekt-Konfiguration!
   * @return \Doctrine\ORM\Configuration
   */
  public function getConfiguration() {
    if (!isset($this->configuration)) {
      throw new \RuntimeException('Cannot getConfiguration(), bootstrap() should be called first.');
    }
    return $this->configuration;
  }

  /**
   * Gibt den ClassPath zurück in dem die Sourcen von doctrine liegen
   */
  public function getClassPath() {
    throw new \Psc\Exception('this is deprecated now');
  }
  
  public function getModuleDependencies() {
    return array('Symfony');
  }
  
  public function getDriverChain() {
    return $this->driverChain;
  }
  
  /**
   * Setzt den Cache für die EntityMetas zurück
   *
   * erzeugt dann beim nächsten Aufruf jedes EntityMeta neu
   */
  public function resetEntityMetas() {
    $this->entityMetas = array();
    return $this;
  }
  
  public function getEntityMetas() {
    return $this->entityMetas;
  }
  
  /**
   * @param Webforge\Common\System\Dir
   */
  public function setEntitiesPath(Dir $path) {
    $this->entitiesPath = $path;
    return $this;
  }

  /**
   * @param string $NavigationNodeClass
   */
  public function setNavigationNodeClass($navigationNodeClass) {
    $this->navigationNodeClass = $navigationNodeClass;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getNavigationNodeClass() {
    if (!isset($this->navigationNodeClass)) {
      $this->navigationNodeClass = $this->getEntityName('NavigationNode');
    }
    return $this->navigationNodeClass;
  }
  
  /**
   * @return Psc\Code\Event\Manager
   */
  public function getManager() {
    return $this->manager;
  }
  
  public function createDoctrinePackage() {
    return new DCPackage($this, $this->getEntityManager());
  }
}
?>