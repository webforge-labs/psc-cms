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
    $pane = new SplitPane(70);
    
    $pane->setLeftContent(
      $layout = Form::group($this->label, NULL)
    );
    $layout->getContent()->div->setStyle('min-height','600px');
    
    $button = function ($type, $label, $data = NULL) {
      $button = new Button($label);
      if ($data) {
        $button->addData('layoutContent',$data);
      }
      $button->getHTML()->setAttribute('title', $type);
      return $button;
    };
    
    $pane->setRightContent(
      $scrollable = HTML::tag('div', Array(
        Accordion::create(array('autoHeight'=>true))
        ->addSection('Text und Bilder', array(
          $button('headline', 'Überschrift'),
          $button('sub-headline', 'Zwischenüberschrift'),
          $button('paragraph', 'Absatz'),
          $button('list', 'Aufzählung'),
          $button('image', 'Bild'),
          $button('downloadslist', 'Download-Liste'),
          $button('websitewidget', 'Kalender', (object) array('label'=>'Kalender', 'name'=>'calendar'))
        ))
        //->addSection('weitere Elemente', array(
          
        //))
        ->html()
          ->addClass('\Psc\right-accordion')
        ,
        
        Group::create(
          'Magic Box',
          array(
            Form::textarea(NULL, array('disabled','magic'))->setStyle('width', '100%')->addClass('magic-box'),
            new Button('umwandeln und hinzufügen'),
            Form::hint('in die Magic Box kann ein gesamter Text eingefügt werden. Der Text wird dann analysiert und automatisch in Abschnitte und Elemente unterteilt. Die neuen Elemente werden immer ans Ende des Layouts angehängt.')
          )
        )->addClass('magic-helper')
      ))->addClass('should-scroll')
    );
    
    $this->html = $pane->html()->addClass('\Psc\serializable')->addClass('\Psc\layout-manager');

    $this->autoLoadJoose(
      $this->createJooseSnippet(
        'Psc.UI.LayoutManager',
        array(
          'widget'=>$this->widgetSelector(),
          'uploadService'=>$this->uploadService->getJooseSnippet(),
          'serializedWidgets'=>$this->serializedWidgets
        )
      )
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