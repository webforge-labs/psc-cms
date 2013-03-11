<?php

namespace Psc\CMS\Controller;

use Psc\Doctrine\TestEntities\Article;
use Psc\Net\Service\LinkRelation;

require_once __DIR__.DIRECTORY_SEPARATOR.'AbstractEntityControllerBaseTest.php';

/**
 * @TODO testen ob init*() funktionen aufgerufen werden(mit korrekten parametern)
 * @TODO testen ob propertiesOrder bei getEntityFormular benutzt wird
 * @TODO refactor: EntityRepositoryBuilder benutzen
 * @group class:Psc\CMS\Controller\AbstractEntityController
 */
class AbstractEntityControllerTest extends AbstractEntityControllerBaseTest {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\AbstractEntityController';
    parent::setUp();
  }
 
  public function testGetEntity() {
    $this->expectRepositoryHydrates($this->article);
    $this->assertSame($this->article, $this->controller->getEntity($this->article->getIdentifier()));
  }
  
  /**
   * @expectedException Psc\Net\HTTP\HTTPException
   */
  public function testGetEntity_ThrowsHTTPException() {
    $this->expectRepositoryHydratesNot(777);
    $this->controller->getEntity(777);
  }
  
  public function testGetEntity_toGrid() {
    $this->articles = $this->loadTestEntities('articles');
    $this->expectRepositoryFinds($this->articles, array());
    $this->assertInstanceOf('Psc\CMS\EntityGridPanel', $gridPanel = $this->controller->getEntities(array(), 'grid'));
  }
  
  public function testSortableControllerInterfaceSetsSortedGridPanelToTrue() {
    $controller = new \Psc\Test\ArticleSortingController();
    $this->assertInstanceof('Psc\CMS\Controller\SortingController', $controller);
    
    $this->articles = $this->loadTestEntities('articles');
    $gridPanel = $controller->getEntityGrid($this->articleMeta, $this->articles);
    
    $this->assertTrue($gridPanel->getSortable(), 'sortable muss true sein für controllers mit SortableEntity Interface');
  }
  
  public function testSortSave() {
    $sortMap = array();
    
    $this->articles = $this->loadTestEntities('articles');
    $id = 1;
    foreach ($this->articles as $article) {
      $article->setId($id++);
      $sortMap[] = $article->getIdentifier();
    }

    $this->expectRepositoryHydrates($this->articles, $this->any());
    $this->expectRepositoryPersists($this->articles, $this->any());
    
    shuffle($sortMap);
    
    $controller = new \Psc\Test\ArticleSortingController();
    $controller->setRepository($this->repository); // inject

    $controller->saveSort($sortMap);
    
    $sortedMap = array();
    foreach ($this->articles as $article) {
      $sortedMap[$article->getSort()-1] = $article->getIdentifier();
    }
    
    $this->assertEquals($sortMap, $sortedMap);
  }

  public function testGetSearchPanel() {
    $this->assertInstanceOf('Psc\CMS\EntitySearchPanel', $this->controller->getEntities(array(), 'search'));
  }
  
  public function testGetEntity_ToCustomAction() {
    $this->expectRepositoryHydrates($this->article);
    $this->controller->expects($this->once())->method('myCustomAction')->with($this->article);
    
    $this->controller->addCustomAction('custom-resource', 'myCustomAction');
    $this->controller->getEntity(7,'custom-resource');
  }
  
  public function testSaveEntity() {
    $this->expectRepositoryHydrates($this->article);
    $this->expectRepositorySavesEqualTo($this->article);
    
    $this->controller->setOptionalProperties(array('tags','category','sort'));
    $return = $this->controller->saveEntity(7,
                                  (object) array(
                                                 /*'action'=>'1',
                                                 'submitted'=>'true',
                                                 'identifier'=>'7',
                                                 'dataJSON'=>'[]',
                                                 'type'=>$this->article->getEntityName(),
                                                 */
                                                 'title'=>'blubb',
                                                 'content'=>'content',
                                                 'tags'=>NULL
                                                ),
                                  'form'
                                 );
    
    $this->assertEquals('content', $this->article->getContent());
    $this->assertEquals('blubb', $this->article->getTitle());
    
    $this->assertSame($this->article, $return);
  }
  
  public function testInsertEntity() {
    $newArticle = new Article('the title', 'the content');
    $this->expectRepositorySavesEqualTo($newArticle);
    
    $this->controller->setOptionalProperties(array('tags','category','sort'));
    $this->controller->insertEntity((object) array(
                                      'title'=>'the title',
                                      'content'=>'the content',
                                      'tags'=>NULL
                                     )
                                   );
    
    $this->assertInstanceOf('Psc\CMS\Service\MetadataGenerator', $this->controller->getResponseMetadata(), 'insert Entity muss Metadata haben für open Tab');
  }


  public function testDeleteEntity() {
    $this->expectRepositoryHydrates($this->article);
    $this->expectRepositoryDeletes($this->article); // nicht removes weil removes würde nicht flushen
    
    $return = $this->controller->deleteEntity(7);
    $this->assertSame($this->article, $return);
    // der test ist blöd, weil das nur doctrine beim flush macht
    // acceptance tests failen aber
    $this->assertNotNull($return->getIdentifier(), 'identifier des alten entities darf nicht auf null gesetzt werden');
  }

  public function testPatchEntity() {
    $this->expectRepositoryHydrates($this->article);
    $this->expectRepositorySavesEqualTo($this->article);
    
    $article = $this->controller->patchEntity(7, (object) array('title'=>'the patched title'));
    
    $this->assertEquals('the patched title', $article->getTitle());
  }
  
  
  public function testInsertWithPreviewRevision_returnsAnOpenWindowInResponseMeta_acceptance() {
    $newArticle = new Article('the title', 'the content');
    $this->expectRepositorySavesEqualTo($newArticle);

    $viewRelation = new LinkRelation('view', '/articles/new+article');
    
    $this->controller->expects($this->once())->method('getLinkRelationsForEntity')
                     ->will($this->returnValue(array($viewRelation)));
    
    $this->controller->setOptionalProperties(array('tags','category','sort'));
    $this->controller->insertEntityRevision(
      $revision = 'preview-insert-172849',
      (object) array(
        'title'=>'the title',
        'content'=>'the content',
        'tags'=>NULL
      )
    );
    
    $this->assertInstanceOf(
      'Psc\CMS\Service\MetadataGenerator',
      $meta = $this->controller->getResponseMetadata(),
      'insert Entity muss Metadata haben für open Tab'
    );
    
    $this->assertDefinesRevisionLink($meta, $revision);
  }
  
  protected function assertDefinesRevisionLink($meta, $revision) {
    // see metadataGenerator for Details
    $meta = $meta->toArray();
    
    $this->assertEquals(
      $revision,
      @$meta['revision']
    );

    $this->assertNotEmpty(
      $meta['links'],
      'links for relations have to be defined '.print_r($meta['links'], true)
    );
  }
  
  public function testSaveEntityWithPreviewRevision_returnsAnOpenWindowInResponseMeta_acceptance() {
    $this->expectRepositoryHydrates($this->article);
    //$this->expectRepositorySavesEqualTo($this->article);
    
    $viewRelation = new LinkRelation('view', '/articles/'.$this->article->getId());
    
    $this->controller->expects($this->once())->method('getLinkRelationsForEntity')
                     ->will($this->returnValue(array($viewRelation)));
    
    $this->controller->setOptionalProperties(array('tags','category','sort'));
    
    $revision = 'preview-172849';
    $return = $this->controller->saveEntityAsRevision(
                7,
                $revision,
                (object) array(
                  'title'=>'blubb',
                  'content'=>'content',
                  'tags'=>NULL
                ),
                'form'
              );
    
    $this->assertInstanceOf('Psc\CMS\Controller\ResponseMetadataController', $this->controller);
    $this->assertNotEmpty($meta = $this->controller->getResponseMetadata(), 'controller should define response meta');    
    
    $this->assertDefinesRevisionLink($meta, $revision);
  }
  
  public function testSetRepository() {
    $this->controller->setRepository($otherRep = $this->getMock('Psc\Doctrine\EntityRepository', array(), array(), '', FALSE));
    $this->assertAttributeSame($otherRep, 'repository', $this->controller);
  }
  
  public function testGetEntitiesForAutoComplete() {
    $this->expectRepositoryAutoCompletes($this->articles);
    
    $exported = $this->controller->getEntities(array('search'=>NULL,'autocomplete'=>'true'));
    
    $this->assertInternalType('array', $exported);
    $this->assertCount(count($this->articles), $exported, 'Anzahl der Exportierten Items ist nicht die der vom Repository autocompleteten');
    
    foreach ($exported as $key=>$item) {
      $info = sprintf('Item %d', $key);
      $this->assertArrayHasKey('tab', $item, $info);
      $this->assertArrayHasKey('ac', $item, $info);
      $this->assertArrayHasKey('identifier', $item, $info);
    }
  }
}
?>