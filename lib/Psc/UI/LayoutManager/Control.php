<?php

namespace Psc\UI\LayoutManager;

use Psc\JS\JooseSnippetWidget;

class Control extends \Psc\HTML\JooseBase implements JooseSnippetWidget {

  /**
   * ClassName for the Psc.UI.LayoutManagerComponent
   * 
   * @var string
   */
  protected $type;

  /**
   * @var object
   */
  protected $params;

  /**
   * Label for the Button (maybe optional in future)
   * 
   * @var string
   */
  protected $label;

  /**
   * Section for the layoutmanager column (css-class-string)
   * 
   * @var string
   */
  protected $section = 'misc';

  public function __construct($type, $params = array(), $label = NULL, $section = NULL) {
    $this->type = $type;
    $this->params = (object) $params;
    $this->label = $label;

    if (isset($section))
      $this->section = $section;
  }

  public function getJooseSnippet() {
    return $this->createJooseSnippet(
      'Psc.UI.LayoutManager.Control', array(
        'params'=>$this->params,
        'type'=>$this->type,
        'label'=>$this->label,
        'section'=>$this->section
      )
    );
  }

  protected function doInit() {
  }
}
