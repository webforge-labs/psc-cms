<?php

namespace Psc\PHPWord;

use Webforge\Common\System\File;
use Psc\PSC;
use PHPWord;
use PHPWord_Section;
use PHPWord_IOFactory;
use Psc\Doctrine\Helper as DoctrineHelper;
use Psc\TPL\TPL;

/**
 * Base Class für alle WordTemplates
 */
class BaseTemplate extends \Psc\System\LoggerObject {
  
  /**
   * @var PHPWord
   */
  protected $word;
  
  /**
   * @var PHPWord_Section
   */
  protected $section;
  
  /**
   * @var PHPWord_Section_TextRun
   */
  protected $run;
  
  /**
   * Die Einrückung des Textes
   *
   * ein Level von 1 bedeutet 2 Leerzeichen eingerückt
   * @var int Level 0 - Level X
   */
  protected $indent;
  
  /**
   * Das Template von dem wir Word + Section bekommen
   *
   * es kann sein, dass wir dieses Objekt selbst sind
   */
  protected $parentTemplate;
  
  public function __construct(BaseTemplate $parentTemplate) {
    $this->parentTemplate = $parentTemplate;
    
    $this->word = $this->parentTemplate->getWord();
    $this->section = $this->parentTemplate->getSection();
  }
  
  /**
   *
   * wird aufgerufen, wenn das Template erstellt werden soll oder mit get() zurückgegeben werden soll
   * dabei sollte der binder gesetzt sein und alles knorke sein
   */
  public function create() {
    
  }
  
  /**
   * Der Text aller Paragraphen innerhalb von allen Sections, die als Style '([a-zA-Z0-9]*).template' haben werden mit diesen Variablen ersetzt
   *
   * letz get dirty:
   * für diesen Hack muss in PHPWord_Section_Text setText() definiert sein
   */
  public function miniTemplateReplace(Array $vars) {
    
    foreach ($this->word->getSections() as $section) {
      foreach ($section->getElements() as $element) {
        
        if ($element instanceof \PHPWord_Section_Text) {
          $style = $element->getParagraphStyle();
          
          if (is_string($style) && mb_strpos($style,'.template') !== FALSE && ($text = $element->getText()) && mb_strpos($text,'%') !== FALSE) {
            $element->setText(TPL::miniTemplate($text,$vars));
          }
        }
      }
    }
  }
  
  /**
   * Fügt Text mit Multi-Lines hinzu
   *
   * es wird der Style des aktuellen Runs in $this->run genommen. Ist kein aktueller Run vorhanden wir ein run mit dem Style "standard" erstellt
   * @return TextRun
   */
  public function addMarkupText($text, $useRun = NULL, $fontStyle = NULL, $pStyle = 'standard') {
    if (!isset($useRun)) 
      $this->newRun();
    else
      $this->run = $useRun;
    
    $regex = '/(' . implode(')|(', array('\[b\]','\[\/b\]','\[i\]','\[\/i\]',"\n")) . ')/';

    $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE; //| PREG_SPLIT_OFFSET_CAPTURE;
    $tokens = preg_split($regex, $text, -1, $flags);
    
    $boldStyle = array('bold'=>true);
    $italicStyle = array('italic'=>true);
    if (!($this->run instanceof \PHPWord_Section_Table_Cell)) {
      $pStyle = $this->run->getParagraphStyle(); // speichern wir, damits immer mit demselben style weitergeht
      $root = $this->section;
    } else {
      $root = $this->run;
    }
    
    foreach ($tokens as $token) {
      
      if ($token == "\n") {
        $this->run = $root->createTextRun($pStyle);
      } elseif ($token == '[b]') {
        $fontStyle = $boldStyle;
      } elseif ($token == '[/b]') {
        $fontStyle = NULL;
      } elseif ($token == '[i]') {
        $fontStyle = $italicStyle;
      } elseif ($token == '[/i]') {
        $fontStyle = NULL;
      } else {
        $this->addRunText(str_repeat('  ',$this->indent).$token,
                            $fontStyle,
                            $pStyle);
      }
    }
    return $this->run;
  }
  
  public function newRun($pStyle = NULL) {
    if ($pStyle == NULL)
      $pStyle = isset($this->run) ? $this->run->getParagraphStyle() : 'standard';
      
    $this->run = $this->section->createTextRun($pStyle);
    return $this->run;
  }
  
  public function addIndent($num) {
    $this->indent += $num;
  }
  
  public function removeIndent($num) {
    $this->indent = max(0,$this->indent-$num);
  }

  public function br() {
    return $this->addText(null,null,'standard');
  }

  /**
   * Fügt Text hinzu und macht dahinter einen Umbruch
   *
   */
  public function addText($text, $style = NULL, $pStyle = NULL) {
    return $this->section->addText($text, $style, $pStyle);
  }
  
  /**
   * Fügt dem Aktuellen Run Text hinzu
   *
   * der run muss zuerst mit newRun erstellt werden
   * Nach dem Text wird nicht umgebrochen (es sei denn ein neuer Run wird begonnen)
   */
  public function addRunText($text, $style = NULL, $pStyle = NULL) {
    return $this->run->addText($text, $style, $pStyle);
  }

  /**
   * @return PHPWord
   */
  public function getWord() {
    return $this->word;
  }
  
  /**
   * @param PHPWord_Section $section
   * @chainable
   */
  public function setSection(PHPWord_Section $section) {
    $this->section = $section;
    return $this;
  }

  /**
   * @return PHPWord_Section
   */
  public function getSection() {
    return $this->section;
  }

  public function isRoot() {
    return FALSE;
  }
}
?>