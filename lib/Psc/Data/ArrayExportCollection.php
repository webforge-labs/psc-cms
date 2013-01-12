<?php

namespace Psc\Data;

use Psc\Code\Code;

class ArrayExportCollection extends \Psc\Data\ArrayCollection implements \Psc\Data\Exportable {
  
  public function export() {
    $export = array();
    foreach ($this->toArray() as $key=>$element) {
      if (!($element instanceof Exportable)) {
        throw new Exception('Jedes Element einer ArrayExportCollection muss ein Exportable sein. '.Code::varInfo($element));
      }
      
      $export[$key] = $element->export();
    }
    
    return $export;
  }

  public function JSON() {
    return json_encode($this->export());
  }
}
?>