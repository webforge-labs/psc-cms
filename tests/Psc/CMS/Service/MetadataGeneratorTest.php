<?php

namespace Psc\CMS\Service;

/**
 * @group class:Psc\CMS\Service\MetadataGenerator
 */
class MetadataGeneratorTest extends \Psc\Code\Test\Base {
  
  protected $metadataGenerator;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\MetadataGenerator';
    parent::setUp();
    //$this->metadataGenerator = new MetadataGenerator();
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
}
?>