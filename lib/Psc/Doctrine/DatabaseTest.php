<?php

namespace Psc\Doctrine;

use Psc\PSC;
use Psc\Doctrine\Helper as DoctrineHelper;

/**
 * Use DatabaseTestCase instead of this class
 *
 * @backupStaticAttributes disabled
 */
class DatabaseTest extends \Psc\Code\Test\NativeDatabaseTest {

  /**
   * @var string
   */
  protected $con = NULL;
  
  /**
   * @var Psc\Doctrine\Module
   */
  protected $module;
  
  /**
   * @var Doctrine\ORM\EntityManager
   */
  protected $em;
  
  /**
   * @var Psc\Doctrine\FixturesManager
   */
  protected $dcFixtures;
  
  
  protected $sm;
  
  public function setUp() {
    $this->configure();
    
    $this->module = $this->getModule('Doctrine'); 
    $this->module->setConnectionName($con = $this->configuration->req(array('doctrine','connectionName'))); // dsa ist nur für doofe tests die entityManager reset mit leerem Parameter aufrufen
    
    // hier ist getEntityManager nochmal explizit mit $con
    // erzeuge auf jeden Fall eine neue Instanz eines EntityManagers
    $this->em = $this->module->getEntityManager($con, $reset = TRUE, $resetConnection = TRUE);
    
    // für nativeDatabaseTest
    $this->configuration->set(array('database'), $this->em->getConnection()->getDatabase());

    $this->sm = $this->em->getConnection()->getSchemaManager();

    /* Datenbank für native Test setzen */
    $this->configuration->setDefault(array('database'), $this->em->getConnection()->getDatabase());
    
    parent::setUp();
    
    // $this->fixtures ist schon von nativedatabasetest belegt
    $this->dcFixtures = new \Psc\Doctrine\FixturesManager($this->em);
    $this->setUpFixtures();
  }
  
  public function tearDown() {
    //$this->em->clear();
    parent::tearDown();
  }
  
  public function configure() {
    parent::configure();
    
    $this->assertNotEmpty($this->con,'Bitte $this->con VOR parent::configure setzen');
    
    // für DatabaseTest
    $this->configuration->set(array('doctrine','connectionName'), $this->con);
  }
  
  public function setUpFixtures() {
    // overload this
    if (\Psc\Code\Code::isTraversable($this->fixtures)) {
      foreach ($this->fixtures as $fixture) {
        $this->dcFixtures->add($fixture);
      }
    }
  }

  protected function onNotSuccessfulTest(\Exception $e) {
    if (isset($this->em) && $this->em->getConnection()->isTransactionActive()) {
      $this->em->getConnection()->rollback();
    }
    
    parent::onNotSuccessfulTest($e);
  }
  
  public function getEntityManagerMock() {
    return $this->doublesManager->createEntityManagerMock($this->module);
  }
  
  /* Assertions */
  public function assertRowsNum($entityName, $expectedNum) {
    $this->assertEquals($expectedNum, $actualNum = $this->getRowsNum($entityName),
                        sprintf('TableRows von %s expected: %d actual: %d',
                                $entityName, $expectedNum, $actualNum));
  }
  
  /* HELPERS */
  
  /**
   * @return int
   */
  public function getRowsNum($entityName) {
    $entityName = $this->getEntityName($entityName);
    $num = (int) $this->em->createQuery("SELECT count(e) FROM ".$entityName." AS e")->getSingleScalarResult();
    
    return $num;
  }
  
  public function startDebug() {
    $this->em->getConnection()->getConfiguration()->setSQLLogger(new \Psc\Doctrine\FlushSQLLogger);
    return $this;
  }
  
  public function stopDebug() {
    $this->em->getConnection()->getConfiguration()->setSQLLogger(NULL);
    return $this;
  }
  
  public function dump($var, $depth = 3) {
    \Psc\Doctrine\Helper::dump($var, $depth);
  }

  public function dropTable($table) {
    $schema = $this->em->getConnection()->getSchemaManager();
    
    if ($schema->tablesExist(array($table))) {
      $schema->dropTable($table);
    }
    return $this;
  }
  
  /**
   * @return bool
   */
  public function tableExists($table) {
    $schema = $this->em->getConnection()->getSchemaManager();
    
    return $schema->tablesExist(array($table));
  }
  
  public function truncateTable($tableNames) {
    $tableNames = (array) $tableNames;
    $this->em->getConnection()->executeQuery('set foreign_key_checks = 0');
    foreach ($tableNames as $table) {
      $this->em->getConnection()->executeQuery('TRUNCATE `'.$table.'`');
    }
    $this->em->getConnection()->executeQuery('set foreign_key_checks = 1');
  }

  
  public function createEntitySchema($entity) {
    $meta = $this->em->getClassMetadata($this->getEntityName($entity));
    
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    $schemaTool->createSchema(array($meta));
    return $this;
  }

