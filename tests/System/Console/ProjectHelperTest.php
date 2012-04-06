<?php

namespace Psc\System\Console;

use Psc\System\Console\ProjectHelper;

class ProjectHelperTest extends \Psc\Code\Test\Base {

  public function testHelper() {
    $helper = new ProjectHelper($pr = $this->getMock('Psc\CMS\Project', array(), array(), '', FALSE));
    
    $this->assertEquals('project',$helper->getName());
    $this->assertSame($pr, $helper->getProject());
  }
}
?>