<?php


class HTMLTag extends Object {

  protected static $glues = array('class'=>' ',
                                  'style'=>'; ',
                                  );
  protected static $selfClosingTags = array('input', 'br', 'img');
  
  protected $attributes = array();
  
  protected $tag;
  
  /**
   * @var mixed kann ein Array sein (dann muss glueContent gesetzt sein) oder ein String
   */
  public $content;
  
  /**
   * @var string kann ein sprintf-string sein. Erster Parameter je ein Wert aus $this->content, zweiter Parameter ein Schlüssel aus $this->content
   */
  public $glueContent = NULL;
  
  /**
   *
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
  public $contentTemplate = NULL;
  
  public $selfClosing = FALSE;
  
  public function __construct($tag, $content = NULL, Array $attributes = NULL) {
    $this->setTag($tag);
    $this->content = $content;
    
    if (isset($attributes))
      $this->setAttributes($attributes);
  }
  
  public function html() {
    $html = '<'.HTML::esc($this->tag).$this->htmlAttributes();

    if ($this->selfClosing) {
      $html .= ' />';
    } else {
      $html .= '>'.$this->htmlContent().'</'.HTML::esc($this->tag).'>';
    }
    
    return $html;
  }
  
  public function htmlContent() {
    if (isset($this->glueContent) && (is_array($this->content) || (is_object($this->content) && $this->content instanceof stdClass))) {
      $content = NULL;
      foreach ($this->content as $key=>$value) {
        $content .= sprintf($this->glueContent,(string) $value,$key);
      }
      return $content;
    }

    if (isset($this->contentTemplate)) {
      $contentsArray = (array) $this->content; // convert obj of stdClass or else to array
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
    
    return (string) $this->content;
  }
  
  public function htmlAttributes() {
    $html = NULL;
    foreach ($this->attributes as $name => $value) {
      $html .= ' '.$name.'="';
      if (array_key_exists($name,self::$glues)) {
        $html .= HTML::escAttr(implode(self::$glues[$name], $value));
      } else {
        $html .= HTML::escAttr((string) $value);
      }
      $html .= '"';
    }
    return $html;
  }

  /* Attributes */  
  public function setAttribute($name, $value = NULL) {
    $name = strtolower($name);
    
    if (array_key_exists($name,self::$glues)) {
      $value = (array) $value;
      if (is_array($this->attributes[$name])) {
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
    if ($attributes == NULL) {
      $this->attributes = array();
      return $this;
    }
    
    foreach ($attributes as $key =>$value) {
      $this->setAttribute($key,$value);
    }
    return $this;
  }
  public function getAttribute($name) {
    if (isset($this->attributes[$name]))
      return $this->attributes[$name];
  }
  
  /* Class */
  public function setClass($class) {
    $this->attributes['class'][strtolower($class)] = $class;
    return $this;
  }
  public function addClass($class) {
    return $this->setClass($class);
  }
  public function removeClass($class) {
    unset($this->attributes['class'][strtolower($class)]);
    return $this;
  }
  public function hasClass($class) {
    return isset($this->attributes['class']) && is_array($this->attributes['class']) && in_array(strtolower($class),$this->attributes['class']);
  }
  
  /* Style */
  public function setStyle($attribute, $value) {
    $this->attributes['style'][strtolower($attribute)] = $attribute.': '.$value;
    return $this;
  }
  public function removeStyle($attribute) {
    unset($this->attributes['style'][strtolower($attribute)]);
    return $this;
  }
  
  public function getTag() {
    return $this->tag;
  }
  
  public function setTag($tag) {
    if ($tag == '') throw new InvalidArgumentException('Tag muss ein String sein');
    $this->selfClosing = in_array($tag,self::$selfClosingTags);
    $this->tag = $tag;
    
    return $this;
  }

  public function setGlueContent($glue) {
    $this->glueContent = $glue;
    
    return $this;
  }
  
  public function __toString() {
    return $this->html();
  }

}