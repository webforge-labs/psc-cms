<?php

function db_query($sql) {
  static $x = 1;
  print ($x++).': '.$sql.'<br />';
  
  $q = mysql_query($sql);
  if ($q === FALSE)
    throw new Exception('MySQL-Fehler: '.mysql_error(),mysql_errno());
  return $q;
}

abstract class BaseObject {
  
  public $id;
  
  public $table;
  
  public $result;
  
  public $context;
  
  public static $debug = '';
  
  public function get($id, $where = NULL) {
    if (!isset($where)) $where = $this->table.'.id = '.((int) $id);
    $sql = "SELECT * FROM ".$this->table." WHERE ".$where;
    
    $q = db_query($sql);
    $res = mysql_fetch_assoc($q);
    mysql_free_result($q);
    
    if (is_array($res)) {
      $this->result = $res;
      
      $this->init();
    }
  }
  
  abstract public function init();
  
  public static function load($type, $id) {
    if (isset($GLOBALS['cache'][$type][$id])) {
      self::$debug .= '<tr><td style="width: 30px;">hit</td><td style="width: 100px;">'.$type.'</td><td style="width: 20px">'.$id.'</td></tr>';
      return $GLOBALS['cache'][$type][$id];
    } else {
      self::$debug .= '<tr><td style="width: 30px;">load</td><td style="width: 100px;">'.$type.'</td><td style="width: 20px">'.$id.'</td></tr>';
      $o = new $type;
      $o->get($id);
      
      return $o;
    }
  }
  
  public static function debug() {
    return '<table style="layout: fixed;" border="1">'.self::$debug.'</table>';
  }
}

$GLOBALS['cache']['Project'] = array();
class Project extends BaseObject {
  
  public $name;
  public $contingent;
  public $listvisible;
  
  public function __construct() {
    $this->table = 'projects';
  }
  
  public function init() {
    $this->id = (int) $this->result['id'];
    $this->name = (string) $this->result['name'];
    $this->contingent = (int) $this->result['contingent'];
    $this->listvisible = ($this->result['listvisible'] == 1);
    
    $GLOBALS['cache']['Project'][$this->id] = $this;
  }
  
}

$GLOBALS['cache']['Aggregation'] = array();
class Aggregation extends BaseObject {
  public $closed;
  
  public $a2ps; // Collection von Aggregation2Projects
  public $timeslices;
  
  public function __construct() {
    $this->table = 'aggregations';
  }
  
  public function init() {
    $this->closed = ($this->result['closed'] == '1');
    $this->id = (int) $this->result['id'];
    
    $GLOBALS['cache']['Aggregation'][$this->id] = $this;
  }
  
  public function initProjects() {
    $o = new Aggregation2Project();
    $this->a2ps = $o->getByAggregationPDO($this);
  }
}


class Aggregation2Project extends BaseObject {
  public $share;
  public $seconds;
  
  public $project;
  public $aggregation;
  
  public static $statement;
  
  public function __construct() {
    $this->table = 'aggregations_projects';
  }
  
  public function init() {
    $this->share = $this->result['share'];
    $this->seconds = (int) $this->result['seconds'];
  }
  
  public function initProject() {
    $projectId = (int) $this->result['project_id'];
    if (isset($GLOBALS['cache']['Project'][$projectId])) {
      $this->project = $GLOBALS['cache']['Project'][$projectId];
    } else {
      $this->project = new Project();
      $this->project->result = $this->result;
      $this->project->init();
    }
  }

  public function initAggregation() {
    $aggregationId = (int) $this->result['aggregation_id'];
    if (isset($GLOBALS['cache']['Aggregation'][$aggregationId])) {
      $this->aggregation = $GLOBALS['cache']['Aggregation'][$aggregationId];
    } else {
      $this->aggregation = new Aggregation();
      $this->aggregation->result = $this->result;
      $this->aggregation->init();
    }
  }

