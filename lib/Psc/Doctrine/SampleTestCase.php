<?php

namespace Psc\Doctrine;

class SampleTestCase extends \Psc\Doctrine\DatabaseTest {
  
  public function configure() {
    $this->con = 'tests';
    parent::configure();
  }
  
  public function getEntityManager() {
    return $this->em;
  }
  
  public function testSomething() {
    /* wir laden hier die Fixtures users und products */
    $this->loadFixture('users');
    $this->loadFixture('products');
  }
}
?>