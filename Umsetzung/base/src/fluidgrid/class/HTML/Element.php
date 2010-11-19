<?php

class FluidGrid_HTMLElement extends FluidGrid_Model {

  protected static $inlineElements = array('h1','h2','a','td','th');
  protected static $specialAttributes = array('id'); //attribute die einen eigenen setter haben
  protected static $glues = array('class'=>' ',
                                  'style'=>'; ');

  /**
   * 
   * Schlüssel ist der Name das Attributes. Wert kann ein string sein oder ein array
   * Ist der Wert ein Array so wird er mit in self::$glues angegeben Trennern mit implode als String zusammengeführt
   * @var array 
   */
  protected $attributes = array();

  /**
   * 
   * @var string
   */
  protected $tag;

  
  public function __construct($tag, FluidGrid_Model $innerHTML = NULL, Array $attributes = NULL) {
    $this->tag = $tag;
    $this->content = $innerHTML;
    
    if (isset($attributes)) {
      $this->setAttributes($attributes);
    }
  }

  public static function factory($tag, $innerHTML = NULL, $attributes = NULL) {
    if ($innerHTML != NULL && is_string($innerHTML))
      $innerHTML = new FluidGrid_String($innerHTML);

    $html = new FluidGrid_HTMLElement($tag, $innerHTML, $attributes);
    return $html;
  }

  public function getContent() {
    $attributes = NULL;

    $it = new ArrayIterator($this->getAttributes());
    while($it->valid()) {
      
      $value = $it->current();
      if (is_string($value))
        $value = html::specialchars($value,TRUE);
      
      if (is_array($value)) {
        $value = implode(self::$glues[$it->key()],$value);
      }
        
      $attributes .= $it->key().'="'.$value.'"';
      
      $it->next();
      
      if ($it->valid())
        $attributes .= ' ';
    }

    /* tag auf */
    $html = $this->indent().'<'.$this->tag;

    /* attribute */
    if(isset($attributes)) 
      $html .= ' '.$attributes;
    $html .= '>';

    if (!$this->isInline())
      $html .= $this->lb();
    
    /* innerHTML */
    $html .= $this->getInnerHTML();

    /* tag zu */
    if (!$this->isInline())
      $html .= $this->indent();
    $html .= '</'.$this->tag.'>'.$this->lb();

    return $html;
  }

  public function getInnerHTML() {
    if (parent::getContent() != NULL) {
      
      if (!$this->isInline()) 
        parent::getContent()->setLevel($this->level + 1);
      
      return (string) parent::getContent();
    }
  }

  /* Atributes */
  public function setAttribute($name, $value) {
    if (array_key_exists($name,self::$glues))
      $value = (array) $value;

    if (in_array(strtolower($name),self::$specialAttributes))
      return $this->__set(strtolower($name),$value); // ist einfacher den magic setter aufzurufen, als hier mit variablen variablen

    $this->attributes[$name] = $value;
    return $this;
  }
  public function removeAttribute($name) {
    unset($this->attributes[$name]);
    return $this;
  }
  public function setAttributes(Array $attributes) {
    foreach ($attributes as $key =>$value) {
      $this->setAttribute($key,$value);
    }
    return $this;
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
    $this->attributes['style'][strtolower($attribute)] = $attribute.': '.$style;
    return $this;
  }
  public function removeStyle($attribute) {
    unset($this->attributes['style'][strtolower($attribute)]);
    return $this;
  }

  /* id */
  /**
   * Setzt eine künstliche ID
   * 
   * Diese Funktion kann später mal schlauer sein
   */
  public function generateId($string) {
    if (strlen($string) == 0)
      throw new Exception('Kann keine leere Id erstellen!');

    $this->setId(FluidGrid_Framework::string2id($string));
  }

  public function setId($id) {
    /* hier mal advanced check ob wir eine doppelte id setzen? */
    $this->attributes['id'] = $id;
  }

  /**
   * 
   * @return string
   */
  public function getId() {
    return @$this->attributes['id'];
  }

  /* Anzeige */
  public function isInline() {
    return (in_array($this->tag,self::$inlineElements));
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