<?php

namespace Psc\Doctrine;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Tools\SchemaTool,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection,
    Psc\Config,
    Psc\PSC,
    Psc\Code\Code,
    Psc\A,
    \Psc\Preg,
    \Closure
;

class Helper {
  
  const FORCE = 'doctrine_helper_force';
  
  const IN_TRANSACTION     = 0x000001;
  const WITH_LOGGING       = 0x000002;
  const RETURN_LOG         = 0x000004;
  const ROLLBACK           = 0x000010;
  
  const TRANSACTION_RETURN_LOG = 0x000007;
  const TRANSACTION_DEBUG = 0x000003;
  
  /**
   * @return \Doctrine\ORM\EntityManager
   */
  public static function em($con = NULL) {
    return PSC::getProject()->getModule('Doctrine')->getEntityManager($con);
  }
  
  /**
   * Organisiert die Collection mit einem neuen Index
   *
   * macht eine Kopie der $collection, deshalb erstmal nur array erlaubt, bis ich weiß, was da die arraycollection macht
   * @param string|Closure $getIndex wenn ein Closure wird als erster Parameter das Objekt der Collection übergeben. wenn ein string ist dies der Name des Getters des indexes für das Objekt in $collection
   * @TODO was ist schneller:
   *  1. $getIndex in ein Closure zu casten und dann einfach durchlaufe,
   *  2. oder $getIndex als String zu benutzen und jedes mal in der Schleife abfragen ob $getIndex ein String oder ein Closure ist
   *
   * @return array
   */
  public static function reindex($collection, $getIndex = 'getIdentifier') {
    if (!Code::isTraversable($collection)) {
      throw new \InvalidArgumentException('$collection muss Traversable sein. '.Code::varInfo($collection));
    }
    
    $getIndex = Code::castGetter($getIndex);
    $ret = array();
    foreach ($collection as $o) {
      $ret[$getIndex($o)] = $o;
    }

    return $ret;
  }

  /**
   * Konvertiert den Array in nur Werte die die Propertys von $getProperty sind
   *
   * ist z. B: Praktisch um in PHP Unit Objekte vergleichen zu können
   *
   * Schlüssel bleiben erhalten!
   * $phpUnit->assertEquals(Helper::convert($expectedModes, 'getIdentifier'),
   *                        Helper::convert($actualModes, 'getIdentifier'), 1,10,TRUE);
   *
   * @return array
   */
  public static function map($collection, $getProperty) {
    $getProperty = Code::castGetter($getProperty);
    $ret = array();
    foreach ($collection as $key =>$o) {
      // nicht direkt collection bearbeiten weil dies bei einen ArrayCollection glaub ich $collection verändern würde
      $ret[$key] = $getProperty($o);
    }
    return $ret;
  }
  
  public static function implodeIdentifiers($ids) {
    return A::implode($ids, ',' , function ($id) {
      if (is_array($id))
        throw new Exception('Arrays sind keine Identifier');
      
      return (string) $id;
    });
  }
  
  /**
   * Fügt die beiden Collections Ahand der Funktion $getIndex zusammen
   *
   * der Array hat als schlüssel die werte von jedem objekt der collection auf das $getIndex angewandt wurde
   * jedes Element kommt nur einmal vor.
   *
   * @see Code::castGetter()
   */
  public static function mergeUnique(Array $collection1, Array $collection2, $getIndex1, $getIndex2 = NULL) {
    $getIndex1 = Code::castGetter($getIndex1);
    if (!isset($getIndex2))
      $getIndex2 = $getIndex1;
    else
      $getIndex2 = Code::castGetter($getIndex2);
    
    $merge = array();
    foreach ($collection1 as $o) {
      $merge[$getIndex1($o)] = $o;
    }
    
    foreach ($collection2 as $o) {
      $index = $getIndex2($o);
      if (!array_key_exists($index,$merge)) {
        $merge[$index] = $o;
      }
    }
    return $merge;
  }
  
