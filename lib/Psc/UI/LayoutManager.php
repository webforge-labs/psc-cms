<?php

namespace Psc\UI;

use Psc\TPL\ContentStream\ContentStream;
use Psc\UI\LayoutManager\Control;
use Webforge\Common\ArrayUtli as A;
use Psc\JS\JooseSnippetWidget;
use Psc\CMS\Translation\Container as TranslationContainer;

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

  /**
   * @var array (range)
   */
  protected $headlines = array();

  protected $jsParams = array();
  
  public function __construct(UploadService $uploadService, Array $serializedWidgets = array()) {
    parent::__construct('Psc.UI.LayoutManager');
    $this->setSerializedWidgets($serializedWidgets);
    $this->setUploadService($uploadService);
    $this->controls = array();
    $this->headlines = array(1,2);
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

  public function initControlsFor(ContentStream $cs, TranslationContainer $translationContainer) {
    $translator = $translationContainer->getTranslator();
    $trans = function($key) use ($translator) {
      return $translator->trans($key, array(), 'cms');
    };

    if ($cs->getType() === 'page-content') {
      foreach ($this->headlines as $level) {
        $this->addNewControl(
          'Headline',
          (object) array('level'=>$level), 
          sprintf(
            '%s (H%d)', 
            $level === 1 ? $trans('sce.widget.headline').' ' : $trans('sce.widget.headline'), 
            $level
          ),
          'text'
        );
      }

      $this->addNewControl('Paragraph', NULL, $trans('sce.widget.paragraph'), 'text');
      $this->addNewControl('Li', NULL, $trans('sce.widget.li'), 'text');
      $this->addNewControl('Image', NULL, $trans('sce.widget.image'), 'images');
      $this->addNewControl('DownloadsList', (object) array('headline'=>'', 'downloads'=>array()), $trans('sce.widget.downloadsList'), 'text');
      $this->addNewControl('WebsiteWidget', (object) array('label'=>$trans('sce.widget.calendar'), 'name'=>'calendar'), $trans('sce.widget.calendar'), 'misc');
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
  public function addNewControl($type, $params = NULL, $label = NULL, $section = NULL) {
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

  public function setHeadlines(array $levels) {
    $this->headlines = $levels;
    return $this;
  }
}
