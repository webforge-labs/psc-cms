<?php

namespace Psc\Net\StopForumSpam;

use Webforge\Common\System\ExecutableFinder;

/**
 * @group class:Psc\Net\StopForumSpam\Client
 */
class ClientTest extends \Psc\Code\Test\Base {
  
  protected $client;
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\StopForumSpam\Client';
    parent::setUp();

    $finder = $this->getSystemContainer()->getExecutableFinder();

    if (!$finder->findsExecutable('stop-forum-spam')) {
      return $this->markTestSkipped('stop-forum-spam executable not installed');
    }
    
    $this->client = Client::create($finder);
  }
  
  public function testAcceptance() {
    $this->assertInternalType('array', $this->client->queryByEmail('p.scheit@ps-webforge.com'));
  }
}
