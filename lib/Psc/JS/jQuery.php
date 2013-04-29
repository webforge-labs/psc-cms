<?php

namespace Psc\JS;

use Psc\JS\Manager,
    Webforge\Common\System\Dir,
    Webforge\Common\System\File,
    Psc\PSC,
    Psc\JS\Code,
    Psc\XML\Helper as xml,
    DOMDocument,
    DOMText,
    DOMElement
;

class jQuery extends \Psc\Data\ArrayCollection {
  
  public $length = 0;
  
  /**
   * @var string
   */
  protected $selector;
  protected $literalSelector;
  
  /**
   * @var jQuery
   */
  protected $prevObject;
  
  /**
   * @var DOMDocument
   */
  protected $document;
  
  //protected $_elements; // die Elemente des Objektes
  
  /**
   * Erstellt ein neues jQuery Object
   *
   * @params $selector, $doc
   * @params $selector, $html
   * @params $selector, $htmlString
   * @params $selector, $page
   *
   * @param string $selector ein jQuery-Like-Selector (siehe \Psc\XML\Helper::query)
   * @param DOMDocument $doc
   * @param Psc\HTML\HTMLInterface $html irgendein Object des interfaces
   * @param Psc\HTML\Page $page ein komplettes Psc HTML Document
   * @param string $htmlString irgendein String mit HTML drin der geparsed wird (nicht ein komplettes Dokument!)
   *
   * Achtung: komplette Documente mit Doctype und <html> Document sollten NICHT als String übergeben werden, da sonst möglicherweise das Encoding falsch gelesen wird
   * Per Default get jQuery davon aus, dsas $htmlString ein PART eines HTML Documentes ist, nicht ein ganzes.
   * Um das zu umgehen einfach \Psc\XML\Helper::doc($html); aufrufen. Dies erzeugt ein korrekt geparstes DomDocument welches jQuery erkennt.
   */
  public function __construct() {
    $args = func_get_args();
    $num = count($args);
    
    $constructor = NULL;
    if ($num === 2) {
      list ($arg1,$arg2) = $args;
     
      /* alle mit $selector am Anfang */
      if (is_string($arg1)) {
        if (is_string($arg2)) {
          return $this->constructSelector($arg1, xml::docPart($arg2));
        } elseif ($arg2 instanceof \DOMDocument) {
          return $this->constructSelector($arg1, $arg2);
        } elseif ($arg2 instanceof \DOMElement) {
          return $this->constructSelector($arg1, xml::docPart($arg2));
        } elseif ($arg2 instanceof \Psc\HTML\Page) {
          return $this->constructSelector($arg1, xml::doc($arg2->html()));
        } elseif( $arg2 instanceof \Psc\HTML\Tag) {
          return $this->constructSelector($arg1, $arg2->getTag() === 'html'
                                                 ? xml::doc($arg2->html())
                                                 : xml::docPart($arg2->html()));
        } elseif ($arg2 instanceof \Psc\HTML\HTMLInterface) {
          return $this->constructSelector($arg1, xml::docPart($arg2->html()));
        } elseif ($arg2 instanceof \Psc\JS\jQuery) {
          return $this->constructSelector($arg1, xml::docPart($arg2->html())); // das ist eher find() und nicht clone wie nur jQuery args
        }
      } elseif ($arg1 instanceof \Psc\JS\jQuery) {
        return $this->constructjQuery($arg1, $arg2);
      }
    } elseif ($num === 1) {
      $arg1 = $args[0];
      
      if ($arg1 instanceof \DOMDocument) {
        return $this->constructDOMDocument($arg1);
      } elseif ($arg1 instanceof \DOMElement) {
        return $this->constructDOMElement($arg1);
      }
    }
    
    $signatur = array_map(array('\Psc\Code\Code','getType'), $args);
    throw new \BadMethodCallException('Es wurde kein Konstruktor für die Parameter: '.implode(', ',$signatur).' gefunden');
  }
  
  protected function constructSelector($selector, DOMDocument $doc) {
    $this->setSelector($selector);
    $this->document = $doc;
    
    $elements = $this->match($this->document, $this->selector);
    parent::__construct($elements);
    $this->length = count($this);
  }
  
  protected function constructDOMDocument(DOMDocument $doc) {
    $this->document = $doc;
    $this->setSelector("");
    
    parent::__construct(array($doc));
    $this->length = count($this);
  }
  
  protected function constructDOMElement(DOMElement $el) {
    $this->document = xml::doc($el);
    $this->setSelector("");
    parent::__construct(array($el));
    $this->length = count($this);
  }
  
  /**
   * Cloned ein jQuery Object
   */
  protected function constructjQuery(jQuery $jQuery, $selector = NULL) {
    if ($selector != NULL) {
      throw new \InvalidArgumentException('Was soll selector hier machen?');
    }
    $this->setSelector($jQuery->getSelector());
    $this->document = $jQuery->getDocument();
    
    parent::__construct($jQuery->toArray());
    $this->length = count($this);
  }
  
  /**
   * @return array
   */
  protected function match(DOMDocument $doc, $selector) {
    return xml::query($doc, $selector);
  }
  
  /**
   *
   * @return jQuery
   */
  public function find($selector) {
    if (!is_string($selector)) throw new \InvalidArgumentException('Kann bis jetzt nur string als find Parameter');
    
    if (count($this) > 1) {
      throw new \Psc\Exception('Kann bis jetzt nur find() auf jQuery-Objekten mit 1 Element. '.$this->selector.' ('.count($this).')');
    }
    
    // erstellt ein Objekt mit dem Document als unser Element
    // mit den matched Elements als das Ergebnis des Selectors in diesem Document
    $jQuery = new self($selector, $this->getElement());
    $jQuery->setPrevObject($this);
    $jQuery->setSelector($this->selector.' '.$selector);
    
    return $jQuery;
  }
  
  
  /**
   * Gibt das Attribut des (ersten) Elements zurück
   *
   * @return string|NULL
   */
  public function attr($name, $value = NULL) {
    if (($el = $this->getElement()) === NULL) return NULL;

    if ($el->hasAttribute($name)) {
      if (func_num_args() == 2) {
        $el->setAttribute($name, $value);
      }

      return $el->getAttribute($name);
    }

    return NULL;
  }
  
