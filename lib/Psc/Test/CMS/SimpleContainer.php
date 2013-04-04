<?php

namespace Psc\Test\CMS;

use Psc\CMS\Roles\AbstractSimpleContainer;
use Psc\Test\ContentStreamConverter;

class SimpleContainer extends AbstractSimpleContainer {

  protected function createContentStreamConverter() {
    return new ContentStreamConverter($this);
  }
}
