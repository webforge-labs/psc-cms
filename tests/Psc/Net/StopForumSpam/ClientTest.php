<?php

namespace Psc\Net\StopForumSpam;

use Psc\System\ExecutableFinder;

/**
 * @group class:Psc\Net\StopForumSpam\Client
 */
class ClientTest extends \Psc\Code\Test\Base {
  
  protected $client;
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\StopForumSpam\Client';
    parent::setUp();

    $finder = new ExecutableFinder();
    if (!$finder->findsExecutable('stop-forum-spam')) {
      return $this->markTestSkipped('stop-forum-spam executable not installed');
    }
    
    $this->client = Client::create();
  }
  
  public function testAcceptance() {
    $this->assertInternalType('array', $this->client->queryByEmail('p.scheit@ps-webforge.com'));
  }
}
?>