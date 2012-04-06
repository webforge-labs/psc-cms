<?php

namespace Psc\UI;

use \Psc\UI\FormPanel;

class FormPanelTest extends \Psc\Code\Test\Base {
  
  public function testSmoke() {
    $formPanel = new FormPanel('Episoden verwalten');

    $formPanel->header();
    
    $formPanel->content();

    $formPanel->footer();
  }
}

?>