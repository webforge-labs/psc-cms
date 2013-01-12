<?php

namespace Psc\UI;

/**
 * 
 */
class UploadService extends \Psc\HTML\JooseBase implements \Psc\JS\JooseSnippetWidget {
  
  /**
   * @var string
   */
  protected $apiUrl;
  
  /**
   * @var string
   */
  protected $uiUrl;
  
  public function __construct($apiUrl, $uiUrl) {
    parent::__construct('Psc.UI.UploadService');
    $this->setApiUrl($apiUrl);
    $this->setUiUrl($uiUrl);
  }
  
  public static function create($apiUrl, $uiUrl) {
    return new static($apiUrl, $uiUrl);
  }
  
  protected function doInit() {
    $this->autoLoadJoose(
      $this->getJooseSnippet()
    );
  }
  
  public function getJooseSnippet() {
    return $this->createJooseSnippet(
      'Psc.UploadService',
      array(
        'ajaxService'=>$this->jsExpr('main'),
        'exceptionProcessor'=>$this->jsExpr('main'),
        'apiUrl'=>$this->getApiUrl(),
        'uiUrl'=>$this->getUiUrl()
      )
    );
  }
  
  /**
   * @param string $apiUrl
   */
  public function setApiUrl($apiUrl) {
    $this->apiUrl = $apiUrl;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getApiUrl() {
    return $this->apiUrl;
  }
  
  /**
   * @param string $uiUrl
   */
  public function setUiUrl($uiUrl) {
    $this->uiUrl = $uiUrl;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getUiUrl() {
    return $this->uiUrl;
  }
}
?>