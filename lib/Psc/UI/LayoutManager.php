<?php

namespace Psc\UI;

/**
 * LayoutManager ist das Pflege Tool für einen ContentStream
 */
class LayoutManager extends \Psc\HTML\JooseBase {
  
  /**
   * @var string
   */
  protected $label;
  
  /**
   * @var array
   */
  protected $serializedWidgets;
  
  /**
   * @var Psc\UI\UploadService
   */
  protected $uploadService;
  
  public function __construct($label, UploadService $uploadService, Array $serializedWidgets = array()) {
    parent::__construct('Psc.UI.LayoutManager');
    $this->setLabel($label);
    $this->setSerializedWidgets($serializedWidgets);
    $this->setUploadService($uploadService);
  }
  
  protected function doInit() {
    $control = function ($type, $label, $data = NULL) {
      $snippet = \Psc\JS\JooseSnippet::create(
        'Psc.UI.LayoutManager.Control', array(
          'params'=>$data,
          'type'=>$type,
          'label'=>$label
        )
      );
      return $snippet;
    };
    
    $this->html = new HTMLTag('div', NULL, array('class'=>array('joose-widget-wrapper', '\Psc\serializable')));

    $this->autoLoadJoose(
      $this->createJooseSnippet(
        'Psc.UI.LayoutManager',
        array(
          'widget'=>$this->widgetSelector(),
          'uploadService'=>$this->uploadService->getJooseSnippet(),
          'serializedWidgets'=>$this->serializedWidgets,
          'controls'=>array(
            $control('Headline', 'Überschrift', (object) array('level'=>1)),
            $control('Headline', 'Zwischenüberschrift', (object) array('level'=>2)),
            $control('Paragraph', 'Absatz'),
            $control('Li', 'Aufzählung'),
            $control('Image', 'Bild'),
            $control('DownloadsList', 'Download-Liste', (object) array('headline'=>'', 'downloads'=>array())),
            $control('WebsiteWidget', 'Kalender', (object) array('label'=>'Kalender', 'name'=>'calendar'))
          )
        )
      )->addRequirement('Psc.UI.LayoutManager.Control')
    );
  }
  
  /**
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
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
}
?>