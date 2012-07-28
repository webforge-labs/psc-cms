<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\AssociationList
 */
class AssociationListTest extends \Psc\Code\Test\Base {
  
  protected $associationList;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\AssociationList';
    parent::setUp();
    $this->assoc = new AssociationList('sounds', "wird als Stimme in den Sounds:\n%list% benutzt.");
    
  }
  
  public function testAcceptance() {
    // es gibt kein Default, dass sieht scheise aus
    $assoc = new AssociationList('sounds', "Wird als Stimme in den Sounds:\n%list% benutzt.");
    $assoc
      ->withLabel('label')
      ->joinedWith(',<br />')
    ;
    
    $this->assertInstanceOf('Psc\CMS\AssociationList', $assoc);
    $this->assertInstanceOf('Closure', $assoc->getWithLabel());
    $this->assertEquals('sounds', $assoc->getPropertyName());
    $this->assertEquals("Wird als Stimme in den Sounds:\n%list% benutzt.", $assoc->getFormat());
    $this->assertEquals(',<br />', $assoc->getJoinedWith());
  }
  
  public function testWithLabelCanBeAClosure() {
    $this->assoc->withLabel(function ($item) {
      return '['.$item->getContextLabel().']';
    });
    
    $this->assertInstanceOf('Closure', $this->assoc->getWithLabel());
  }
}
?>