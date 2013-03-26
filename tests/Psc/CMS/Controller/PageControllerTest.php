<?php

namespace Psc\CMS\Controller;

use Psc\UI\PagesMenu;
use Psc\Entities\Page;
use Psc\Entities\ContentStream\ContentStream;

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

  public function testGetEntityWithSubresourceContentStreamAndLanguageReturnsTheFormOfTheContentStreamFromThePage() {
    $page = $this->insertPageWithLocaleContentStreams();

    $csController = $this->getMockForAbstractClass('Psc\CMS\Controller\ContentStreamController', array(), '', FALSE, TRUE, TRUE, array('getEntityFormular'));

    $this->container->expects($this->atLeastOnce())->method('getController')->with('ContentStream')
      ->will($this->returnValue($csController));

    $csDe = $page->getContentStreamByLocale('de');
    $that = $this;

    $csController->expects($this->atLeastOnce())->method('getEntityFormular')
      ->will($this->returnCallback(function ($csDep) use ($that, $csDe) {
        $that->assertEquals($csDe->getIdentifier(), $csDep->getIdentifier());
        return 'thehtml';
      }));

    $this->assertEquals('thehtml', $this->controller->getEntity($page->getId(), array('contentstream', 'de')));
  }

  protected function insertPageWithLocaleContentStreams() {
    $page = new Page('test-page');

    foreach ($this->languages as $lang) {
      $cs = new ContentStream($lang, 'cs-'.$lang);
      $page->addContentStream($cs);
      $this->em->persist($cs);
    }

    $this->em->persist($page);
    $this->em->flush();
    $this->em->clear();

    return $page;
  }
}
