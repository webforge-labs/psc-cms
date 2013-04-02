<?php

namespace Psc\CMS\Controller;

use Psc\Entities\ContentStream\ContentStream;
use Psc\Entities\Page;

class PageControllerHelperTest extends \Psc\Doctrine\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Controller\\PageControllerHelper';
    parent::setUp();

    $this->helper = new PageControllerHelper();

    $this->page = new Page('home');  

    foreach (array('de', 'fr') as $lang) {
      foreach (array('page-content', 'sidebar-content') as $type) {
        $this->page->addContentStream(
          $this->contentStreams[$lang][$type] = ContentStream::create($lang, $type, $revision = 'default', $slug = $lang.'-'.$type)
        );
      }
    }
  }

  public function testReturnsButtonsForContentStreams() {
    $entityMeta = $this->dc->getEntityMeta('Psc\Entities\ContentStream\ContentStream');

    $this->assertGreaterThanOrEqual(
      4,
      count($this->helper->getContentStreamButtons($this->page, $entityMeta))
    );
  }
}
