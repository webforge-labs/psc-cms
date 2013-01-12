<?php

namespace Psc\Code\Generate;

use Psc\Code\Annotation;
use Psc\Preg;
use Psc\String as S;

/**
 *
 *
 * implementation: vor jedem neuen "Part" des Docblocks muss \n (am Ende weglassen)
 */
class DocBlock {
  
  protected $body;
  
  protected $summary;
  
  protected $annotations = array();
  
  public function __construct($body = NULL) {
    $this->summary = NULL;
    $this->setBody($body);
  }

  /**
   * Wie man hier sehen kann, ist des natürlich Käse
   * aber ich hab keine Lust auf den ganzen Annotation-Rumble
   * (siehe Annotation.php)
   * @TODO ding dong (Annotation Writer schreiben)
   */
  public function addSimpleAnnotation($name, Array $values = array()) {
    if (isset($this->body)) $this->body .= "\n";
    $this->body .= S::expand((string) $name, '@', S::START);
    
    if (count($values) > 0) {
      $this->body .= '(';
      foreach ($values as $key => $value) {
        if (is_string($value)) {
          $this->body .= sprintf('%s="%s", ',$key,str_replace('"','""',$value));
        } elseif (is_integer($value)) {
          $this->body .= sprintf('%s=%d, ',$key,$value);
        } elseif (is_array($value)) {
          throw new Exception('Ich kann keine verschachtelten Arrays');
        } else {
          throw new Exception('Ich kann noch nichts anderes');
        }
      }
      $this->body = mb_substr($this->body,0,-2);
      $this->body .= ')';
    }
    
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasSimpleAnnotation($name) {
    return mb_strpos($this->body, '@'.ltrim($name,'@')) !== FALSE;
  }
  
  
  /**
   * @return string the word after the annotation (stopped being parsed bei whitespace or end of line)
   */
  public function parseSimpleAnnotation($name) {
    $m = array();
    if (Preg::match($this->body, '/@'.$name.'\s+([^\s\n]+)/i', $m)) {
      return $m[1];
    }
    
    return NULL;
  }
  
  /**
   * Fügt dem Docblock Text hinzu
   *
   * dies geschieht hinter den Annotations (wenns welche gibt)
   * es wird automatisch zum letzten String ein Umbruch eingefügt
   */
  public function append($string) {
    if (isset($this->body)) $this->body .= "\n";
    $this->body .= $string;
    
    return $this;
  }
  
  /**
   * @param string $summary
   * @chainable
   */
  public function setSummary($summary) {
    $this->summary = $summary;
    return $this;
  }

  /**
   * @return string
   */
  public function getSummary() {
    return $this->summary;
  }
  
  public function getDescription() {
    return $this->body;
  }
  
  public function parseSummary() {
    if (!isset($this->summary) && mb_strlen($this->body) > 0) {
      $m = array();
      if (Preg::match($this->body, '/^\s*(.+?)[\n]{2}(.+)$/is', $m)) {
        $this->summary = $m[1];
        $this->body = $m[2];
      }
    }
    
    return $this;
  }

  public function addAnnotation(Annotation $annotation) {
    $this->annotations[] = $annotation;
    return $this;
  }
  
  /**
   *
   * Achtung: Das ist hier mit Vorsicht zu genießen, da DocBlocks die geschrieben und Elevated wurden, noch nicht von einem Reader gelesen werden
   * Das ist auch hochgradig-nicht-trivial da hier den AnnotationReader instanziiert werden muss, usw.
   * Die Aussage dieser Funktion ist also sehr wackelig!
   * 
   * @return bool
   */
  public function hasAnnotation($FQN) {
    foreach ($this->annotations as $annotation) {
      if ($annotation->getAnnotationName() === $FQN)
        return TRUE;
    }
    
    return FALSE;
  }
  
  /**
   *
   * Achtung: Das ist hier mit Vorsicht zu genießen, da DocBlocks die geschrieben und Elevated wurden, noch nicht von einem Reader gelesen werden
   * Das ist auch hochgradig-nicht-trivial da hier den AnnotationReader instanziiert werden muss, usw.
   * Die Aussage dieser Funktion ist also sehr wackelig!
   * 
   * gibt es mehrere unter demselben FQN wird die Erste zurückgegeben
   */
  public function getAnnotation($FQN) {
    foreach ($this->annotations as $annotation) {
      if ($annotation->getAnnotationName() === $FQN)
        return $annotation;
    }
    
    return NULL;
  }
  
  public function removeAnnotation($FQN) {
    foreach ($this->annotations as $key=>$annotation) {
      if ($annotation->getAnnotationName() === $FQN)
        unset($this->annotations[$key]);
    }
    
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasAnnotations() {
    return count($this->annotations) > 0;
  }
  
  /**
   * Gibt Annotations des DocBlocks als Array zurück
   * 
   * wird ein FQN übergeben werden alle Annotations dieser Klasse zurückgegeben
   * @return array
   */
  public function getAnnotations($filterFQN = NULL) {
    if (isset($filterFQN)) {
      return array_filter($this->annotations, function ($annotation) use ($filterFQN) {
        return $annotation->getAnnotationName() === $filterFQN;
      });
    }
    
    return $this->annotations;
  }
  
  /**
   * @chainable
   */
  public function setBody($body = NULL) {
    if ($body != NULL) {
      $this->body = $this->parseBody($body);
    } else {
      $this->body = NULL;
    }
    return $this;
  }
  
  protected function parseBody($body) {
    /* wir schauen nach ob der Body z.B. noch kommentare beeinhaltet */
    if (Preg::qmatch($body, '|^/\*+|',0) !== NULL) {
      $body = $this->stripCommentAsteriks($body);
    }
    
    return $body;
  }
  
  public function stripCommentAsteriks($body) {
    
    /* okay, also das Prinzip ist eigentlich: lösche alle Tokens am Anfang der Zeile (deshalb /m)
       bzw * / am Ende der Zeile 
       
       da aber bei leeren Zeilen im Docblock bzw. Zeilen ohne * das multi-line-pattern kaputt geht
       überprüfen wir im callback ob die zeile "leer" ist und fügen dann einen Umbruch ein.
       Wir behalten quasi die Umbrüche zwischendrin
    */
    //$body = preg_replace_callback('/(^\s*$|^\/\*\*($|\s*)|^\s*\*\/|^\s*\*($|\s*)|\*\/$)/mu', function ($m) {
    //  return (mb_strpos($m[0], '*') !== FALSE) ? NULL : "\n";
    //}, $body);
    $lines = array();
    
    $debug =  "\n";
    foreach (explode("\n", ltrim($body)) as $line) {
      $tline = trim($line);
      $debug .=  "'$tline'";
      
      $debug .=  "\n  ";
      if ($tline === '*') {
        // nur ein sternchen bedeutet eine gewollte Leerzeile dazwischen
        $lines[] = NULL; // means zeilenumbruch
        $debug .=  "newline";
      } elseif ($tline === '/**' || $tline === '/*' || $tline === '*/') { 
        // top und bottom wollen wir nicht in unserem docBlock haben
        $debug .=  "discard";
      } elseif (($content = Preg::qmatch($line, '/^\s*\/?\*+\s?(.*?)\s*$/')) !== NULL) {
        // eine Zeile mit * am Anfang => wir nehmen den $content (Einrückungssafe)
        // eine Zeile mit /*+ am Anfang => wir nehmen den content (Einrückungssafe)
        $content = rtrim($content,' /*');  // read carefully
        $lines[] = $content;
        $debug .=  "content: '".$content."'";
      } else {
        // dies kann jetzt nur noch eine Zeile ohne * sein (also ein kaputter Docblock). Den machen wir heile
        $lines[] = $line; // preservewhitespace davor?
        $debug .=  "fix: '$line'";
      }
      
      $debug .=  "\n";
    }
    $debug .=  "ergebnis:\n";
    //$debug .= print_r($lines,true);
    $debug .= "\n\n";
    //print $debug;

    // mit rtrim die leerzeilen nach dem letzten Text entfernen
    return rtrim(implode("\n",$lines)); // while array_peek(lines) === NULL : array_pop($lines)
  }
  
  /**
   * @return string
   */
  protected function mergeBody() {
    $body = NULL;
    if (isset($this->summary)) {
      $body .= $this->summary."\n"; // das macht die leerzeile nach der summary
      if (isset($this->body)) $body .= "\n";
    }
    $body .= $this->body;
    
    
    if ($this->hasAnnotations()) {
      $annotations = array();
      foreach ($this->annotations as $annotation) {
        $annotations[] = $annotation->toString();
      }
      
      if (mb_strlen($body) > 0) { // für den ersten abstand nach oben, sonst nicht (annotation ist dann die erste zeile im db)
        $body .= "\n";
      }
      $body .= implode("\n", $annotations);
    }
    
    return $body;
  }

  /**
   * @return string
   */
  public function toString() {
    $body = $this->mergeBody();
    
    $br = "\n";
    $s  = '/**'.$br;
    $s .= ' * '.str_replace($br, $br.' * ',rtrim($body)).$br;
    $s .= ' */'.$br;
    
    return $s;
  }
  
  public function getBody() {
    return $this->mergeBody();
  }
  
  public function __toString() {
    return $this->toString();
  }
}
?>