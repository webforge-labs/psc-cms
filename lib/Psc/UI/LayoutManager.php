<?php

namespace Psc\UI;

use Psc\TPL\ContentStream\ContentStream;
use Psc\UI\LayoutManager\Control;
use Webforge\Common\ArrayUtli as A;
use Psc\JS\JooseSnippetWidget;

/**
 * LayoutManager ist das Pflege Tool für einen ContentStream
 */
class LayoutManager extends \Psc\HTML\JooseBase implements JooseSnippetWidget {

  /**
   * @var Control[]
   */
  protected $controls;
  
  /**
   * @var array
   */
  protected $serializedWidgets;
  
  /**
   * @var Psc\UI\UploadService
   */
  protected $uploadService;

  protected $jsParams = array();
  
  public function __construct(UploadService $uploadService, Array $serializedWidgets = array()) {
    parent::__construct('Psc.UI.LayoutManager');
    $this->setSerializedWidgets($serializedWidgets);
    $this->setUploadService($uploadService);
    $this->controls = array();
  }
  
  protected function doInit() {
    $this->html = new HTMLTag('div', NULL, array('class'=>array('joose-widget-wrapper', '\Psc\serializable')));

    $this->autoLoadJoose(
      $this->getJooseSnippet()
    );
  }

  public function getJooseSnippet() {
    return $this->createJooseSnippet(
      'Psc.UI.LayoutManager',
      array_merge($this->jsParams,
        array(
          'widget'=>$this->widgetSelector(),
          'container'=>$this->jsExpr('main.getContainer()'),
          'uploadService'=>$this->uploadService->getJooseSnippet(),
          'serializedWidgets'=>$this->serializedWidgets,
          'controls'=>array_map(function ($control) { return $control->getJooseSnippet(); }, $this->controls)
        )
      )
    )->addRequirement('Psc.UI.LayoutManager.Control');
  }

  public function initControlsFor(ContentStream $cs) {

    if ($cs->getType() === 'page-content') {
      $this->addNewControl('Headline', (object) array('level'=>1), 'Überschrift', 'text');
      $this->addNewControl('Headline', (object) array('level'=>2), 'Zwischenüberschrift', 'text');
      $this->addNewControl('Paragraph', NULL, 'Absatz', 'text');
      $this->addNewControl('Li', NULL, 'Aufzählung', 'text');
      $this->addNewControl('Image', NULL, 'Bild im Text', 'images');
      $this->addNewControl('DownloadsList', (object) array('headline'=>'', 'downloads'=>array()), 'Download-Liste', 'text');
      $this->addNewControl('WebsiteWidget', (object) array('label'=>'Kalender', 'name'=>'calendar'), 'Kalender', 'misc');
    }

  }

  public function hackFlatNavigationInjection(Array $navigationFlat) {
    $this->jsParams['injectNavigationFlat'] = $navigationFlat;
    return $this;
  }

  public function addControl(Control $control) {
    $this->controls[] = $control;
    return $this;
  }

  /**
   * @return Control
   */
  protected function addNewControl($type, $params = NULL, $label = NULL, $section = NULL) {
    $this->controls[] = $control = new Control($type, $params, $label, $section);
    
    return $control;
  }
  
  /**
   * @param array $serializedWidgets
   */
  public function setSerializedWidgets(Array $serializedWidgets) {
    $this->serializedWidgets = $serializedWidgets;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getSerializedWidgets() {
    return $this->serializedWidgets;
  }
  
  /**
   * @param Psc\UI\UploadService $uploadService
   */
  public function setUploadService(UploadService $uploadService) {
    $this->uploadService = $uploadService;
    return $this;
  }
  
  /**
   * @return Psc\UI\UploadService
   */
  public function getUploadService() {
    return $this->uploadService;
  }

  public function getControls() {
    return $this->controls;
  }
}
?>