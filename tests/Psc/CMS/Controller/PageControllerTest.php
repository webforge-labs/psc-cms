<?php

namespace Psc\CMS\Controller;

use Psc\UI\PagesMenu;
use Psc\Entities\Page;
use Psc\Entities\NavigationNode;
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
        new PagesMenu(array(), 'de')
      )
    );

    $this->container->expects($this->atLeastOnce())->method('getController')->with('NavigationNode')
      ->will($this->returnValue($navController));

    $this->html = $panel = $this->controller->getEntities(array(), 'grid');

    $this->test->css('.psc-cms-ui-form-panel')->count(1);
  }

  public function testGetEntityWithSubresourceContentStreamAndLanguageReturnsTheFormOfTheContentStreamFromThePage() {
    $page = $this->insertPageWithLocaleContentStreams();

    $csController = $this->expectReturnsCSController();

    $csDe = $page->getContentStream()->locale('de')->revision('default')->type('page-content')->one();
    $that = $this;

    $csController->expects($this->atLeastOnce())->method('getEntityFormular')
      ->will($this->returnCallback(function ($csDep) use ($that, $csDe) {
        $that->assertEquals($csDe->getIdentifier(), $csDep->getIdentifier());
        return 'thehtml';
      }));

    $this->assertEquals('thehtml', $this->controller->getEntity($page->getId(), array('contentstream', 'de')));
  }

  public function testGetEntityWithSubresourceContentStreamAndLanguageReturnsTheFormOfTheContentStreamFromThePageWithTypeGiven() {
    $that = $this;
    $page = $this->insertPageWithLocaleContentStreams();

    $csController = $this->expectReturnsCSController();

    $cs = $page->getContentStream()->locale('de')->revision('default')->type('sidebar-content')->one();

    $csController->expects($this->once())->method('getEntityFormular')
      ->will($this->returnCallback(function ($csDep) use ($that, $cs) {
        $that->assertEquals($cs->getIdentifier(), $csDep->getIdentifier());
        return 'thesidebarhtml';
    }));

    $this->assertEquals('thesidebarhtml', $this->controller->getEntity($page->getId(), array('contentstream', 'de', 'sidebar-content')));
  }

  public function testGetEntityWithSubresourceContentStreamAndLanguage_ReturnsTheFormOfTheContentStreamFromThePage_CreatesTheContentStreamIfNotExisting() {
    $that = $this;
    $page = $this->insertPageWithLocaleContentStreams();

    $csController = $this->expectReturnsCSController();

    $csController->expects($this->once())->method('getEntityFormular')
      ->will($this->returnCallback(function ($cs) use ($that) {
        $that->assertEquals('de', $cs->getLocale());
        $that->assertEquals('other-content', $cs->getType());
        $that->assertEquals('default', $cs->getRevision());
        return 'theotherhtml';
    }));

    $this->assertEquals('theotherhtml', $this->controller->getEntity($page->getId(), array('contentstream', 'de', 'other-content')));
  }

  protected function expectReturnsCSController() {
    $csController = $this->getMock('Psc\Test\Controllers\ContentStreamController', array('getEntityFormular'), array($this->dc, $this->container));

    $this->container->expects($this->atLeastOnce())->method('getController')->with('ContentStream')
      ->will($this->returnValue($csController));

    return $csController;
  }

  public function testGetEntityFormularDoesWork() {
    $page = $this->insertPageWithNavigationNode();

    $this->controller->getEntityFormular($page);
  }

  public function testDeletingThePageWillSetAnInactivePageInTheNode() {
    $page = $this->insertPageWithNavigationNode();
    $nav = $page->getPrimaryNavigationNode();

    $this->controller->deleteEntity($page->getIdentifier());

    $this->assertNotSame($page, $inactivePage = $nav->getPage(), 'nav should have a new page which is inactive');
    $this->assertFalse($inactivePage->isActive(), 'inactive page should be inactive');
  }

  public function testCreateInactivePageFillesWithContentStreams() {
    $page = $this->controller->createInactivePage('some-slug');
    $this->assertFalse($page->isActive(),'inactive page should be inactive ;)');

    $this->assertInstanceOf('Psc\CMS\Roles\Page', $page);
    $this->assertGreaterThanOrEqual(count($this->languages)*2, count($page->getContentStreams()), 'page should be filled with content streams');

    $this->assertGreaterThanOrEqual(1, count($page->getContentStream()->locale($this->language)->revision('default')->type('page-content')->collection()));
    $this->assertGreaterThanOrEqual(1, count($page->getContentStream()->locale($this->language)->revision('default')->type('sidebar-content')->collection()));
  }

  protected function insertPageWithNavigationNode() {
    $page = new Page('page-with-nav');
    $page->setActive(TRUE);

    $title = array();
    foreach ($this->languages as $lang) {
      $title[$lang] = 'node for page with navigation';
    }

    $nav = new NavigationNode($title);
    $nav->setLft(1);
    $nav->setRgt(1);
    $nav->setPage($page);
    $nav->setDepth(0);
    $this->em->persist($nav);
    $this->em->persist($page);
    $this->em->flush();
    $this->em->clear();

    return $this->hydrate('Page', $page->getIdentifier());
  }

  protected function insertPageWithLocaleContentStreams() {
    $page = new Page('test-page');

    foreach ($this->languages as $lang) {
      foreach (array('page-content', 'sidebar-content') as $type) {
        $cs = ContentStream::create($lang, $type, 'default', 'cs-'.$type.'-'.$lang);
        $page->addContentStream($cs);
        $this->em->persist($cs);
      }
    }

    $this->em->persist($page);
    $this->em->flush();
    $this->em->clear();

    return $page;
  }
}

