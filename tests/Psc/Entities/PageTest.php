<?php

namespace Psc\Entities;

use Psc\Entities\ContentStream\ContentStream;

class PageTest extends \Webforge\Code\Test\Base {

  protected $page, $revisionPage;

  protected $contentStreams;
  
  public function setUp() {
    $this->chainClass = 'Psc\Entities\Page';
    parent::setUp();

    $this->page = new Page('home');
    $this->revisionPage = new Page('revision-page');
    
    $this->deDefault = ContentStream::create('de', 'page-content', 'default', 'de-default');
    $this->frDefault = ContentStream::create('fr', 'page-content', 'default', 'default');
    
    $this->dePreview = ContentStream::create('de', 'page-content', 'preview-1', 'de-preview-1');
    $this->frPreview = ContentStream::create('fr', 'page-content', 'preview-1', 'fr-preview-1');
    
    $this->revisionPage->addContentStream(
      $this->deDefault
    )->addContentStream(
      $this->frDefault
    )->addContentStream(
      $this->dePreview
    );

    foreach (array('de', 'fr') as $lang) {
      foreach (array('page-content', 'sidebar-content') as $type) {
        $this->page->addContentStream(
          $this->contentStreams[$lang][$type] = ContentStream::create($lang, $type, $revision = 'default', $slug = $lang.'-'.$type)
        );
      }
    }
  }
  
  public function testContentStreamsByLocaleHaveRevisionDefault_perDefaulthihi() {
    $this->assertEquals(
      array(
        'de'=>$this->deDefault,
        'fr'=>$this->frDefault
      ),
      $this->revisionPage->getContentStreamsByLocale()
    );
  }

  public function testContentStreamsByLocaleWithParameterRevision_HaveTheRevisionIfExisting() {
    $this->assertEquals(
      array(
        'de'=>$this->dePreview,
      ),
      $this->revisionPage->getContentStreamsByLocale($rev = 'preview-1')
    );
    
    $this->revisionPage->addContentStream($this->frPreview);
    
    $this->assertEquals(
      array(
        'de'=>$this->dePreview,
        'fr'=>$this->frPreview
      ),
      $this->revisionPage->getContentStreamsByLocale($rev = 'preview-1')
    );
  }
  
  public function testgetContentStreamRespectsRevision() {
    $this->assertSame($this->dePreview, $this->revisionPage->getContentStreamByLocale('de', 'preview-1'));
    $this->assertSame($this->frDefault, $this->revisionPage->getContentStreamByLocale('fr'));
    $this->assertSame($this->frDefault, $this->revisionPage->getContentStreamByLocale('fr', 'default'));
  }


  public function testGetContentStreamReturnsChainableHelperToGetCSfromMatrixOfCS() {

    $this->assertEquals(
      $this->contentStreams['de']['page-content'],

      $this->page->getContentStream()
        ->locale('de')
        ->type('page-content')
        ->one()
    );

    $this->assertEquals(
      $this->contentStreams['fr']['page-content'],

      $this->page->getContentStream()
        ->locale('fr')
        ->type('page-content')
        ->one()
    );

    $this->assertEquals(
      $this->contentStreams['fr']['sidebar-content'],

      $this->page->getContentStream()
        ->locale('fr')
        ->type('sidebar-content')
        ->one()
    );

    $this->assertEquals(
      $this->contentStreams['de']['sidebar-content'],

      $this->page->getContentStream()
        ->locale('de')
        ->type('sidebar-content')
        ->one()
    );
  }

  public function testGetContentStreamReturnsChainHelperWithMultiple() {
    $this->assertArrayEquals(
      array(
        $this->contentStreams['de']['sidebar-content'],
        $this->contentStreams['fr']['sidebar-content']
      ),

      $this->page->getContentStream()
        ->type('sidebar-content')
        ->collection()
          ->toArray()
    );
  }

  public function testGetContentStreamThrowsMultipleExceptionForOne() {
    $this->setExpectedException('RuntimeException');

    $this->page->getContentStream()->locale('de')->one();
  }

  public function testGetContentStreamThrowsMultipleExceptionForOneWithNothing() {
    $this->setExpectedException('RuntimeException');

    $this->page->getContentStream()->one();
  }  

  public function testGetContentStreamThrowsMultipleExceptionForOneWithType() {
    $this->setExpectedException('RuntimeException');

    $this->page->getContentStream()->type('page-content')->one();
  }
}
