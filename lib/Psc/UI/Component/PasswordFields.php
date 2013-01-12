<?php

namespace Psc\UI\Component;

use Psc\UI\HTML;

/**
 * 2 Felder für die Passwort Pflege (password + confirmation)
 *
 */
class PasswordFields extends \Psc\UI\Component\Base implements \Psc\UI\Component\CompositeComponent {
  
  protected $passwordField;
  protected $confirmationField;
  
  protected function setUp() {
    $this->confirmationField = new PasswordField();
    $this->passwordField = new PasswordField();
    parent::setUp();
  }
  
  protected function initValidatorRule() {
    $this->validatorRule = 'Password';
  }
  
  protected function doInit() {
    $this->confirmationField->init();
    $this->passwordField->init();
    
    parent::doInit();
  }
  
  public function setFormValue($value) {
    // wollen wir das erlauben?
    parent::setFormValue($value);
    //$this->passwordField->setFormValue($value);
    //$this->confirmationField->setFormValue($value);
    return $this;
  }
  
  public function setFormLabel($label) {
    parent::setFormLabel($label);
    $this->passwordField->setFormLabel($label);
    return $this;
  }
  
  public function setFormName($name) {
    parent::setFormName($name);
    $this->setSubFormName($this->passwordField, 'password');
    $this->setSubFormName($this->confirmationField, 'confirmation');
    return $this;
  }
  
  public function getInnerHTML() {
    return HTML::tag(
      'div',
      (object) array(
        'password'=>$this->passwordField->getInnerHTML(),
        'confirmation'=>$this->confirmationField->getInnerHTML()
      ),
      array('class'=>'password-pane')
    )->setContentTemplate('<p style="margin-bottom: 10px;">%password%</p><p>%confirmation%</p>');
  }
}
?>