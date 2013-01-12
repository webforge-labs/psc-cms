<?php

namespace Psc\UI;

class SplitPane extends \Psc\HTML\Base {
  
  protected $leftContent;
  protected $rightContent;
  
  /**
   * Die Breite des linken SplitPanes
   * 
   * @var int 0-100 in Prozent
   */
  protected $width;
  
  protected $space = 1;
  
  public function __construct($width = 50, $leftContent = NULL, $rightContent = NULL, $space = 1) {
    $this->leftContent = $leftContent;
    $this->rightContent = $rightContent;
    $this->width = (int) $width;
    $this->space = $space;
  }
  
  protected function doInit() {
    $avaibleWidth = 100;
      
    $leftSpace = (int) floor($this->space/2);
    $rightSpace = $this->space - $leftSpace;
      
    $left = $this->width-$leftSpace;
    $right = ($avaibleWidth-$this->width)-$rightSpace;
      
    $this->html = HTML::tag('div',
        (object) array(
          'left'=>HTML::tag('div', $this->leftContent, array('class'=>'left'))
                      ->setStyles(array(
                          'float'=>'left',
                          'width'=>$left.'%',
                          'height'=>'100%',
                          'margin-right'=>$this->space.'%'
                          )
                      )
          ,
          'right'=>HTML::tag('div', $this->rightContent, array('class'=>'right'))
                    ->setStyles(array(
                      'width'=>$right.'%',
                      'float'=>'left',
                      'height'=>'100%'
                      )
                    )
          ,
          'clear'=>HTML::tag('div', NULL, array('style'=>'clear: left'))
        ),
        array('class'=>'\Psc\splitpane')
    );
  }
  
  /**
   * @param mixed $leftContent
   * @chainable
   */
  public function setLeftContent($leftContent) {
    $this->leftContent = $leftContent;
    
    if ($this->html) {
      $this->getContent()->left->setContent($leftContent);
    }
    return $this;
  }

  /**
   * @param mixed $rightContent
   * @chainable
   */
  public function setRightContent($rightContent) {
    $this->rightContent = $rightContent;
    
    if ($this->html) {
      $this->getContent()->right->setContent($rightContent);
    }
    return $this;
  }

  /**
   * @return mixed
   */
  public function getLeftContent() {
    return $this->leftContent;
  }
  
  public function getLeftTag() {
    return $this->getContent()->left;
  }

  /**
   * @return mixed
   */
  public function getRightContent() {
    return $this->rightContent;
  }

  public function getRightTag() {
    return $this->getContent()->right;
  }
}
?>