  /**
   * @return string
   */
  public function text() {
    if (($el = $this->getElement()) === NULL) return NULL;
    
    return $el->nodeValue;
  }
  
  /**
   * Get the HTML -->contents<-- of the first element in the set of matched elements.
   * 
   * @return string
   */
  public function html() {
    if (($el = $this->getElement()) === NULL) return NULL;
    
    return implode('',xml::export($el->childNodes)); // mit dem implode glue hier bin ich mir nicht ganz sicher
  }
  
  public function isChecked() {
    if (($el = $this->getElement(TRUE)) === NULL) return FALSE;
    
    return $el->attr('checked') === 'checked';
  }

  public function isSelected() {
    if (($el = $this->getElement(TRUE)) === NULL) return FALSE;
    
    return $el->attr('selected') === 'selected';
  }
  
  /**
   * Gibt das erste gematchte Element zurück oder NULL
   *
   * @return NULL|DOMNode|jQuery
   */
  public function getElement($jQuery = FALSE) {
    if ($this->containsKey(0)) {
      return $jQuery ? new static($this->get(0)) : $this->get(0);
    }
    
    return NULL;
  }
  
  /**
   * Reduce the set of matched elements to the one at the specified index.
   * 
   */
  public function eq($index) {
    if ($index < 0) throw new Exception('Das kann ich noch nicht (negativer index)');
    
    return new static($this->get($index));
  }
  
  public function hasClass($class) {
    // first ugly than fast
    
    $classes = $this->attr('class');
    return in_array($class, explode(' ',$classes));
  }
  
  /**
   * Gibt einen Array von Lesbaren DOMNodes zurück
   *
   * kann als debug benutzt werden
   */
  public function export() {
    return xml::export(parent::toArray());
  }
  
  
  /**
   * @return string
   */
  public function getSelector() {
    return $this->selector;
  }

  /**
   * @return string
   */
  public function getLiteralSelector() {
    return $this->literalSelector;
  }


  /**
   * @return DOMDocument
   */
  public function getDocument() {
    return $this->document;
  }
  
  public function setPrevObject(jQuery $jQuery) {
    $this->prevObject = $jQuery;
    return $this;
  }

  public function setSelector($selector) {
    // nth-of-type ist ein schöner alias für eq jedoch ist es 1-basierend (eq ist 0-basierend)
    $this->literalSelector = $selector;
    $this->selector = \Psc\Preg::replace_callback(
      $selector, 
      '/:eq\(([0-9]+)\)/', 
      function ($m) { 
        return sprintf(':nth-of-type(%d)', $m[1]+1); 
      }
    );
    return $this;
  }
  
  public function __toString() {
    return print_r($this->export(), true);
  }

  public static function factory($arg1, $arg2=NULL) {
    if ($arg2 !== NULL) {
      return new static($arg1, $arg2);
    } else {
      return new static($arg1);
    }
  }



  
  /* Statische Funktionen (Helpers) */
  public static function getIdSelector(\Psc\HTML\Tag $tag) {
    if ($tag->getAttribute('id') == NULL) $tag->generateId();
    
    return "jQuery('#".$tag->getAttribute('id')."')";
  }
  
  /**
   * @return string
   */
  public static function getClassSelector(\Psc\HTML\Tag $tag) {
    $guid = $tag->guid();
    $tag->publishGUID();
    
    if (mb_strpos($guid,'[') !== FALSE || mb_strpos($guid,']') !== FALSE) 
      return sprintf("jQuery('%s[class*=\"%s\"]')",$tag->getTag(),$guid);
    else
      return sprintf("jQuery('%s.%s')",$tag->getTag(),$guid);
  }
  
  /**
   * Fügt dem JavaScript des Tags eine weitere Funktion hinzu
   *
   * z. B. so:
   * <span id="element"></span>
   * <script type="text/javascript">$('#element').click({..})</script>
   *
   * @param Expression $code wird mit einem . an das Javascript des Selectors angehängt $code darf also am Anfang keinen . haben
   */
  public static function chain(\Psc\HTML\Tag $tag, Expression $code) {
    
    if (isset($tag->templateContent->jQueryChain)) {
      // append to previous created chain
      $tag->templateContent->jQueryChain .= sprintf("\n      .%s", $code->JS());
    
    } else {
      
      /*
       * make something like:
       *
       * require([...], function () {
       *   require([...], function (jQuery) {
       *     jQuery('selector for element')%jQueryChain%
       *   });
       * });
       */
      
      $tag->templateAppend(
        "\n".Helper::embedWithJQuery(
          new Code(
            '    '.self::getClassSelector($tag).'%jQueryChain%' // this is of course not valid js code, but it will be replaced to one
          )
        )
      );

      $tag->templateContent->jQueryChain = sprintf("\n      .%s", $code->JS());
    }
    
    return $tag;
  }
  
  public static function onWindowLoad($jsCode) {
    if ($jsCode instanceof \Psc\JS\Lambda) {
      $c = $jsCode->JS();
    } elseif ($jsCode instanceof \Psc\JS\Expression) {
      $c = 'function() { '.$jsCode->JS().' }';
    } else {
      $c = 'function() { '.(string) $jsCode.' }';
    }
      
    return Helper::embed('jQuery(window).load('.$c.')');
  }
}