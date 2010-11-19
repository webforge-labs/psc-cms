<?php

class FluidGrid_Box extends FluidGrid_HTMLElement {
  
  protected $headline;

  /**
   * Die ID der Box
   * @var string
   */  
  protected $name;

  /**
   * 
   * @var bool
   */
  protected $toggle;

  public function __construct($headline, FluidGrid_Model $content, $toggle = TRUE) {
    $this->setHeadline($headline);
    $this->toggle = ($toggle == TRUE);

    parent::__construct('div', $content, array('class'=>'box'));
  }
  

  public static function factory($headline, $content, $toggle = TRUE) {
    return new FluidGrid_Box($headline, $content, $toggle);
  }


  public function getName() {
    if (!isset($this->name) && isset($this->headline)) {
      /* wir generieren den Namen aus der headline */
      $this->name = FluidGrid_Framework::string2id($this->headline);
      
    }
      
    return $this->name;
  }

  public function getInnerHTML() {
    $html = NULL;

    /* headline mit oder ohne toggle */
    if ($this->getHeadline() != NULL) {
      $headline = ($this->toggle) ? 
          new FluidGrid_HTMLElement('a',new FluidGrid_String($this->getHeadline()), array('href'=>'#','id'=>'toggle-'.$this->getName())) 
          : new FluidGrid_String($this->getHeadline());

      $html = $this->indent().'<h2>'.$headline.'</h2>'.$this->lb();
    }

    /* div block */
    $html .= $this->indent().'<div class="block" id="'.$this->getName().'">'.$this->lb();

    $html .= parent::getInnerHTML();

    /* / div block */
    $html .= $this->indent().'</div>'.$this->lb();
    
    return $html;
  }

  public function __toString() {
    try {
      return (string) $this->getContent();
    } catch (Exception $e) {
      print $e;
    }
  }
}

?>