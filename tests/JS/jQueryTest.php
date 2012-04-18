<?php

use \Psc\JS\jQuery,
    \Psc\JS\Manager,
    Psc\XML\Helper as xml,
    Psc\HTML\HTML
;

class jQueryTest extends \Psc\Code\Test\Base {
  
  protected $formHTML = <<< 'HTML_FORM'
<form class="main" action="" method="POST">
  <fieldset class="user-data group">
    <input type="text" name="email" value="" /><br />
    <br />
    <input type="text" name="name" value="" /><br />
  </fieldset>
  <fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br />
    <input type="password" name="pw1" value="" /><br />
    Bitte best&auml;tigen sie das Passwort:<br />
    <input type="password" name="pw2" value="" /><br />
  </fieldset>
  <input type="hidden" name="submitted" value="true" />
  <input type="submit">
</form>
HTML_FORM;

  protected $c = 'Psc\JS\jQuery';


  public function testConstruct() {
    $doc = xml::doc($this->formHTML);
    $html = HTML::tag('form',HTML::tag('input'))->addClass('main');
    
   /*
    * 
    * @params $selector, $doc
    * @params $selector, $html
    * @params $selector, $htmlString
    * @params $selector, $jQuery
    */
    $this->assertInstanceOf($this->c, new jQuery('form.main', $this->formHTML));
    $this->assertInstanceOf($this->c, $form = new jQuery('form.main', $doc));
    $this->assertInstanceOf($this->c, new jQuery($doc));
    $this->assertInstanceOf($this->c, new jQuery('form.main', $html));
    return $form;
  }
  
  public function testHTML() {
    $doc = xml::doc($this->formHTML);
    
    $jQuery = new jQuery('fieldset', $doc);
    $this->assertEquals('<input type="text" name="email" value=""/><br/>'.
                        '<br/>'.
                        '<input type="text" name="name" value=""/><br/>',
                        $jQuery->html()
                       );
  }
  
  public function testFindWithJQueryConstructor_Regression() {
    $form = new jQuery('form.main', $this->formHTML);
    $fieldset = new jQuery('fieldset.password.group', $form);
    $input = new jQuery('input[type="password"][name="pw2"]', $fieldset);
    
    $this->assertTrue($input->getElement()->hasAttribute('value'));
  }
  
  public function testSelectIndex() {
    $firstInput = new jQuery('form.main fieldset.user-data.group input:nth-of-type(2)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="name" value=""/>'), $firstInput->export());
    $firstInput = new jQuery('form.main fieldset.user-data.group input:eq(1)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="name" value=""/>'), $firstInput->export());

    $firstInput = new jQuery('form.main fieldset.user-data.group input:nth-of-type(1)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="email" value=""/>'), $firstInput->export());
    $firstInput = new jQuery('form.main fieldset.user-data.group input:eq(0)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="email" value=""/>'), $firstInput->export());
  }
  
  public function testHasClass() {
    $form = new jQuery('form.main', $this->formHTML);
    $this->assertTrue($form->hasClass('main'));
    $this->assertFalse($form->hasClass('blubb'));
    
    $fieldset = $form->find('fieldset.password');
    $this->assertTrue($fieldset->hasClass('password'));
    $this->assertTrue($fieldset->hasClass('group'));
    $this->assertFalse($fieldset->hasClass('password group'));
    $this->assertFalse($fieldset->hasClass('pass'));
  }
  
  public function testQueries() {
    $form = new jQuery('form.main', $this->formHTML);
    
    $this->assertEquals(1,count($form));
    $this->assertTag(array('tag'=>'form',
                           'attributes' => array('class'=>'main')
                           ), xml::export($form->get(0))); // export macht aus dem domElement einen HTML string, den phpunit braucht
    
    $inputs = $form->find('input');
    $this->assertInstanceof($this->c, $inputs);
    
    // schöner wäre hier auch assertTag zu nehmen
    // wir testen hier aber eh teilweise funktionen aus xml helper, deshalb hier nur akzeptanz
    $ex = array (
       0 => '<input type="text" name="email" value=""/>',
       1 => '<input type="text" name="name" value=""/>',
       2 => '<input type="password" name="pw1" value=""/>',
       3 => '<input type="password" name="pw2" value=""/>',
       4 => '<input type="hidden" name="submitted" value="true"/>',
       5 => '<input type="submit"/>'
    );
    foreach ($inputs as $key =>$input) {
      $this->assertEquals($ex[$key], xml::export($input), 'Input mit Schlüssel: '.$key);
      $input = new jQuery($input);
      
      $type = FALSE;
      switch ($key) {
        case 0:
          $type = 'text';
          $name = 'email';
          break;

        case 1:
          $type = 'text';
          $name = 'name';
          break;

        case 2:
          $type = 'password';
          $name = 'pw1';
          break;

        case 3:
          $type = 'password';
          $name = 'pw2';
          break;

        case 4:
          $type = 'hidden';
          $name = 'submitted';
          break;

        case 5:
          $type = 'submit';
          $name = NULL;
          break;
      }
      $this->assertEquals($type, $input->attr('type'));
      $this->assertEquals($name, $input->attr('name'));
    }
  }
  
  
  /**
   * expectedException \Psc\System\Exception
   */
  public function testAddDeveloperUI() {
    $manager = Manager::instance();
    
    $manager->register('/js/jquery-1.5.1.min.js','jquery');
    $manager->enqueue('jquery');
        
    jQuery::addDeveloperUI($manager);
    
  }
}
?>