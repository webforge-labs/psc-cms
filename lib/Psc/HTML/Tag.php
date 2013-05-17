<?php

namespace Psc\HTML;

use stdClass;
use InvalidArgumentException;
use Webforge\Common\String AS S;
use Psc\SimpleObject;
use Psc\JS\jQuery;
use Psc\JS\Code AS JSCode;
use Psc\JS\Expression;

/**
 *
 */
class Tag extends \Psc\OptionsObject implements HTMLInterface {
  
  const NO_LOWER_ATTRIBUTES = 0x000001;
  
  protected static $glues = array('class'=>' ','style'=>'; ');
  
  protected static $selfClosingTags = array('input','br','img','param');
  
  protected $attributes = array();
  
  protected $tag;
  
  public $debug = false;
  
  /**
   * Eine kleine Extension um das Chainen von Javascript-Script-Tags einfacher zu machen
   */
  protected $guid;
  
  /**
   * Der Container für den Inner-Content
   * 
   * @var mixed kann ein Array sein (dann muss glueContent gesetzt sein) oder ein String
   */
  public $content;
  
  /**
   * Der Container für den Content für das Outer-Template
   * 
   * @var mixed eine Instance von stdClass
   */
  public $templateContent;
  
  /**
   * @var string kann ein sprintf-string sein. Erster Parameter je ein Wert aus $this->content, zweiter Parameter ein Schlüssel aus $this->content
   */
  public $glueContent = '%s
';
  
  /**
   * Das Inner-Template (für den Content)
   * <example>
   * $tag->contentTemplate = '<p>
   *                              %headline%
   *                              %input%
   *                          </p>';
   * $tag->content['headline'] = fHTML::tag('h1',HTML::esc('Banana Joana'));
   * $tag->content['input'] = fHTML::tag('input',NULL,array('type'=>'text','name'=>'banana_joana','value'=>'Got Banana?'));
   * </example>
   * 
   * @var string kann ein Template mit den Schüsseln aus $this->content als %schüssel% markiert sein
   */
  public $contentTemplate;
  
  /**
   * Das Outer-Template
   * 
   * folgt denselben regeln wie contentTemplate nur, dass dieses "außenrum" ist.
   * die Inhalte können sich jetzt auch in $templateContent befinden
   * 
   * to get the idea:
   * $html = HTML::tag('html');
   * $html->template = %doctype%\n%self%;
   * $html->contentTemplate = "%head% \n %body%";
   * $html->templateContent->doctype = '<!DOCTYPE html>';
   * %self% ist dann das eigentlich html tag
   */
  public $template;
  
  /**
   * ein Array von Javascript Code Schnipseln die mit . an das Script-Tag des HTML Elements angehängt werden
   * 
   * siehe jQuery::chain()
   */
  protected $jsChain = array();
  
  /**
   * @var bool
   */
  protected $lowerAttributes = TRUE;
  
  public function __construct($tag, $content = NULL, Array $attributes = NULL, $flags = 0x000000) {
    $this->lowerAttributes = (bool) ($flags ^ self::NO_LOWER_ATTRIBUTES);
    $this->setTag($tag);
    $this->content = $content;
    
    $this->templateContent = new stdClass();
    
    if (isset($attributes))
      $this->setAttributes($attributes);
      
    $this->setUp(); // für ableitende klassen
  }
  
  protected function setUp() {
  }
  
  public function html() {
    /* js chains */
    if ($this->getOption('closeTag',TRUE) == TRUE) {
      foreach ($this->jsChain as $code) {
        jQuery::chain($this, $code);
      }
      $this->jsChain = array();
    }
    
    $tagIndent = (int) $this->getOption('tag.indent',0);
    $indent = max((int) $this->getOption('indent',0),(int) $this->getOption('content.indent',0));
    
    if ($this->tag == 'textarea' || $this->tag == 'pre') {
      $indent = $tagIndent = 0;
    }
    
    $html = '<'.HTML::esc($this->tag).$this->htmlAttributes();
    
    if ($this->getOption('selfClosing',FALSE)) {
      /* self closing element ohne inhalt */
      $html .= ' />'.($this->getOption('br.selfClosing',FALSE) ? "\n" : NULL);
    } elseif ($this->getOption('onlyOpen',FALSE)) {
      /* nur open-tag schließen, kein content, kein schließendes tag */
      $html .= '>'.($this->getOption('br.onlyOpen',FALSE) ? "\n" : NULL);
    } else  {
      /* open-tag schließen, content, schließendes tag */
      $html .= '>'.($this->getOption('br.openTag',FALSE) ? "\n" : NULL).
      ($this->getOption('br.beforeContent',FALSE) ? "\n" : NULL).
      
      /* Content */
      ($indent > 0 ? S::indent($this->htmlContent(),$indent) : $this->htmlContent()).
      
      ($this->getOption('br.afterContent',FALSE) ? "\n" : NULL);
      
      if ($this->getOption('closeTag',TRUE)) {
       $html .= '</'.HTML::esc($this->tag).'>'.($this->getOption('br.closeTag',FALSE) ? "\n" : NULL);
      }
    }
    
    if ($tagIndent > 0) 
      $html = S::indent($html,$tagIndent,"\n");
    else
      $html;
    
    if (isset($this->template)) {
      $contentsArray = array_merge($this->convertContent(), $this->convertTemplateContent());
      $contentsArray['self'] = $html;
      $html = str_replace(
                          // ersetze %key%
                          array_map(create_function('$a','return "%".$a."%"; '),array_keys($contentsArray)),
                          // mit dem wert
                          array_values($contentsArray),
                           // in
                           $this->template
                         );
    }
    
    return $html;
  }
  
