<?php

namespace Psc\JS;

use stdClass;
use Psc\Preg;

/**
 * später mals Trait?
 */
abstract class JSONObject extends \Psc\OptionsObject implements JSON {
  
  public function JSON() {
    return \Psc\JS\Helper::reformatJSON(json_encode($this->exportJSON()));
  }
  
  /**
   * @return stdClass
   */
  public function exportJSON() {
    $export = new stdClass;
    foreach ($this->getJSONFields() as $field => $meta) {
      if ($meta === 'JSON[]') { // das nicht zur optimierung nach unten schieben da sonst xx hier matched
        $export->$field = array_map(function ($object) {
                                      return $object->exportJSON();
                                    },
                                    $this->$field
                                   );
      } elseif (mb_substr($meta,0,4) === 'JSON') { //xx
        if (($v = $this->$field) instanceof JSON) {
          $export->$field = $v->exportJSON();
        } else {
          $export->$field = $v;
        }
      } elseif ($meta === 'Collection<JSON>') {
        $collection = $this->$field;
        $export->$field = array_map(function ($object) {
                                      return $object->exportJSON();
                                    },
                                    $collection->toArray()
                                   );
      } elseif($meta instanceof \Closure) {
        $export->$field = $meta($this->$field);
      } else {
        $export->$field = $this->$field;
      }
    }
    return $export;
  }

  abstract protected function getJSONFields();

  public function setJSONFields(stdClass $json) {
    foreach ($this->getJSONFields() as $field => $meta) {
      if ($meta === NULL) {
        $this->$field = $json->$field;
      } elseif (mb_substr($meta,0,4) === 'JSON') {
        if ($json->$field === NULL) {
          $this->$field = NULL;
        } elseif (($class = Preg::qmatch($meta,'/^JSON<(.*)>$/', 1)) !== NULL) {
          $this->$field = $class::createFromJSON($json->$field);
        }
      }
    }
    return $this;
  }
}
?>