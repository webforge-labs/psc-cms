<?php

namespace Psc\UI;

use Psc\Code\Code;
use Webforge\Translation\Translator;
use Psc\CMS\Translation\Container as TranslationContainer;

/**
 * @todo geil wäre META_MEANING => icon, also ne klasse die "was ich sagen will" zu einem icon mappt. Weil sich das ja mal ändern kann
 * z.b. new => icon-circle-plus
 * you name it
 */
class PanelButtons extends \Psc\HTML\Base {
  
  // block0 für misc
  const CLEAR                   = 0x000002;
  
  // block bit2 für alignment
  const ALIGN_LEFT              = 0x000010;
  const ALIGN_CENTER            = 0x000020; 
  const ALIGN_RIGHT             = 0x000040; // fügt clear hinzu
  
  
  // block bit3 fuer position im panel
  const PREPEND                 = 0x000100;
  const APPEND                  = 0x000200;
  
  public function __construct(Array $buttons, TranslationContainer $translationContainer, $flags = NULL) {
    $this->translator = $translationContainer;
    $this->buttons = array();
    
    foreach ($buttons as $button) {
      if ($button === 'save') {
        $this->addSaveButton();
      } elseif ($button === 'insert-open') {
        $this->addInsertOpenButton();
      } elseif ($button === 'save-close') {
        $this->addSaveCloseButton();
      } elseif ($button === 'insert-close') {
        $this->addInsertCloseButton();
      } elseif ($button === 'insert') {
        $this->addInsertButton();
      } elseif ($button === 'reload') {
        $this->addReloadButton();
      } elseif ($button === 'reset') {
        $this->addResetButton();
      } elseif ($button === 'preview') {
        $this->addPreviewButton();
      } elseif ($button instanceof \Psc\UI\Button) {
        $this->buttons[] = $button;
      } else {
        throw new \InvalidArgumentException('Unbekannter Parameter in array $buttons (1. Parameter) : '.Code::varInfo($button));
      }
    }
    
    if ($flags === NULL) {
      $this->flags = self::ALIGN_RIGHT | self::CLEAR;
    } else {
      $this->flags = $flags;
    }
  }
  
  protected function doInit() {
    $this->html = HTML::tag('div',$this->buttons, array('class'=>'\Psc\buttonset'));
    
    if (($this->flags & self::ALIGN_RIGHT) == self::ALIGN_RIGHT) {
      $this->html
        ->setStyle('float','right')
        ->addClass('\Psc\buttonset-right');
    }
    
    if (($this->flags & self::ALIGN_LEFT) == self::ALIGN_LEFT) {
      $this->html
        ->setStyle('float','left')
        ->addClass('\Psc\buttonset-left');
    }
      
  
    if (($this->flags & self::CLEAR) == self::CLEAR) {
      $this->html->templateAppend("\n".'<div class="clear"></div>');
    }
  }
  
  public function addSaveButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('speichern', $flags, 'disk');
    $button->getHTML()
      ->addClass('\Psc\button-save');
      return $button;
  }

  public function addInsertOpenButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('neu erstellen und geöffnet lassen', $flags, 'circle-plus', NULL);
    $button->getHTML()
      ->addClass('\Psc\button-save');
      return $button;
  }
  
  public function addNewButton($button = 'Neu', $flags = self::ALIGN_LEFT) {
    $button = $this->addButton($button, $flags, 'circle-plus');
    $button->getHTML()
      ->addClass('\Psc\button-new');
    
    return $button;
  }
  
  public function addSaveCloseButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('speichern und schließen', $flags, 'disk', 'close');
    $button->getHTML()
      ->addClass('\Psc\button-save-close');
    
    return $button;
  }

  /**
   * Same Button functionality as saveClose but other icon + label
   *
   * in insert-tab
   */
  public function addInsertCloseButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('neu erstellen', $flags, 'circle-plus', 'close');
    $button->getHTML()
      ->addClass('\Psc\button-save-close');
    
    return $button;
  }

  /**
   * Same Button functionality as saveClose but other icon + label
   *
   * in insert-tab
   */
  public function addInsertButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('neu erstellen', $flags, 'circle-plus', 'close');
    $button->getHTML()
      ->addClass('\Psc\button-save-close-this');
    
    return $button;
  }
  
  /**
   * used in the save-tab
   */
  public function addReloadButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('neu laden', $flags, 'refresh', NULL);
    $button->getHTML()
      ->addClass('\Psc\button-reload');
    
    return $button;
  }

  /**
   * Button has the same functionality as the reload button, but other icon + label
   *
   * used in the insert-tab
   */
  public function addResetButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('zurücksetzen', $flags, 'arrowreturn-1-w', NULL);
    $button->getHTML()
      ->addClass('\Psc\button-reload');
    
    return $button;
  }
  
  /**
   * Button used to preview the document
   *
   * used in the edit-tab
   */
  public function addPreviewButton($flags = self::ALIGN_LEFT) {
    $button = $this->addButton('preview', $flags, 'image', NULL);
    $button->getHTML()
      ->addClass('\Psc\button-preview');
    
    return $button;
  }
  
  /**
   * @param ButtonInterface|string $button
   */
  public function addButton($button, $flags = 0x000000, $iconLeft = NULL, $iconRight = NULL) {
    if (!$button instanceof ButtonInterface) {
      $button = new Button($button);
    }
    
    if ($iconLeft) {
      $button->setLeftIcon($iconLeft);
    }
    
    if ($iconRight) {
      $button->setRightIcon($iconRight);
    }
    
    if (($flags & self::ALIGN_RIGHT) == self::ALIGN_RIGHT) {
      $button->getHTML()
        ->setStyle('float','right')
        ->addClass('\Psc\button-right');
    }
    
    if (($flags & self::ALIGN_LEFT) == self::ALIGN_LEFT) {
      $button->getHTML()
        ->setStyle('float','left')
          ->addClass('\Psc\button-left');
    }
      
  
    if (($flags & self::CLEAR) == self::CLEAR) {
      $button->getHTML()
        ->templateAppend("\n".'<div class="clear"></div>');
    }
    
    if (($flags & self::PREPEND) == self::PREPEND) {
      array_unshift($this->buttons, $button);
    } else {
      $this->buttons[] = $button;
    }
    
    return $button;
  }
  
  public function unshiftButton($button, $flags = 0x000000, $iconLeft = NULL, $iconRight = NULL) {
    $flags |= self::PREPEND;
    $flags |= self::ALIGN_LEFT;
    return $this->addButton($button, $flags, $iconLeft, $iconRight);
  }
  
  public function getButton($index) {
    return $this->buttons[$index];
  }
}
?>