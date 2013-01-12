<?php

namespace Psc\CMS;

use Psc\Code\Code;
use Psc\UI\HTML;
use Psc\Form\HTML as FormHTML;

class Form extends \Psc\HTML\Base {
  
  /**
   * @var string
   */
  protected $formId;

  /**
   * Die Action der Form (URI)
   * @var string
   */
  protected $action; 
  
  /**
   * Die Method der Form
   *
   * post|get
   * @var string
   */
  protected $method = 'post';
  
  /**
   * @var array
   */
  protected $httpHeaders = array();
  
  /**
   * @var array
   */
  protected $postData = array();
  
  /**
   * Der Innere Content der Form
   * 
   * @var stdClass
   */
  protected $content;
  
  public function __construct($formId = NULL, $action = NULL, $method = NULL) {
    $this->formId = $formId ?: uniqid('form');

    if (isset($method))
      $this->setMethod($method);
    
    if (isset($action)) {
      if ($action instanceof RequestMeta) {
        $this->setRequestMeta($action);
      } else {
        $this->setAction($action);
      }
    }

    $this->content = new \stdClass;
    $this->setUp();
  }
  
  public function setRequestMeta(RequestMeta $requestMeta) {
    $this->setAction($requestMeta->getUrl());
    $this->setHTTPHeader('X-Psc-Cms-Request-Method', $requestMeta->getMethod());
    return $this;
  }
  
  protected function setUp() {
  }
  
  protected function doInit() {
    $this->html = HTML::tag('form', $this->content, array('method'=>$this->method,'action'=>$this->action,'class'=>'\Psc\form'));
    $this->html->guid($this->formId);

    foreach ($this->httpHeaders as $name => $value) {
      // fHTML ist mehr basic und macht nur hidden, ohne ids ohne schnickschnack
      $this->html->content->$name = FormHTML::hidden($name, $value)->addClass('psc-cms-ui-http-header');  // \Psc geht nicht weil kein UI
    }
    
    foreach ($this->postData as $key => $value) {
      $this->html->content->$key = FormHTML::hidden($key, $value);
    }
  }
  
  public function setContent($name, $content) {
    $this->content->$name = $content;
    return $this;
  }
  
  
  public function addPostData(Array $data) {
    // @todo vll hier auch zu controlfields hinzufügen?
    
    foreach ($data as $key => $value) {
      $this->postData[$key] = $value;
    }
    
    return $this;
  }
  
  public function setHTTPHeader($name, $value) {
    $this->httpHeaders[$name] = $value;
    return $this;
  }

  /**
   * Gibt alle Felder zurück, die in dem Formular zur "Steuerung" dienen und keine echten Daten beeinhalten
   * 
   * @return array von strings/arrays von namen der Felder
   */
  public function getControlFields() {
    return array_keys($this->httpHeaders);
  }
  
  /**
   * @return Psc\HTML\Tag das <form> Tag
   */
  public function getTag() {
    return $this->html;
  }
  
  /**
   * @param string $action
   * @chainable
   */
  public function setAction($action) {
    $this->action = $action;
    return $this;
  }

  /**
   * @return string
   */
  public function getAction() {
    return $this->action;
  }
  
  /**
   * @param string $method
   * @chainable
   */
  public function setMethod($method) {
    Code::value($method, 'post', 'get');
    $this->method = $method;
    return $this;
  }

  /**
   * @return string
   */
  public function getMethod() {
    return $this->method;
  }
  
  /**
   * @return string
   */
  public function getFormId() {
    return $this->formId;
  }
}
?>