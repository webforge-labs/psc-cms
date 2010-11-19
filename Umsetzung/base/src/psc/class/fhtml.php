<?php

class fHTML {
  
  public static function checkbox($name, $checked = FALSE, $value = 'true', Array $attributes = NULL) {
    $type = 'checkbox';

    $t = self::tag('input')
      ->setAttributes(compact('type','name','value'))
      ->setAttributes((array) $attributes);
      
    if ($checked)
      $t->setAttribute('checked','checked');
    
    return $t;
  }
  
  public static function text($name, $value = '', Array $attributes = NULL) {
    $type = 'text';
    return self::tag('input')
    ->setAttributes(compact('type','name','value'))
    ->setAttributes((array) $attributes);
  }

  public static function textarea($name, $value = '', Array $attributes = NULL) {
    return self::tag('textarea', HTML::esc($value), $attributes)
    ->setAttributes(compact('name'));
  }
  
  public static function label($content, $for = NULL, Array $attributes = NULL) {
    $label = self::tag('label', $content, $attributes);
    if (isset($for)) $label->setAttribute('for',$for);
    return $label;
  }

  /**
   * Erstellt ein Select Element
   * 
   * @param string $name
   * @param HTMLTag[] $options
   * @param Array $attributes
   * @return HTMLTag
   */
  public static function select($name, Array $options, Array $attributes = NULL) {
    if (count($options) > 0 && !($options[0] instanceof HTMLTag)) {
      $options = self::selectOptions($options);
    }
    $select = self::tag('select',$options, $attributes)
      ->setAttribute('name',$name)
      ->setGlueContent("%s \n");
    
    return $select;
  }

  /**
   * Erstellt einen Array von <option> Elementen für select
   * 
   * @param array $optionsArray $value => $label , $label wird escaped
   * @return HTMLTag[]
   */  
  public static function selectOptions($optionsArray) {
    $ret = array();
    foreach ($optionsArray as $value => $label) {
      $ret[] = self::tag('option',HTML::esc($label),array('value'=>$value));
    }
    return $ret;
  }
  
  /**
   * Erzeugt ein neues Tag
   * 
   * @param string $name       
   * @param mixed $content
   * @param Array $attributes
   * @return HTMLTag
   */
  public static function tag($name, $content = NULL, Array $attributes = NULL) {
    $tag = new HTMLTag($name, $content, $attributes);
    return $tag;
  }
}

?>