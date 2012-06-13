<?php

namespace Psc\UI;

use \Psc\UI\Group;

/**
 * @group class:Psc\UI\Group
 */
class GroupTest extends \Psc\Code\Test\Base {

  public function testHTMLOut() {
    $group = new Group('grouplabel','contentisthier');

    // <fieldset class="ui-corner-all ui-widget-content psc-cms-ui-group"><legend>grouplabel</legend>
    // <div class="content">contentisthier</div>
    // </fieldset>'.
    
    $this->assertTag(
      array('tag' => 'fieldset',
            'attributes' => array('class' => 'ui-widget-content ui-corner-all psc-cms-ui-group'),
            'child' => array('tag' => 'legend',
                             'content' => 'grouplabel'
                            )
            ), (string) $group);
    
    $this->assertTag(
      array('tag' => 'fieldset',
            'child' => array('tag' => 'div',
                             'attributes'=> array('class' => 'content'),
                             'content' => 'contentisthier'
                            )
           ),
      (string) $group
    );
  }
}
?>