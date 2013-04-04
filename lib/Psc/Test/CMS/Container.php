<?php

namespace Psc\Test\CMS;

use Psc\Test\ContentStreamConverter;

class Container extends \Psc\CMS\Roles\AbstractContainer {

  protected function createContentStreamConverter() {
    return new ContentStreamConverter($this);
  }
}
