<?php

namespace Psc\CSS;

/**
 * @group class:Psc\CSS\Manager
 */
class ManagerTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $manager;
  
  public function setUp() {
    $this->chainClass = 'Psc\CSS\Manager';
    parent::setUp();
    
    $this->manager = Manager::instance('test');
    $this->manager->register('/css/some/test.css');
    $this->manager->enqueue('test');
  }
  
  public function testEnqueuedIsInHTML() {
    $this->html = $this->manager->html();
    
    $this->test->css('link[rel="stylesheet"]')
      ->count(1)
      ->hasAttribute('href','/css/some/test.css');
  }
    
  public function testCSSUrlChangesIfSetAfterRegister() {
    $this->manager->setUrl('/cms');
    $this->assertEquals('/cms/', $this->manager->getUrl());
    
    $this->html = $this->manager->html();
    $this->test->css('link[rel="stylesheet"]')
      ->count(1)
      ->hasAttribute('href','/cms/css/some/test.css');
  }
  
  public function testDefaultmanagerHasDefaultURl() {
    $manager = new Manager('default');
    $this->assertEquals('/css/',$manager->getUrl());
  }
}
?>