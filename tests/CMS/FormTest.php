<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\Form
 */
class FormTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $form;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Form';
    parent::setUp();
    
    $this->form = new Form(NULL, '/test', 'get');
  }
  
  public function testAcceptance() {
    $this->html = $this->form;
    
    $this->test->css('form.psc-cms-ui-form')->count(1)
      ->hasAttribute('method','get')
      ->hasAttribute('action', '/test')
    ;
  }

  public function testAddsHeaders() {
    $this->form->setMethod('post');
    $this->form->setHTTPHeader('X-Psc-Cms-Request-Method','PUT');
    $this->assertEquals('post',$this->form->getMethod());
    
    $this->html = $this->form;
    $this->test->css('form.psc-cms-ui-form')->count(1)
      ->hasAttribute('method', 'post')
      ->test('input[type="hidden"][name="X-Psc-Cms-Request-Method"]')
        ->count(1)
        ->hasAttribute('value', 'PUT')
        ->hasClass('psc-cms-ui-http-header')
      ->end()
    ;
  }
    
  public function testAddsNotHeadersTwice() {
    $this->form->setHTTPHeader('X-Psc-Cms-Request-Method','PUT');
    $this->form->setHTTPHeader('X-Psc-Cms-Request-Method','PUT');
    
    $this->html = $this->form;
    $this->test->css('form.psc-cms-ui-form')->count(1)
      ->test('input[type="hidden"][name="X-Psc-Cms-Request-Method"]')->count(1);
  }
  
  public function testAddsPostData() {
    $this->form->addPostData(array(
      'k1'=>'v1',
      'k2'=>'v2',
      'k3'=>array('complex')
    ));
    
    $this->html = $this->form;
    $this->test->css('form.psc-cms-ui-form')->count(1)
      ->test('input[type="hidden"][name="k1"][value="v1"]')->count(1)->end()
      ->test('input[type="hidden"][name="k2"][value="v2"]')->count(1)->end()
      ->test('input[type="hidden"][name="k3"][value="Array"]')->count(1)->end()
    ;
  }
}
?>