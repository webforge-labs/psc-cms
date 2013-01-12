<?php

namespace Psc\Code\Test;

use \Psc\Config,
    \Psc\PSC,
    \Psc\Code\Code,
    \Psc\Doctrine\Helper as DoctrineHelper,
    \PHPUnit_Extensions_Database_TestCase,
    \PHPUnit_Extensions_Database_Operation_Null,
    \PHPUnit_Extensions_Database_Operation_Composite,
    \PHPUnit_Extensions_Database_Operation_Factory,
    \Psc\DB\PDO
;

/**
 * Whenever you can, don't use this test anymore
 *
 * Psc\Doctrine\DatabaseTest
 * oder Psc\Code\Test\NativeDatabaseTest
 */
abstract class DatabaseTest {
  
    protected $resourceHelper;
    
    static $pdo = NULL;
  
    protected $connection; // phpunit connection
    protected $em; // doctrine em
    
    protected $con = NULL;
    
    protected $xmlName;
    
    public static function setUpBeforeClass() {
      /* das hier in die subklasse kopieren */
      //print DoctrineHelper::createSchema(\Psc\Doctrine\Helper::FORCE);
      //print DoctrineHelper::updateSchema(\Psc\Doctrine\Helper::FORCE);
    }
    
    protected function getSetUpOperation() {
      return new PHPUnit_Extensions_Database_Operation_Composite(array(
          new \Psc\PHPUnit_Operation('set foreign_key_checks = 0'),
          new \Psc\PHPUnit_Operation_TableInsert,
          new \Psc\PHPUnit_Operation('set foreign_key_checks = 1')
      ));
    }
    
    protected function tearDown() {
      parent::tearDown();
      
      $this->resetEntityManager();
    }
    
    protected function truncateAllTables() {
      $meta = $this->getConnection()->getMetadata();
      
      $sql = 'set foreign_key_checks = 0;';
      foreach ($meta->getTableNames() as $table) {
        $sql .= 'TRUNCATE `'.$table.'`;';
      }
      
      $sql .= 'set foreign_key_checks = 1;';
      
      $this->getConnection()->getConnection()->query($sql);
    }
    
    protected function configure() {
      $this->con = 'tests';
    }
    
    public function setUp() {
      $this->configure();
      
      $this->truncateAllTables();
      parent::setUp();// führt den kram von getSetupOperation() aus
      
      $this->em = PSC::getProject()->getModule('Doctrine')->getEntityManager($this->con);
      $this->assertEquals('_tests', mb_substr($this->em->getConnection()->getDatabase(),-6), 'Datenbank muss eine _tests Datenbank sein: '.$this->em->getConnection()->getDatabase());
      
      $this->setUpTest();
    }
    
    public function setUpTest() {
      
    }
    
    /*
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection() {
      $conf = Config::req('db','tests');
      $this->assertEquals('_tests', mb_substr(Config::req('db','tests','database'),-6));
      
      if ($this->connection == NULL) {
        if (self::$pdo == NULL) {
          self::$pdo = new PDO('tests');
          self::$pdo->registerWithDoctrine();
        }

        $this->connection = $this->createDefaultDBConnection(self::$pdo,'tests');

        if (isset($conf['charset'])) {
          $this->connection->getConnection()->query("SET CHARACTER SET '".$conf['charset']."'");
        }
      }
      
      return $this->connection;
    }
    
  /**
   * Erzeugt eine Datei mit dem Namen (ohne .xml) in files/testdata/dbfixtures/$name.xml
   *
   * mysqldump --xml
   */
  abstract public function xmlName();
    
  public function getDataSet() {
    $dbFixtures = PSC::get(PSC::PATH_TESTDATA).'dbfixtures'.DIRECTORY_SEPARATOR;
    $dbFixture = $dbFixtures.$this->xmlName().'.xml';
    $this->assertFileExists($dbFixture);
    $dataSet = $this->createMySQLXMLDataSet($dbFixture);
    
    return $dataSet;
  }
  
  protected function resetEntityManager() {
    $this->em = DoctrineHelper::reCreateEm();
  }
  
  /**
   * @return Psc\Code\Test\ResourceHelper
   */
  public function getResourceHelper() {
    if (!isset($this->resourceHelper)) {
      $this->resourceHelper = new ResourceHelper(PSC::getProject());
    }
    return $this->resourceHelper;
  }
  
  /**
   * Gibt das "persönliche" Verzeichnis für den Test im Filesystem zurück
   *
   * Erstellt das Directory wenn es nicht existiert
   */
  public function getTestDirectory($make = TRUE) {
    $dir = $this->getResourceHelper()->getTestDirectory($this);
    if (!$dir->exists()) {
      $dir->make('-p');
    }
    return $dir;
  }

  /**
   * @return Webforge\Common\System\File (existiert)
   */
  public function getFile($name, $subDir = '/') {
    $file = $this->getTestDirectory()->sub($subDir)->getFile($name);
    
    $this->assertInstanceOf('Webforge\Common\System\File',$file,'Kann Datei: "'.$subDir.$name.'" nicht in: '.$this->getTestDirectory().' finden');
    
    return $file;
  }
  
  /**
   * @return Webforge\Common\System\File
   */
  public function newFile($name, $subDir = '/') {
    return $this->getTestDirectory()->sub($subDir)->make(Dir::PARENT | DIR::ASSERT_EXISTS)->getFile($name);
  }
  
  public function assertException($class, Closure $closure, $code = NULL, $message = NULL, $debug = TRUE) {
    try {
      $closure();
    } catch (\Exception $e) {
      $this->assertInstanceOf($class, $e, 'Exception hat nicht die richtige Klasse: '.$e);
      
      if (isset($code)) {
        $this->assertEquals($e->getCode(),$code);
      }
      
      if (isset($message)) {
        $this->assertEquals($e->getMessage(),$message);
      }
    }
  }
}
?>