  /**
   * Gibt die Elemente aus der $dbCollection zurück, die gelöscht werden müssen (die nicht in $actualItems sind)
   *
   * ist sortierungsunabhängig (die schlüssel werden nicht gecheckt)
   * die Schlüssel sind nicht von 0 - x sondern wild
   * 
   * @param \Doctrine\Common\Collections\Collection|array $dbCollection 
   * @param \Doctrine\Common\Collections\Collection|array $actualItems ist dies ein array, wird ein array zurückgeeben, ist dies eine collection, eine collection
   * @return ArrayCollection|array
   */
  public static function deleteDiff($dbCollection, $actualItems) {
    $convert = $actualItems instanceof Collection;
    $dbCollection = Code::castArray($dbCollection);
    $actualItems = self::reindex(Code::castArray($actualItems));

    /* Es ist gar nicht so leicht dass mit den php diff etc funktionen (außer vielleicht array_diff_key) nachzubauen
    
      wir haben folgende Probleme: die udiff funktionen wollen alle die Schlüssel vergleichen, man kann ihnen nicht sagen:
      alle schlüssel sind gleich. da sie anscheinend die vergleiche anhand der reihenfolge der Element-Indizes (mit Transitivität) ermitteln
      
      wir könnten hier reindex($db), reindex($actual) und dann array_diff_key() machen, dann wären jedoch die schlüssel nicht mehr numerisch und wir müssten nochmal mit array_merge() drüber (wieder linear schlüsseln)
      
      man müsste hier testen was schneller ist.
    */
    
    $diff = array();
    foreach ($dbCollection as $a) {
      if (!array_key_exists($a->getIdentifier(),$actualItems)) {
        $diff[] = $a;
      }
    }
    
    if ($convert)
      return new ArrayCollection($diff);
    else
      return $diff;
  }
  
  /**
   * Gibt die Elemente aus der $actualItems zurück, die eingefügt werden müssen (die nicht in der $dbCollection  sind)
   *
   * ist sortierungsunabhängig (die schlüssel werden nicht gecheckt)
   * die Schlüssel sind nicht von 0 - x sondern wild
   *
   * @param \Doctrine\Common\Collections\Collection|array $dbCollection
   * @param \Doctrine\Common\Collections\Collection|array $actualItems ist dies ein array, wird ein array zurückgeeben, ist dies eine collection, eine collec
   * @return ArrayCollection
   */
  public static function insertDiff($dbCollection, $actualItems) {
    $convert = $actualItems instanceof Collection;
    $dbCollection = self::reindex(Code::castArray($dbCollection),'getIdentifier');
    $actualItems = Code::castArray($actualItems);
    
    $diff = array();
    foreach ($actualItems as $a) {
      if (!array_key_exists($a->getIdentifier(),$dbCollection)) {
        $diff[] = $a;
      }
    }
    
    if ($convert)
      return new ArrayCollection($diff);
    else
      return $diff;
  }
  
  /**
   * @return string
   */
  public static function debugCollectionDiff($expectedCollection, $actualCollection, $expectedCollectionLabel = 'expectedCollection', $actualCollectionLabel = 'actualCollection') {

    return  "Diff der Collections\n".
            "\n".
            ($expectedCollectionLabel ?: 'expectedCollection').":\n".
            self::debugCollection($expectedCollection)."\n".
            "\n".
            ($actualCollectionLabel ?: 'actualCollection').":\n".
            self::debugCollection($actualCollection)."\n";
  }
  