  public function htmlContent() {
    if (isset($this->glueContent) && !isset($this->contentTemplate) && (is_array($this->content) || (is_object($this->content) && $this->content instanceof stdClass))) {
      $content = NULL;
      foreach ($this->content as $key=>$value) {
        $content .= sprintf($this->glueContent,(string) $value,$key);
      }
      return $content;
    }
    
    if (isset($this->contentTemplate)) {
      $contentsArray = $this->convertContent(); // convert obj of stdClass or else to array
      $content = $this->contentTemplate;
      $content = str_replace(
                             // ersetze %key%
                             array_map(create_function('$a','return "%".$a."%"; '),array_keys($contentsArray)),
                             // mit dem wert
                             array_values($contentsArray),
                             // in
                             $content
                             );
      return $content;
    }
    
    if ($this->content instanceof stdClass && !isset($this->contentTemplate)){
      throw new Exception('Content ist ein Objekt(stdClass) aber contentTemplate ist nicht gesetzt. Vergessen?');
    }
    
    return (string) $this->content;
  }
  
  protected function convertContent() {
    if (is_array($this->content)) {
      return $this->content;
    } elseif ($this->content instanceof Tag) {
      return array($this->content->html());
    } else {
      return (array) $this->content;
    }
  }
  
  protected function convertTemplateContent() {
    if (is_array($this->templateContent)) {
      return $this->templateContent;
    } elseif ($this->templateContent instanceof Tag) {
      return array($this->templateContent->html());
    } else {
      return (array) $this->templateContent;
    }
  }
  
  public function htmlAttributes() {
    $html = NULL;
    foreach ($this->getAttributes() as $name => $value) {
      $html .= ' '.$name.'="';
      if (array_key_exists($name, self::$glues)) {
        $value = implode(self::$glues[$name], $value);
      } elseif ($name === 'id') {
        $value = HTML::string2id($value);
      } elseif(is_array($value)) {
        throw new \Psc\Exception('Cannot connvert value (as array) for attribute: '.$name);
      }
      $html .= HTML::escAttr((string) $value);
      $html .= '"';
    }
    return $html;
  }
  
  public function setAttribute($name, $value = NULL) {
    if ($this->lowerAttributes) $name = strtolower($name);
    
    if (array_key_exists($name,self::$glues)) {
      $value = (array) $value;
      if (isset($this->attributes[$name]) && is_array($this->attributes[$name])) {
        $this->attributes[$name] = array_merge($this->attributes[$name], $value);
      } else {
        $this->attributes[$name] = $value;
      }
    }
    
    $this->attributes[$name] = $value;
    
    return $this;
  }
  
  public function removeAttribute($name) {
    unset($this->attributes[$name]);
    return $this;
  }
  
