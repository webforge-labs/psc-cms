<?php

namespace Psc\UI;

use Psc\CMS\EntityMeta;
use Psc\HTML\HTMLInterface;
use Psc\Code\Code;
use Psc\CMS\Item\Buttonable;

/**
 *
 * @TODO vertical buttons und tests für die setter / getter der flags
 */
class DropBox2 extends \Psc\HTML\JooseBase implements \Psc\JS\JooseSnippetWidget {
  
  const MULTIPLE = 1;
  const VERTICAL_BUTTONS = 2;
  
  /**
   * FormName
   * @var string
   */
  protected $name;
  
  /**
   * @var mixed
   */
  protected $assignedValues;
  
  /**
   * EntityMeta der Items in der DropBox
   * 
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var bitmap
   */
  protected $flags;
  
  /**
   * @var string
   */
  protected $connectWith;
  
  /**
   * @var string
   */
  protected $label;
  
  
  protected $buttonSnippets;
  
  public function __construct($name, EntityMeta $entityMeta, $valuesCollection = array(), $flags = 0, $label = NULL) {
    $this->assignedValues = $valuesCollection; 
    $this->setEntityMeta($entityMeta);
    $this->flags = $flags;
    $this->name = $name;
    
    parent::__construct('Psc.UI.DropBox');
    if (isset($label))
      $this->setLabel($label);
  }
  
  protected function doInit() {
    list ($buttons, $this->buttonSnippets) = $this->convertButtons();
    $this->html = HTML::tag('div', $buttons, array('class'=>'\Psc\drop-box'));
    
    if (isset($this->label)) {
      \Psc\UI\Form::attachLabel($this->html, $this->label);
    }
    
    $this->autoLoadJoose(
      $this->getJooseSnippet()
    );
  }
  
  public function getJooseSnippet() {
    $params = array();
    $params['name'] = $this->name;
    
    if (isset($this->connectWith)) {
      $params['connectWith'] = $this->connectWith;
    }
    
    $params['multiple'] = ($this->flags & self::MULTIPLE) == self::MULTIPLE;
    $params['widget'] = $this->widgetSelector();
    
    if (is_array($this->buttonSnippets)) {
      // das ist natürlich nicht so schön, weil wir die buttons hier flach übergeben
      // aber wir lesen diese in der dropbox im moment eh noch nicht aus
      // d.h. das brauchen wir nur um die lade reihenfolge zu faken
      foreach ($this->buttonSnippets as $key=>$snippet) {
        $params['button'.$key] = $snippet;
      }
    }
    
    return $this->createJooseSnippet($this->jooseClass, $params);
  }
  
  /**
   * Wandelt die assignedValues in Buttons um
   *
   * die JooseSnippets werden gesammelt, wenn sie extrahierbar sind
   * @return list($buttons, $snippets)
   */
  protected function convertButtons() {
    $buttons = array();
    $snippets = array();
    
    if (isset($this->assignedValues)) {
      foreach ($this->assignedValues as $item) {
        if ($item instanceof \Psc\UI\ButtonInterface) {
          $button = $item;
        } elseif ($item instanceof \Psc\CMS\Entity) {
          $button = $this->entityMeta->getAdapter($item)
            ->setButtonMode(Buttonable::CLICK | Buttonable::DRAG) 
            ->getDropBoxButton();
        } else {
          throw new \InvalidArgumentException(Code::varInfo($item).' kann nicht in einen Button umgewandelt werden');
        }
        
        
        if ($button instanceof \Psc\JS\JooseSnippetWidget) {
          $button->disableAutoLoad();
          
          // das bevor getJooseSnippet() machen damit widgetSelector im snippet geht
          // aber NACH disableAutoLoad()
          $button->html()->addClass('assigned-item');
          
          $snippets[] = $button->getJooseSnippet();
        } else {
          $button->html()->addClass('assigned-item');
          
        }
      
        $buttons[] = $button;
      }
    }
    
    return array($buttons, $snippets);
  }
  
  /**
   * @param Psc\CMS\EntityMeta $entityMeta
   */
  public function setEntityMeta(EntityMeta $entityMeta) {
    $this->entityMeta = $entityMeta;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }
  
  /**
   * @param mixed $assignedValues
   */
  public function setAssignedValues($assignedValues) {
    $this->assignedValues = $assignedValues;
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function getAssignedValues() {
    return $this->assignedValues;
  }
  
  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Verbindet die DropBox mit anderen DropBoxen
   * 
   * ein jquery selector
   * @param string $connectWith
   */
  public function setConnectWith($connectWith) {
    $this->connectWith = $connectWith;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getConnectWith() {
    return $this->connectWith;
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
  
  public function setMultiple($bool) {
    if ($bool) {
      $this->flags |= self::MULTIPLE;
    } else {
      $this->flags &= ~self::MULTIPLE;
    }
    return $this;
  }

  public function getMultiple() {
    return $this->flags & self::MULTIPLE === self::MULTIPLE;
  }

  public function setVerticalButtons($bool) {
    if ($bool) {
      $this->flags |= self::VERTICAL_BUTTONS;
    } else {
      $this->flags &= ~self::VERTICAL_BUTTONS;
    }
    return $this;
  }
  
  public function getVerticalButtons() {
    return $this->flags & self::VERTICAL_BUTTONS === self::VERTICAL_BUTTONS;
  }
}
?>