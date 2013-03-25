<?php

namespace Psc\CMS\Controller;

use Psc\UI\PagesMenu;

class PageControllerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Controller\\PageController';
    self::$setupDatabase = FALSE;
    parent::setUp();

    $this->languages = array('de');
    $this->language = 'de';

    $this->container = $this->getMockForAbstractClass(
      'Psc\CMS\Roles\AbstractContainer', 
      array('Psc\Test\Controllers', $this->dc, $this->languages, $this->language),
      '',
      TRUE,
      TRUE,
      TRUE,
      array('getController')
    );
    $this->controller = new \Psc\Test\Controllers\PageController($this->dc, $this->container);
  }

  public function testPreSetup() {
    $this->assertChainable($this->controller);
  }

  public function testGetEntityGridConstructsAPanelWithPageMenus() {
    $navController = $this->getMockForAbstractClass('Psc\CMS\Controller\NavigationController', array(), '', FALSE, TRUE, TRUE, array('getPagesMenu'));
    $navController->expects($this->atLeastOnce())->method('getPagesMenu')
      ->will($this->returnValue(
        new PagesMenu(array())
      )
    );

    $this->container->expects($this->atLeastOnce())->method('getController')->with('NavigationNode')
      ->will($this->returnValue($navController));

    $this->html = $panel = $this->controller->getEntities(array(), 'grid');

    $this->test->css('.psc-cms-ui-form-panel')->count(1);
  }
}