  public function setAttributes(Array $attributes = NULL) {
    if ($attributes === NULL) {
      $this->attributes = array();
      return $this;
    }
    
    foreach ($attributes as $key =>$value) {
      $this->setAttribute($key,$value);
    }
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasAttribute($name) {
    return isset($this->attributes[$name]);
  }
  
  public function getAttribute($name) {
    if (isset($this->attributes[$name]))
      return $this->attributes[$name];
  }
  
  public function setClass($class) {
    $this->attributes['class'][strtolower($class)] = $class;
    return $this;
  }
  
  /**
   * @param string|array
   */
  public function addClass($class) {
    if (is_array($class)) {
      foreach ($class as $c) {
        $this->setClass($c);
      }
      return $this;
      
    } else {
      return $this->setClass($class);
    }
  }
  
  public function removeClass($class) {
    unset($this->attributes['class'][strtolower($class)]);
    return $this;
  }
  
  public function hasClass($class) {
    return isset($this->attributes['class']) && is_array($this->attributes['class']) && in_array(strtolower($class),$this->attributes['class']);
  }
  
  public function setStyle($attribute, $value) {
    $this->attributes['style'][strtolower($attribute)] = $attribute.': '.$value;
    return $this;
  }
  
  public function setStyles(Array $styles) {
    foreach ($styles as $attribute => $value) {
      $this->setStyle($attribute, $value);
    }
    return $this;
  }
  
  public function removeStyle($attribute) {
    unset($this->attributes['style'][strtolower($attribute)]);
    return $this;
  }
  
  public function hasStyle($attribute) {
    return isset($this->attributes['style']) && array_key_exists(mb_strtolower($attribute),$this->attributes['style']);
  }
  
  public function getTag() {
    return $this->tag;
  }
  
  public function setTag($tag) {
    if ($tag == '') throw new InvalidArgumentException('Tag muss ein String sein');
    $this->setOption('selfClosing',in_array($tag,self::$selfClosingTags));
    $this->tag = $tag;
    
    return $this;
  }
  
  public function setGlueContent($glue) {
    $this->glueContent = $glue;
    
    return $this;
  }
  
  public function indent($num) {
    $this->setOption('tag.indent',$num);
    $this->setOption('content.indent',2);
    return $this;
  }
  
  public function generateId() {
    // getObjectId ist auch nicht schön eindeutig, weil pro request bei einem ajaxrequest das object genau dieselbe id bekommen kann
    // deshalb besser uniquid (wobei mir völlig unklar ist, wie die das macht)
    $this->setAttribute('id','psc-cms-'.str_replace('.', '_', uniqid('html-tag',true)));
    return $this;
  }
  
  public function generateGUID() {
    if (($id = $this->getAttribute('id')) != NULL) {
      $set = 'psc-guid-'.$id;
    } else {
      $set = uniqid('psc-guid-');
    }
    return $this->guid($set);
  }
  
  public function publishGUID() {
    $this->addClass($this->guid());
    return $this;
  }
  
  public function guid($set = NULL) {
    if (func_num_args() == 1) {
      $guid = $this->guid;
      
      if ($set != NULL) {
        if (!S::startsWith($set,'psc-guid-'))
          $set = 'psc-guid-'.$set;
        
        $set = HTML::string2id($set);
      }
      
      $this->guid = $set;
      if ($this->guid != $guid && $this->hasClass($guid)) {
        $this->removeClass($guid); //unpublish
        $this->publishGUID();
      }
        
      return $this;
    }
    
    if (!isset($this->guid)) {
      $this->generateGUID();
    }
    
    return $this->guid;
  }
  
  /**
   * Fügt einen Prefix vor die ID ein
   * 
   * wenn die ID leer ist, steht nur der prefix in der id
   * @param string $prefix zu diesem werden keine zusätzlichen Zeichen hinzugefügt
   */
  public function prefixId($prefix) {
    $this->setAttribute('id',$prefix.$this->getAttribute('id'));
    return $this;
  }
  
  public function suffixId($suff) {
    $this->setAttribute('id',$this->getAttribute('id').$suff);
    return $this;
  }
  
  public function templateAppend($templatePart) {
    if (!isset($this->template)) $this->template = '%self%';
    
    $this->template .= $templatePart;
    return $this;
  }
  
  public function templatePrepend($templatePart) {
    if (!isset($this->template)) $this->template = '%self%';
    
    $this->template = $templatePart.$this->template;
    return $this;
  }
  
  /**
   * 
   * @param HTMLTag $element An HTML snippet, selector expression, jQuery object, or DOM element specifying the structure to wrap around the matched elements.
   * @return HTMLTag
   */
  public function wrap(Tag $element) {
    $inner = clone $this;
    return $element->setContent($inner);    
  }
  
  /**
   * 
   * An HTML snippet, selector expression, jQuery object, or DOM element specifying the structure to wrap around the content of the matched elements
   * @return HTMLTag(this)
   */
  public function wrapInner(Tag $container) {
    $container->contentTemplate = $this->contentTemplate;
    $container->content = $this->content;
    
    $this->contentTemplate = '%wrap%';
    $this->content = new stdClass;
    $this->content->wrap = $container;
    
    return $container;
  }
  
  public function append(Tag $tag) {
    if (is_array($this->content))
      $this->content[] = $tag;
    else
      throw new \RuntimeException('Ich kann nur an Contents appenden die arrays sind');
    
    return $this;
  }
  
  /**
   * Fügt hinter diesem Element ein anderes Element an
   */
  public function after(Tag $after) {
    $id = $after->guid();
    $this->templateAppend('%'.$id.'%');
    $this->templateContent->$id =& $after;
  }
  
  public function chain(Expression $js) {
    $this->jsChain[] = $js;
    return $this;
  }
  
  public function widget($widgetName, Array $options = array()) {
    \Psc\UI\jQuery::widget($this, $widgetName, $options);
    return $this;
  }
  
  public function __toString() {
    try {
      return $this->html();
    } catch (\Exception $e) {
      print nl2br($e);
      throw $e;
    }
  }
  
  /**
   * @param bool $lowerAttributes
   */
  public function setLowerAttributes($lowerAttributes) {
    $this->lowerAttributes = $lowerAttributes;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getLowerAttributes() {
    return $this->lowerAttributes;
  }
}
?>