  public function dropEntitySchema($entity) {
    $meta = $this->em->getClassMetadata($this->getEntityName($entity));
    
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    $schemaTool->dropSchema(array($meta));
    return $this;
  }
  
  public function updateEntitySchema($entity) {
    $meta = $this->em->getClassMetadata($this->getEntityName($entity));
    
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    $sql = $schemaTool->getUpdateSchemaSql(array($meta));
    $schemaTool->updateSchema(array($meta), true);
    
    return $sql;
    return $this;
  }
  
  public function updateSchema() {
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    $classes = $this->em->getMetadataFactory()->getAllMetadata();
    $sql = $schemaTool->getUpdateSchemaSql($classes);
    $schemaTool->updateSchema($classes, true);
    
    return $sql;
  }

  public function dropSchema() {
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    $classes = $this->em->getMetadataFactory()->getAllMetadata();
    $sql = $schemaTool->getDropSchemaSql($classes);
    $schemaTool->dropSchema($classes, true);
    
    return $sql;
  }

  public function createSchema() {
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    $classes = $this->em->getMetadataFactory()->getAllMetadata();
    $sql = $schemaTool->getCreateSchemaSql($classes);
    $schemaTool->createSchema($classes, true);
    
    return $sql;
  }  
  /**
   * @return EntitiyRepository
   */
  public function getRepository($name) {
    return $this->em->getRepository($this->getEntityName($name));
  }
  
  public function getEntityName($shortName) {
    return DoctrineHelper::getEntityName($shortName);
  }
  
  public function hydrate($entity, $data) {
    if (is_array($data) && !\Webforge\Common\ArrayUtil::isNumeric($data)) // numeric bedeutet composite key (z.b. OID)
      return $this->getRepository($entity)->hydrateBy($data);
    else
      return $this->getRepository($entity)->hydrate($data);
  }
  
  /**
   * Gibt alle persisten Instanzen eines Entities zurück und reindeziert wenn gewünscht
   *
   * @param string $entity der Name des Entities
   * @param string $reindex der Name des Getters oder der Getter nachdem die Liste indiziert werden soll (keys)
   * @return array
   */
  public function hydrateAll($entity, $reindex = NULL) {
    $entities = $this->getRepository($entity)->findAll();
    if ($reindex !== NULL) {
      $entities = DoctrineHelper::reindex($entities, $reindex);
    }
    return $entities;
  }
  
  /**
   * Gibt eine Collection mit einem neuen index zurück
   *
   */
  public function hydrateCollection($entity, Array $identifiers, $reindex = NULL) {
    return $this->getHydrator($entity)->collection($identifiers, $reindex);    
  }
  
  /**
   * Asserted 2 Collections mit einem Feld als vergleicher
   */
  public function assertCollection($expected, $actual, $compareFieldGetter = 'identifier') {
    $this->assertEquals(DoctrineHelper::map($expected, $compareFieldGetter),
                        DoctrineHelper::map($actual, $compareFieldGetter),
                        'Collections sind nicht gleich'
                       );
  }
    
  /**
   * Gibt eine Collection ohne neuen Index zurück
   *
   */
  public function hydrateCollectionByField($entity, Array $values, $findField) {
    return $this->getHydrator($entity)->byList($values, $findField, TRUE);
  }
  
  public function getHydrator($entity) {
    return new \Psc\Doctrine\Hydrator($this->getEntityName($entity), $this->dc);
  }
  
  public function installEntity($entityName) {
    $this->loadEntity($entityName);
    $this->updateEntitySchema($entityName);
    return $this;
  }
  
  public function loadEntity($entityName, \Psc\Doctrine\Module $module = NULL) {
    $module = $module ?: $this->module;
    $c = $this->getEntityName($entityName);
    $module->registerEntityClassesMetadataDriver()->getEntityClassesMetadataDriver()->addClass($c);
    return $this;
  }
  
  /**
   * @return ClassMetadata
   */
  public function getEntityMetadata($entityName) {
    return $this->em->getMetaDataFactory()->getMetadataFor($this->getEntityName($entityName));
  }
  
  // getEntityMeta() siehe in Base
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    if (!isset($this->dc)) {
      $this->dc = new DCPackage($this->module, $this->em);
    }
    return $this->dc;
  }
  
  /**
   * Erstellt einen neuen EntityManager und setzt den in $this->em
   *
   * @return Doctrine\ORM\EntityManager
   */
  public function resetEntityManager() {
    return $this->em = $this->module->getEntityManager($this->con, TRUE);
  }
  
  public function flush() {
    $this->em->flush();
    return $this;
  }
}
?>