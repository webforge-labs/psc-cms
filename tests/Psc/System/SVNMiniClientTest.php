<?php

namespace Psc\System;

/**
 * @group class:Psc\System\SVNMiniClient
 */
class SVNMiniClientTest extends \Psc\Code\Test\Base {
  
  protected $miniClient;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\SVN\MiniClient';
    parent::setUp();
    
    if (!is_dir('D:\www\TestRepos')) {
      $this->markTestSkipped('test needs a local testing sandbox');
    }
    
    $this->miniClient = new SVNMiniClient();
    $this->repos = new Dir('D:\www\TestRepos\\');
  }
  
  public function testCommitsASingleFile() {
    $this->miniClient->commitSinglePath($this->repos, 'Umsetzung/TODO.txt', 'the new contents for '.uniqid(), 'commited from test part xxx');
    
  }
}
?>