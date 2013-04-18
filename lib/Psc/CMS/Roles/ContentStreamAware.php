<?php

namespace Psc\CMS\Roles;

interface ContentStreamAware {

  /**
   * @return Psc\TPL\ContentStream\Controller
   */
  public function getContentStream();
}
