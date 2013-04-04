<?php

namespace Psc\Test;

class ContentStreamConverter extends \Psc\TPL\ContentStream\Converter {
  
  public function getTypeClass($typeName) {
    return 'Psc\Entities\ContentStream\\'.$typeName;
  }
}
