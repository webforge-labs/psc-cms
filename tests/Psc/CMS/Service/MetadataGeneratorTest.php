<?php

namespace Psc\CMS\Service;

use Psc\Net\Service\LinkRelation;

/**
 * @group class:Psc\CMS\Service\MetadataGenerator
 */
class MetadataGeneratorTest extends \Psc\Code\Test\Base {
  
  protected $metadataGenerator;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\MetadataGenerator';
    parent::setUp();
    //$this->metadataGenerator = new MetadataGenerator();

    $this->entity = new \Psc\Doctrine\TestEntities\Article('Lorem Ipsum:', 'Lorem Ipsum Dolor sit amet... <more>');
    $this->entity->setId(7);
  }
  
  public function testAcceptance() {
    $validatorException = new \Psc\Form\ValidatorException('Ich habe einen User-Fehler verursacht');
    $validatorException->field = 'myField';
    $validatorException->data = NULL;
    $validatorException->label = 'My Field';
    
    $meta = MetadataGenerator::create()
      ->validationError($validatorException)
      ->toHeaders();
    
    $this->assertEquals(
      array(
        'X-Psc-Cms-Meta'=>json_encode(
          array(
            'data'=>new \stdClass(),
            'validation'=>Array(
              array(
                'msg'=>'Ich habe einen User-Fehler verursacht',
                'field'=>'myField',
                'data'=>NULL,
                'label'=>'My Field'
              )
            )
          )
        )
      ),
      $meta
    );
  }
  
  public function testOpenTab() {
    $tabOpenable = $this->doublesManager->createTabOpenable('/url/des/tabs', 'label des Tabs');

    $meta = MetadataGenerator::create()
      ->openTab($tabOpenable)
      ->toArray();
      
    $this->assertEquals(
      array(
        'data'=>(object) array(
          'tab'=>(object) array(
            'url'=>'/url/des/tabs',
            'label'=>'label des Tabs',
            'id'=>'url-des-tabs'
          )
        )
      ),
      $meta
    );
  }
  
  public function testEntityResponseMetaAddsDataAndItemMetaForAnEntity() {
    $viewRelation = new LinkRelation('view', '/articles/7');
    
    $meta = MetadataGenerator::create()
      ->entity($this->entity, array($viewRelation))
      ->toArray();
      
    $this->assertNotEmpty(
      $meta['links'],
      'links have to be defined and in meta for entity '.print_r($meta, true)
    );
    
    $this->assertEquals(
      'view',
      $meta['links'][0]->rel,
      'links[0].rel is wrong'.print_r($meta, true)
    );
    
    $this->assertEquals(
      '/articles/7',
      $meta['links'][0]->href,
      'links[0].href is not defined'.print_r($meta, true)
    );
  }

  public function testEntityResponseMetaAddsRevisionInItemMeta() {
    $meta = MetadataGenerator::create()
      ->revision($revision = 'some-saved-revision-17')
      ->toArray();
    
    $this->assertEquals(
      $revision,
      @$meta['revision'],
      'revision has to be defined in meta'."\n".print_r($meta, true)
    );
  }
}
?>