  public static function debugEntity($item) {
    if ($item instanceof \Psc\Doctrine\Object) {
      return sprintf("Psc\Doctrine\Object<%s|%s> '%s'",
                     $item->getEntityName(),
                     ($item->getIdentifier() > 0 ? $item->getIdentifier() : 'noid:'.trim(spl_object_hash($item),'0')),
                     $item->getTabsLabel()
                     );
    } elseif ($item instanceof \Psc\Doctrine\Entity) {
      return sprintf("Psc\Doctrine\Entity<%s|%s> '%s'",
                     $item->getEntityName(),
                     ($item->getIdentifier() > 0 ? $item->getIdentifier() : 'noid:'.trim(spl_object_hash($item),'0')),
                     $item->getContextLabel()
                    );

    } else {
      return Code::varInfo($item);
    }
  }
  
  public static function debugCollection($collection, $glue = "\n", $label = NULL) {
    $ret = ($label ? 'Collection '.$label.$glue : NULL);
    if (count($collection) === 0) {
      $ret .= '[leere Collection]';
    } else {
      $ret .= \Psc\A::implode(Code::castArray($collection), $glue, function ($item, $key) {
        return sprintf('[%s] %s', $key, \Psc\Doctrine\Helper::debugEntity($item));
      });
    }
    
    return $ret;
  }
  
  
  public static function hydrateReference($object) {
    if ($object instanceof \Psc\Doctrine\EntityReference) {
      return $object->hydrate();
    }
    
    return $object;
  }
  
  /**
   * @param mixed $input kann der type sein (wie z.b. oid wird zu Entities\OID)
   * @return string
   */
  public static function getEntityName($input) {
    return PSC::getProject()->getModule('Doctrine')->getEntityName($input);
  }
  
  public static function getDefaultNamespace() {
    return PSC::getProject()->getModule('Doctrine')->getEntitiesNamespace();
  }
  
  public static function dump($item,$maxDepth = 3) {
    print '<pre>';
    \Doctrine\Common\Util\Debug::dump($item, $maxDepth);
    print '</pre>';
  }
  
  
  public static function getDump($item, $maxDepth = 3) {
        ini_set('html_errors', 'On');
        
        if (extension_loaded('xdebug')) {
            ini_set('xdebug.var_display_max_depth', $maxDepth);
        }
        
        $var = \Doctrine\Common\Util\Debug::export($item, $maxDepth++);
        
        ob_start();
        var_dump($var);
        $dump = ob_get_contents();
        ob_end_clean();
        
        return strip_tags(html_entity_decode($dump));
        
        ini_set('html_errors', 'Off');
    }
    