  /**
   * @return array
   */
  public function getByAggregationPDO(Aggregation $aggregation) {
    global $dbh;
    $sql = "SELECT * FROM ".$this->table." ";
    $sql .= "LEFT JOIN projects ON project_id = projects.id ";
    $sql .= "WHERE ".$this->table.".aggregation_id = :aggregation_id ";

    if (!isset(self::$statement)) {
      self::$statement = $dbh->prepare($sql);
    }
    
    self::$statement->bindParam(':aggregation_id', $aggregation->id, PDO::PARAM_INT);
    self::$statement->execute();
    
    $a2ps = array();
    while ($row = self::$statement->fetch(PDO::FETCH_ASSOC)) {
      $a2p = new Aggregation2Project();
      $a2p->result = $row;
      
      $a2p->aggregation = $aggregation;
      $a2p->init();
      $a2p->initProject();
      
      $a2ps[] = $a2p;
    }
    return $a2ps;
  }

  /**
   * @return array
   */
  public function getByAggregation(Aggregation $aggregation) {
    global $dbh;
    $sql = "SELECT * FROM ".$this->table." ";
    $sql .= "LEFT JOIN projects ON project_id = projects.id ";
    $sql .= "WHERE ".$this->table.".aggregation_id = ".$aggregation->id;

    $q = db_query($sql);
    
    $a2ps = array();
    while ($row = mysql_fetch_assoc($q)) {
      $a2p = new Aggregation2Project();
      $a2p->result = $row;
      
      $a2p->aggregation = $aggregation;
      $a2p->init();
      $a2p->initProject();
      
      $a2ps[] = $a2p;
    }
    return $a2ps;
  }

  public function get($id) {
    $sql = "SELECT * FROM ".$this->table." ";
    $sql .= "LEFT JOIN projects ON project_id = projects.id ";
    $sql .= "LEFT JOIN aggregations ON aggregation_id = aggregations.id ";
    $sql .= "WHERE ".$this->table.".id = ".$id.' ';
    
    $q = db_query($sql);
    $res = mysql_fetch_assoc($q);
    mysql_free_result($q);
    
    if (is_array($res)) {
      $this->result = $res;
      
      $this->init();
    }
  }
}

$data = '<table border="1">';

$c = mysql_connect('localhost', 'root', '1atToae');
mysql_select_db('timetracker',$c);
mysql_query('SET SESSION query_cache_type = OFF');

$dbh = new PDO('mysql:host=localhost;dbname=timetracker', 'root', '1atToae');
$dbh->query('SET SESSION query_cache_type = OFF');

$sql = "SELECT * FROM projects ";
$q = db_query($sql);
while (($row = mysql_fetch_assoc($q)) !== FALSE) {
  $o = new Project();
  $o->result = $row;
  $o->init();
}
mysql_free_result($q);

$sql = "SELECT * FROM aggregations ";
$q = db_query($sql);
while (($row = mysql_fetch_assoc($q)) !== FALSE) {
  $o = new Aggregation();
  $o->result = $row;
  $o->init();
}
mysql_free_result($q);

$sql = "SELECT id FROM aggregations";
//$sql .= " LIMIT 20";
$q = mysql_query($sql);

$collection = array();
$x = 0;
while (($row = mysql_fetch_assoc($q)) !== FALSE) {
  $id = (int) $row['id'];
  
  $test = BaseObject::load('Aggregation', $id);
  $test->initProjects();
  $collection[$id] = $test;

  if (TRUE) {
    $data .= '<tr>';
    $data .= '<td>'.$test->id.'</td>';
    
    $data .= '<td>';
    if (is_array($test->a2ps)) {
      foreach ($test->a2ps as $a2p) {
        $data .= $a2p->project->name.' ('.$test->a2ps[0]->seconds.')<br />';
      }
    }
    $data .= '</td>';
    $data .= '<td>'.($test->closed ? 'ja' : 'nein').'</td>';
    $data .= '</tr>';
  }
}

$data .= '</table>';

print $data;

//print BaseObject::debug();

$queries = 182 // projekte 
 + 3547 // aggregations
 + 3547; // getByAggregation
 
 // 322ms

// projecte und aggregations vorher laden sind dann 278 ms

?>