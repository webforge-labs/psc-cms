<?php

namespace Psc\Code\Test;

use Psc\Code\Test\FormTesterFrontend;

class FormTesterFrontendTest extends \Psc\Code\Test\Base {

  protected $formHTML = <<< 'HTML_FORM'
<form class="main" action="" method="POST">
  <fieldset class="user-data group">
    <input type="text" name="email" value="p.scheit@ps-webforge.com" /><br />
    <br />
    <input type="text" name="name" value="" /><br />
    <textarea name="about">about MySelf and I</textarea>
    <select name="age">
      <option value="18"></option>
      <option value="19"></option>
      <option value="20"></option>
      <option selected="selected" value="21"></option>
      <option value="22"></option>
    </select>
  </fieldset>
  <fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br />
    <input type="password" name="pw1" value="" /><br />
    Bitte best&auml;tigen sie das Passwort:<br />
    <input type="password" name="pw2" value="" /><br />
    <input type="checkbox" name="rememberme" value="true" />
    <input type="radio" name="accept" value="yes" /><input type="radio" name="accept" value="no" />
  </fieldset>
  <input type="hidden" name="submitted" value="true" />
  <input type="submit">
</form>
HTML_FORM;

  protected $formHTML2 = <<< 'HTML_FORM'
<form class="main" action="" method="POST">
  <fieldset class="user-data group">
    <input type="text" name="email" value="p.scheit@ps-webforge.com" /><br />
    <br />
    <input type="text" name="name" value="" /><br />
    <textarea name="about"></textarea>
    <select name="age">
      <option value="18"></option>
      <option value="19"></option>
      <option value="20"></option>
      <option value="21"></option>
      <option value="22"></option>
    </select>
  </fieldset>
  <fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br />
    <input type="password" name="pw1" value="" /><br />
    Bitte best&auml;tigen sie das Passwort:<br />
    <input type="password" name="pw2" value="" /><br />
    <input type="checkbox" checked="checked" name="rememberme" value="true" />
    <input type="radio" name="accept" value="yes" /><input checked="checked" type="radio" name="accept" value="no" />
  </fieldset>
  <input type="hidden" name="submitted" value="true" />
  <input type="submit">
</form>
HTML_FORM;

  public function testParsing() {
    $fe = new FormTesterFrontend();
    $fe->parseFrom($this->formHTML);
    
    $this->assertEquals(Array(
      'email'=>'p.scheit@ps-webforge.com',
      'name'=>NULL,
      'pw1'=>NULL,
      'pw2'=>NULL,
      'submitted'=>'true',
      'about'=>'about MySelf and I',
      'age'=>'21',
      'rememberme'=>NULL,
      'accept'=>NULL
    ), $fe->getFields());

    $fe = new FormTesterFrontend();
    $fe->parseFrom($this->formHTML2);
    $this->assertEquals(Array(
      'email'=>'p.scheit@ps-webforge.com',
      'name'=>NULL,
      'pw1'=>NULL,
      'pw2'=>NULL,
      'submitted'=>'true',
      'about'=>NULL,
      'age'=>'18',
      'rememberme'=>'true',
      'accept'=>'no'
    ), $fe->getFields());
  }
}
?>