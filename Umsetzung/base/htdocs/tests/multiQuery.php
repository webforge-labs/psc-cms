<?php

abstract class BaseObject {
  
  public $id;
  
  public $table;
  
  public $result;
  
  public function get($id) {
    $sql = "SELECT * FROM ".$this->table." WHERE id = ".$id;
    
    $q = mysql_query($sql);
    $res = mysql_fetch_assoc($q);
    
    if (is_array($res)) {
      $this->result = $res;
      
      $this->init();
    }
  }
  
  abstract public function init();
  
  public static function load($type, $id) {
    if (isset($GLOBALS['cache'][$type][$id])) {
      //echo "cache hit: ".$type.":".$id.'<br />';
      return $GLOBALS['cache'][$type][$id];
    } else {
      //echo "cache load: ".$type.":".$id.'<br />';
      $o = new $type;
      $o->get($id);
      
      return $o;
    }
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
  
  public function __construct() {
    $this->table = 'aggregations';
  }
  
  public function init() {
    $this->closed = ($this->result['closed'] == '1');
    $this->id = (int) $this->result['id'];
    
    $GLOBALS['cache']['Aggregation'][$this->id] = $this;
  }
}


class Aggregation2ProjectMulti extends BaseObject {
  public $share;
  public $seconds;
  
  public $project;
  public $aggregation;
  
  public function __construct() {
    $this->table = 'aggregations_projects';
  }
  
  public function init() {
    $this->share = $this->result['share'];
    $this->seconds = (int) $this->result['seconds'];

    $projectId = (int) $this->result['project_id'];
    if (isset($GLOBALS['cache']['Project'][$projectId])) {
      $this->project = $GLOBALS['cache']['Project'][$projectId];
    } else {
      $this->project = new Project();
      $this->project->result = $this->result;
      $this->project->init();
    }

    $aggregationId = (int) $this->result['aggregation_id'];
    if (isset($GLOBALS['cache']['Aggregation'][$aggregationId])) {
      $this->aggregation = $GLOBALS['cache']['Aggregation'][$aggregationId];
    } else {
      $this->aggregation = new Aggregation();
      $this->aggregation->result = $this->result;
      $this->aggregation->init();
    }
  }

  public function get($id) {
    
    $sql = "SELECT * FROM ".$this->table." ";
    $sql .= "LEFT JOIN projects ON project_id = projects.id ";
    $sql .= "LEFT JOIN aggregations ON aggregation_id = aggregations.id ";
    $sql .= "WHERE ".$this->table.".id = ".$id.' ';
    
    $q = mysql_query($sql);
    $res = mysql_fetch_assoc($q);
    
    if (is_array($res)) {
      $this->result = $res;
      
      $this->init();
    }
  }
}

class Aggregation2ProjectSingle extends BaseObject {
  public $share;
  public $seconds;
  
  public $project;
  public $aggregation;
  
  public function __construct() {
    $this->table = 'aggregations_projects';
  }
  
  public function init() {
    $this->share = $this->result['share'];
    $this->seconds = (int) $this->result['seconds'];
    
    $this->project = BaseObject::load('Project',(int) $this->result['project_id']);
    $this->aggregation = BaseObject::load('Aggregation',(int) $this->result['aggregation_id']);
  }
  
}


$c = mysql_connect('localhost', 'root', '1atToae');
mysql_select_db('timetracker',$c);

mysql_query('SET SESSION query_cache_type = OFF');

$sql = "SELECT id FROM aggregations_projects";
//$sql .= " LIMIT 20";
$q = mysql_query($sql);

$collection = array();
$x = 0;
while (($row = mysql_fetch_assoc($q)) !== FALSE) {
  $id = (int) $row['id'];
  
  if (@$_GET['type'] == 'multi')
    $aggregation2Project = new Aggregation2ProjectMulti();
  else
    $aggregation2Project = new Aggregation2ProjectSingle();

  $aggregation2Project->get($id);

  //print $id . ' loaded<br />';
  $collection[$id] = $aggregation2Project;

  if (($x++ % 300) == 0) {
    $test = $aggregation2Project;
    
    print $test->project->name;
    print '('.$test->project->listvisible.')  ';
    print $test->seconds.' '.$test->share.'   ';
    print $test->aggregation->closed;
    print '<br />';
  }
}

//$timesMult = array(486,483,481,485,487);
//$timesSingle = array(885,871,880,891,876);

//var_dump(array_sum($timesMult) / count($timesMult));
//var_dump(array_sum($timesSingle) / count($timesSingle));

?>