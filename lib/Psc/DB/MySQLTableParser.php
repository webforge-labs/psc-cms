<?php

namespace Psc\DB;

use \Psc\String AS S,
    \Psc\SQL\SQL,
    \Psc\Preg
;

class MySQLTableParser extends \Psc\Object {
  
  const INT = 'INT';
  const STRING = 'STRING';
  const BOOL = 'BOOL';
  const UTS = 'UTS';
  const VALUES = 'VALUES';
  const FLOAT = 'FLOAT';

  /**
   * 
   * @var DB
   */
  protected $db;

  public function __construct(DB $db) {
    $this->db = $db;
  }

  public function getPrimaryKey($tableName) {
    try {
      $sql = "SHOW KEYS FROM `".$tableName."` ";
    
      $res = $this->db->fetch($sql);
    } catch (DBNoSuchTableException $e) {
      throw new Exception('Tabelle `'.$tableName.'` nicht gefunden.');
    }
    
    $keys = array();
    foreach ($res as $key) {
      if ($key['Key_name'] == 'PRIMARY') {
        $keys[] = $key['Column_name'];
      }
    }
    
    return $keys;
  }


  /**
   * 
   * @return Array
   */
  public function getColumns($tableName) {
    try {
      $sql = 'SHOW COLUMNS FROM '.SQL::quote($tableName);
      
      $res = $this->db->fetch($sql);

    } catch (DBNoSuchTableException $e) {
      throw new Exception('Tabelle `'.$tableName.'` nicht gefunden.');
    }
    
    /* regex für das parsen der Parameter und Flags des Typs */
    $stringRx = "'(?:'.|[^'])*'"; // ein string in quotes 'wert' wobei auch 'fieser''wert' erkannt wird (so escaped mysql wohl single quotes)
    $optionalsRx = '(?:\s(?:binary|ascii|unicode|unsigned|zerofill))*';
    $parameterRx = '(?:'.$stringRx.'|[0-9]+)';
    $parametersRx = '\('.$parameterRx.'(?:,\s*'.$parameterRx.')*\)';
    $typeRx = '/^([A-Za-z]+)((?:'.$parametersRx.')?)('.$optionalsRx.')$/'; // TINYINT(11) UNSIGNED ZEROFILL

    // \x5C ist ein Backslash, pattern nicht in double quote, da dieser sonst von php ersetzt würde (was wir nicht wollen)
    $dblQuoted = '"(?:[^\x5C"]+|\x5C(?:\x5C\x5C)*[\x5C"])*"'; // ein double Quoted Parameter
    $snglQuoted = '\'(?:[^\x5C\']+|\x5C(?:\x5C\x5C)*[\x5C\'])*\''; // ein single Quoted Parameter
    $nonQuoted = '[^"\',]+'; // alles andere
    $paramsRx = '/(?:'.$dblQuoted.'|'.$snglQuoted.'|'.$nonQuoted.')+/g';

    $columns = array();
    
    foreach ($res as $columnDef) {

      /* wir parsen noch die parameter */
      if (Preg::match($columnDef['Type'],$typeRx,$match) > 0) {
        list($NULL,$type,$parameters,$optionals) = $match;
        
        if (Preg::match(mb_substr(trim($parameters),1,-1),$paramsRx,$matches) > 0) {
          $parameters = array_map(
            function ($a) {
              $a = trim($a[0]);
              if (S::startsWith($a,"\'")) {
                $a = mb_substr($a,1,-1);
              }
                
              return $a;
            },
            $matches
          );
        }

        $optionals = (!empty($optionals)) ? trim($optionals) : NULL;
        
        $primaryKey = $columnDef['Key'] == 'PRI';
        
        $column = Array(
          'type' => $type,
          'parameters' => $parameters,
          'flags' => $optionals,
          'null' => $columnDef['Null'] == 'YES',
          'primary' => $primaryKey,
          'name' => $columnDef['Field'],
          'default' => $columnDef['Default'],
          'extra' => $columnDef['Extra'],
        );
               
        $columns[$columnDef['Field']] = $column;
      } else {
        throw new Exception('internal: Parse Error. Fehler beim parsen vom SQL Type: '.$columnDef['Type']);
      }
    }

    return $columns;
  }

  /**
   * 
   * @return array
   */
  public function getTables() {
    $sql = "SHOW TABLES";
    
    $res = $this->db->fetch($sql);

    $tables = array();
    foreach ($res as $row) {
      $tables[] = array_pop($row);
    }

    return $tables;
  }


}
?>