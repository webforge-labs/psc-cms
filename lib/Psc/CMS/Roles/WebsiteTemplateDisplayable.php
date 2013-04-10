<?php

namespace Psc\CMS\Roles;

interface WebsiteTemplateDisplayable {

  /**
   * @return Psc\TPL\ContentStream\Controller
   */
  public function getContentStream();
}
