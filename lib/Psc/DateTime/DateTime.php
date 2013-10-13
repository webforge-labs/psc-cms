<?php

namespace Psc\DateTime;

class DateTime extends \Webforge\Common\DateTime\DateTime implements \Psc\Data\Walkable, \Psc\Code\Info, \Psc\Data\Exportable {
  
  public function getWalkableType($field) {
    if ($field === 'date') {
      return \Psc\Data\Type\Type::create('Integer');
    } elseif ($field === 'timezone') {
      return \Psc\Data\Type\Type::create('String');
    }
  }
}
