<?php

namespace Psc\Code\Test;

use Psc\DateTime\DateTime;
use Psc\CMS\Configuration;
use Psc\PSC;
use Psc\System\Console\MySQLLoader;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;

/**
 * Mein neuer Ansatz für deinen Datenbank-Test (denn das von PHPUnit find ich käs)
 *
 * http://wiki.ps-webforge.com/psc-cms:dokumentation:tests
 */
class NativeDatabaseTest extends \Psc\Code\Test\HTMLTestCase {
  
  const LATEST = 'latest';
  
  protected $fixtures = NULL;
  
  protected $configuration;
  
  /**
   * Wir setzen ein paar Defaults für die Konfiguration
   *
   * diese kann natürlich immer durch ableiten von configure() überschrieben werden
   */
  public function configure() {
    $conf = PSC::getProjectsFactory()->getHostConfig()->req(array('system','dbm'));

    $this->configuration = new Configuration($conf);
    $this->configuration->set(array('database'),
                              sprintf('%s_tests',
                                      PSC::getProject()->getLowerName())
                             );
  }
  
  /**
   * Gibt das Verzeichnis zurück in dem die ganzen SQL-Fixture Dateien liegen
   *
   */
  public function getDBDirectory() {
    return $this->getResourceHelper()->getCommonDirectory()->sub('db/')->make(Dir::ASSERT_EXISTS | DIR::PARENT);
  }
  
  public function getFixtures() {
    if (!isset($this->fixtures)) {
      $this->fixtures = array();
      
      foreach ($this->getDBDirectory()->getFiles('sql',NULL,FALSE) as $sqlFile) {
        $match = array();
        if (\Psc\Preg::match($sqlFile->getName(File::WITHOUT_EXTENSION), '/^(.+)_([0-9]{8}\.[0-9]{4})$/',$match) == 0) {
          throw new \Psc\Exception('Datei mit dem Namen: '.$sqlFile->getName().' hat ein falsches format.');
        }
        list($null,$tableName,$date) = $match;
        $tableName = mb_strtolower($tableName);
        $datetime = DateTime::parse('Ymd.Hi', $date);
        
        $this->fixtures[$tableName][$datetime->getTimestamp()] = (object) array(
          'date'=>$datetime,
          'tableName'=>$tableName,
          'file'=>$sqlFile
        );
      }
      
      foreach ($this->fixtures as $tableName => $group) {
        krsort($group, SORT_NUMERIC);
        $this->fixtures[$tableName] = $group;
      }
    }
    return $this->fixtures;
  }

  /**
   * 
   * @return \stdClass
   */
  public function getFixture($tableName, $specific = self::LATEST) {
    $fixtures = $this->getFixtures();
    
    if ($specific != self::LATEST) {
      throw new \Psc\Exception('Noch nicht implementiert');
    }
    
    $this->assertArrayHasKey($tableName, $fixtures,
                             sprintf("Fixture-Table-Name: '%s' nicht gefunden\nFolgende Fixtures sind möglich zu laden:\n  %s",
                                     $tableName, implode("\n  ",array_keys($fixtures)))
                            );
    $this->assertInternalType('array', $fixtures[$tableName]);
    return current($fixtures[$tableName]);
  }
  
  /**
   * Fügt die Daten eines Fixtures in die Datenbank ein
   * 
   */
  public function loadFixture($tableName, $specific = self::LATEST) {
    $this->assertNotEmpty($tableName);
    $loader = new MySQLLoader($this->configuration->req('database'), $this->configuration);
    try {
      $loader->loadFromFile($file = $this->getFixture($tableName, $specific)->file);
    } catch (\Psc\System\Console\MySQLLoaderException $e) {
      throw new \Psc\Exception(sprintf("SQL Fehlerhaft für Fixture: '%s' Fehler: '%s'",$file->getName(), $e->sqlError));
    }
    
    return $this;
  }
  
  protected function loadFixtureFromFile(File $file) {
    $loader = new MySQLLoader($this->configuration->req('database'), $this->configuration);
    try {
      $loader->loadFromFile($file);
    } catch (\Psc\System\Console\MySQLLoaderException $e) {
      throw new \Psc\Exception(sprintf("SQL Fehlerhaft für Fixture: '%s' Fehler: '%s'",$file->getName(), $e->sqlError));
    }
    //print "  Loaded Fixture from File: ".$file."\n";
    //flush();
    
    return $this;
  }

  /**
   * Fügt die Daten von mehreren Fixtures in die Datebank ein
   *
   * gibt Verbose Informationen aus
   */
  public function loadFixtures(Array $fixtureNames) {
    $fixtureNames = array_unique($fixtureNames);
    //print sprintf("Loading Fixtures into: '%s' \n", $this->configuration->req('database'));
    //$bench = new \Psc\DateTime\TimeBenchmark();
    foreach ($fixtureNames as $name) {
      $this->loadFixture($name);
    }
    //print $bench->end()."\n";
    //flush();
    return $this;
  }
}
?>