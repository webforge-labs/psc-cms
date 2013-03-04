<?php

namespace Psc\PHPWord;

use PHPWord;
use PHPWord_IOFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Webforge\Common\System\File;

/**
 * Das Maintemplate für eine Word-Template Struktur
 *
 * ein Word-MainTemplate kann mehrere Unter-Templates haben
 * die Unter-Templates sind vom Typ: BaseTemplate
 *
 * ein MainTemplate ist auch selbst ein BaseTemplate, allerdings hat dies root = TRUE
 */
class MainTemplate extends BaseTemplate {
  
  public function __construct(PHPWord $word = NULL) {
    $this->setWord($word);
    $this->section = $this->word->createSection();
    
    parent::__construct($this);
  }
  
  /**
   * 
   *
   * das MainTemplate ist das einzige Template, was das darf
   */
  public function setWord(PHPWord $word = NULL) {
    if (!isset($word)) {
      $this->word = new PHPWord();

      $this->word->setDefaultFontName('Arial');
      $this->word->setDefaultFontSize(10);
    } else {
      $this->word = $word;
    }
    
    $this->word->addParagraphStyle('standard',array(
      'spacing'=>0,
      'spaceAfter'=>0
    ));
    
    $this->word->addParagraphStyle('standard.right',array(
      'spacing'=>0,
      'spaceAfter'=>0,
      'align'=>'right'
    ));

    return $this;
  }
  
  /**
   * Schreibt das Objekt als eine Word2007 Datei
   *
   * setzt die Extension für $file korrekt
   */
  public function write(File $file) {
    if (!$this->isRoot()) {
      throw new \Psc\Exception('Kein write() für nicht-Root-Elemente!');
    }

    try {    
      $objWriter = PHPWord_IOFactory::createWriter($this->word, 'Word2007');
      $file->setExtension('docx');
      $objWriter->save((string) $file);
    } catch (\Exception $e) {
      throw $e;
      //throw new \Psc\Exception(sprintf("Fehler '%s' beim Schreiben der Datei '%s'.", $e->getMessage(), $file), 0, $e);
    }
    return $file;
  }
  
  public function output() {
    if (!$this->isRoot()) {
      throw new \Psc\Exception('Kein write() für nicht-Root-Elemente!');
    }

    try {    
      $objWriter = PHPWord_IOFactory::createWriter($this->word, 'Word2007');
      
      return $objWriter->save('php://output');
    } catch (\Exception $e) {
      throw $e;
    }
  }
  
  public function isRoot() {
    return TRUE;
  }
}
?>