<?php

namespace Psc\DateTime;

use Webforge\Types\Type;

class DateTime extends \Webforge\Common\DateTime\DateTime implements \Psc\Data\Walkable, \Psc\Code\Info, \Psc\Data\Exportable {
  
  public function getWalkableType($field) {
    if ($field === 'date') {
      return Type::create('Integer');
    } elseif ($field === 'timezone') {
      return Type::create('String');
    }
  }
}