  /**
   * @TODO das hier geht noch nicht
   * Das Problem ist, dass ich nicht weiß ,wie ich Abfrage ob User oder eine Subklasse von User schon von doctrine geladen wird (dann wollen wir nicht automatisch installieren)
   * */
  public static function installEntity(\Psc\Doctrine\Object $object) {
    $em = $object->getEM();
    throw new Exception('Dies hier muss noch gebaut werden');
    
    if (Config::get('cms.installEntities') == TRUE) {
      
$code = '
namespace Entities;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends \Psc\CMS\User {
  
}';
    }
  }
  

  
  public static function enableSQLLogging($type = 'stack', EntityManager $em) {
    if ($type == 'echo') {
      $em->getConnection()->getConfiguration()->setSQLLogger(new FlushSQLLogger);
    } elseif($type === NULL) {
      $em->getConnection()->getConfiguration()->setSQLLogger(NULL);
    } else {
      $em->getConnection()->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\DebugStack);
    }
  }

  
  public static function printSQLLog($filter = NULL, $return = FALSE, EntityManager $em = NULL) {
    $em = $em ?: self::em();
    
    $br = "<br />\n";
    $config = $em->getConnection()->getConfiguration();
    
    $ret = NULL;
    
    if (!is_object($config->getSQLLogger())) {
      throw new \Psc\Exception('Logger muss vorher gestartet worden sein');
    }
    
    $time = 0;
    foreach ((array) $config->getSQLLogger()->queries as $item) {
      if (isset($filter) && Preg::match($item['sql'],$filter) == 0) {
        continue;
      }
      
      $time += $item['executionMS']*1000000;
      
      $sql = $item['sql'];
      if (isset($item['params']) && is_array($item['params'])) {
        foreach ($item['params'] as $p) {
          try {
            if (is_array($p)) $p = self::implodeIdentifiers($p);
        } catch (\Exception $e) { $p = '[unconvertible array]'; }
          if (is_string($p)) $p = "'".$p."'";
          if ($p instanceof \DateTime) $p = $p->format('YDM-H:I');
          $sql = preg_replace('/\?/',(string) $p, $sql, 1); // ersetze das erste ? mit dem parameter
        }
      }
      
      $ret .= $sql.';'.$br;
    }
    $ret .= 'Time (cumulated): '.($time/1000000);
    
    if (!$return)
      print $ret;
      
    return $ret;
  }
  
  
  /**
   * Führt Datenbank Aktionen innerhalb eine Safe-Umgebung (try catch - transactional) aus und kann dabei loggen
   * $what bekommt als erstes Argument $em übergeben
   */
  public static function doSafe($flags, Closure $what, EntityManager $em = NULL) {
    if (!isset($em)) $em = self::em();
    
    $transaction = $flags & self::IN_TRANSACTION;
    $logging = $flags & self::WITH_LOGGING;
    $rollback = $flags & self::ROLLBACK;
    
    try {
      if ($transaction)
        $em->beginTransaction();
        
      if ($logging)
        self::enableSQLLogging();
      
      /* ausführen */
      $ret = $what($em);
      
      /* flush */
      $em->flush();
      
      /* commit */
      if ($transaction && !$rollback)
        $em->commit();
        
      if ($rollback)
        $em->rollback();
        
      /* log */
      if ($logging) {
        if ($flags & self::RETURN_LOG)
          return self::printSQLLog('/^(INSERT INTO|DELETE|UPDATE)/',TRUE);
        else
          self::printSQLLog('/^(INSERT INTO|DELETE|UPDATE)/');
      }
      
      return $ret;
      
    } catch (\Exception $e) {
      if ($transaction)
        $em->rollback();
        
      if ($logging && !($flags & self::RETURN_LOG)) {
        self::printSQLLog('/^(INSERT INTO|DELETE|UPDATE)/');
      }
        
      $em->close();
      throw $e;
    }
  }
  
  
  public static function updateSchema($force = NULL, $eol = "<br />", \Doctrine\ORM\EntityManager $em = NULL) {
    $em = $em ?: self::em();
    
    if (extension_loaded('apc')) {// damit das update auch durchgeht
      apc_clear_cache();
    }
    
    $tool = new SchemaTool($em);
    $classes = $em->getMetadataFactory()->getAllMetadata();
    
    $log = NULL;
    foreach ($tool->getUpdateSchemaSql($classes, TRUE) as $sql) {
      $log .= $sql.';'.$eol;
    }
    
    if ($force == self::FORCE) {
      $tool->updateSchema($classes, TRUE);  
    }
    return $log;
  }

  public static function createSchema($force = NULL) {
    $em = self::em();
    
    $tool = new SchemaTool($em);
    $classes = $em->getMetadataFactory()->getAllMetadata();

    foreach ($tool->getCreateSchemaSql($classes, TRUE) as $sql) {
      print $sql.';<br />';
    }
    
    if ($force == self::FORCE) {
      $tool->createSchema($classes, TRUE);  
    }
  }
    
  public static function reCreateEm($con = NULL) {
    return PSC::getProject()->getModule('Doctrine')->getEntityManager($con, TRUE);
  }
  
  public static function assertCTI($item) {
    if (!($item instanceof \Psc\CMS\TabsContentItem) && !($item instanceof \Psc\CMS\TabsContentitem2)) {
      throw new \InvalidArgumentException('Typ TabsContentItem2 / TabsContentitem erwartet');
    }
  }
}

?>