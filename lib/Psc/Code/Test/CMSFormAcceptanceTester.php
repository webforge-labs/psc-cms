<?php

namespace Psc\Code\Test;

use Psc\URL\Response;

/**
 * 
 */
class CMSFormAcceptanceTester extends \Psc\SimpleObject {

  /**
   * @var Psc\Code\Test\Base
   */
  protected $testCase;
  
  /**
   * @var Psc\Code\Test\FrontendCodeTester
   */
  protected $test;
  
  /**
   * @var Psc\URL\Response
   */
  protected $response;
  
  /**
   * @var Psc\Code\Test\CMSAcceptanceTester
   */
  protected $acceptanceTester;
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  
  public function __construct(Response $Response, CMSAcceptanceTester $acceptanceTester) {
    $this->setResponse($Response);
    $this->setAcceptanceTester($acceptanceTester);
    $this->testCase = $this->acceptanceTester->getTestCase();
    $this->testCase->setHTML($this->response->getRaw());
    $this->test = $this->testCase->getCodeTester();
    $this->entityMeta = clone $this->acceptanceTester->getEntityMeta();
  }
  
  public function hasAction($action) {
    $this->test->css('form')
      ->count(1)
      ->hasAttribute('action', $action);
    return $this;
  }
  
  public function saves($id) {
    $this->hasAction($this->getUrlPrefix().'/'.$this->entityMeta->getEntityName().'/'.$id);
    $this->hasMethod('PUT');
    return $this;
  }
  
  public function inserts($subResource = NULL) {
    $this->hasAction($this->getUrlPrefix().'/'.$this->entityMeta->getEntityNamePlural().($subResource ? '/'.$subResource : NULL));
    $this->hasMethod('POST');
    return $this;
  }
  
  public function hasMethod($method) {
    $this->test->css('input[type=hidden][name="X-Psc-Cms-Request-Method"]')
      ->count(1)
      ->hasAttribute('value', mb_strtoupper($method));
    return $this;
  }
  
  // components
  
  public function hasComboBox($name = NULL) {
    if (isset($name)) {
      $this->test->css(sprintf('input.psc-cms-ui-combo-box[name*="%s"]', $name))->count(1);
    } else {
      $this->test->css('input.psc-cms-ui-combo-box')->count(1);
    }
      
    return $this;
  }
  
  public function hasComboDropBox($name) {
    $this->test->css(sprintf('div.component-wrapper.component-for-%s fieldset.psc-cms-ui-combo-drop-box-wrapper',$name))->count(1)
      ->test('input.psc-cms-ui-combo-box')->count(1)->end()
      ->test('div.psc-cms-ui-drop-box')->count(1)->end()
    ;

    return $this;
  }
  
  // Eine Sehr unspezifizierte Compononente
  public function hasComponent($name) {
    $this->test->css('div.component-wrapper.component-for-'.$name)->count(1);
    return $this;
  }

  // Eine Sehr unspezifizierte Compononente
  public function hasNotComponent($name) {
    $this->test->css('div.component-wrapper.component-for-'.$name)->count(0);
    return $this;
  }
  
  // eine Componente mit einem <input Field
  public function hasInput($name) {
    $this->test->css('div.component-wrapper input[name="'.$name.'"]')->count(1);
    return $this;
  }
  
  // buttons
  public function hasReloadButton() {
    $this->test->css('button.psc-cms-ui-button-reload')->count(1);
    return $this;
  }
  
  public function hasSaveButton() {
    $this->test->css('button.psc-cms-ui-button-save')->count(1);
    return $this;
  }
  
  public function hasSaveCloseButton() {
    $this->test->css('button.psc-cms-ui-button-save-close')->count(1);
    return $this;
  }

  public function hasSaveCloseThisButton() {
    $this->test->css('button.psc-cms-ui-button-save-close-this')->count(1);
    return $this;
  }

  public function hasStandardButtons() {
    $this->hasReloadButton();
    $this->hasSaveButton();
    $this->hasSaveClosebutton();
    return $this;
  }

  public function hasStandardInsertButtons() {
    $this->hasReloadButton();
    $this->hasSaveButton();
    $this->hasSaveCloseThisButton();
    return $this;
  }
  
  /**
   * @return Psc\JS\jQuery
   */
  public function getRightAccordion() {
    return $this->test->css('.psc-cms-ui-form-panel .psc-cms-ui-splitpane div.right .psc-cms-ui-accordion')->getJQuery();
  }

  public function getUrlPrefix() {
    return $this->acceptanceTester->getUrlPrefix();
  }
  
  /**
   * @param Psc\URL\Response $Response
   */
  public function setResponse(Response $Response) {
    $this->response = $Response;
    return $this;
  }
  
  /**
   * @return Psc\URL\Response
   */
  public function getResponse() {
    return $this->response;
  }
  
  /**
   * @param Psc\Code\Test\CMSAcceptanceTester $acceptanceTester
   */
  public function setAcceptanceTester(CMSAcceptanceTester $acceptanceTester) {
    $this->acceptanceTester = $acceptanceTester;
    return $this;
  }
  
  /**
   * @return Psc\Code\Test\CMSAcceptanceTester
   */
  public function getAcceptanceTester() {
    return $this->acceptanceTester;
  }
  
  /**
   * @param Psc\Code\Test\TestCase $testCase
   * @chainable
   */
  public function setTestCase(TestCase $testCase) {
    $this->testCase = $testCase;
    return $this;
  }

  /**
   * @return Psc\Code\Test\TestCase
   */
  public function getTestCase() {
    return $this->testCase;
  }  
}
?>