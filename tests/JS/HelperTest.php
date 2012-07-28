<?php

namespace Psc\JS;

/**
 * @group class:Psc\JS\Helper
 */
class HelperTest extends \Psc\Code\Test\Base {
  
  protected $helper;
  
  public function setUp() {
    $this->chainClass = 'Psc\JS\Helper';
    parent::setUp();
    //$this->helper = new Helper();} 
  }
  
  public function testDocPartDoesNotConvertHTMLPartsWithDoctype() {
    $this->markTestIncomplete('todo');
  }

  public function testDocPartDoesNotConvertHTMLPartsWithHTMLBeginning() {
    $this->markTestIncomplete('todo');
  }
  
  public function testAcceptance() {
    
    $export =     
      array (
       'avaibleItems' =>
       array (
         0 =>
         (object)array(
            'label' => 'Tag: Russland',
            'value' => 1,
            'tci' =>
           (object)array(
              'identifier' => 1,
              'id' => 'entities-tag-1-form',
              'label' => 'Tag: Russland',
              'fullLabel' => 'Tag: Russland',
              'drag' => false,
              'type' => 'entities-tag',
              'url' => NULL,
           ),
         ),
         1 =>
         (object)array(
            'label' => 'Tag: Demonstration',
            'value' => 2,
            'tci' =>
           (object)array(
              'identifier' => 2,
              'id' => 'entities-tag-2-form',
              'label' => 'Tag: Demonstration',
              'fullLabel' => 'Tag: Demonstration',
              'drag' => false,
              'type' => 'entities-tag',
              'url' => NULL,
           ),
         ),
         2 =>
         (object)array(
            'label' => 'Tag: Protest',
            'value' => 4,
            'tci' =>
           (object)array(
              'identifier' => 4,
              'id' => 'entities-tag-4-form',
              'label' => 'Tag: Protest',
              'fullLabel' => 'Tag: Protest',
              'drag' => false,
              'type' => 'entities-tag',
              'url' => NULL,
           ),
         ),
         3 =>
         (object)array(
            'label' => 'Tag: Wahl',
            'value' => 5,
            'tci' =>
           (object)array(
              'identifier' => 5,
              'id' => 'entities-tag-5-form',
              'label' => 'Tag: Wahl',
              'fullLabel' => 'Tag: Wahl',
              'drag' => false,
              'type' => 'entities-tag',
              'url' => NULL,
           ),
         ),
         4 =>
         (object)array(
            'label' => 'Tag: Pr├ñsidentenwahl',
            'value' => 10,
            'tci' =>
           (object)array(
              'identifier' => 10,
              'id' => 'entities-tag-10-form',
              'label' => 'Tag: Pr├ñsidentenwahl',
              'fullLabel' => 'Tag: Pr├ñsidentenwahl',
              'drag' => false,
              'type' => 'entities-tag',
              'url' => NULL,
           ),
         )
      ),
    );
      
    $js = Helper::convertValue($export);
    
    // wo liegen die unterschiede zwischen json und javascript? encoding?
    $decoded = json_decode($js);
    foreach ($decoded->avaibleItems as $item) {
      $this->assertInternalType('object', $item);
      $this->assertAttributeInternalType('string', 'label', $item);
      $this->assertAttributeInternalType('integer', 'value', $item);
      $this->assertAttributeInternalType('object', 'tci', $item);
      
      
      $tci = $item->tci;
      $this->assertAttributeInternalType('integer', 'identifier', $tci);
      $this->assertAttributeInternalType('bool', 'drag', $tci);
      $this->assertAttributeInternalType('string', 'label', $tci);
      $this->assertAttributeInternalType('string', 'fullLabel', $tci);
      $this->assertAttributeInternalType('string', 'type', $tci);
    }
  }
}
?>