<?php

namespace Psc\CMS\Ajax;

class TemplateResponse extends StandardResponse {
  
  protected $template;
  
  public function export() {
    $this->setContent($this->template->get());
    return parent::export();
  }
}
?>