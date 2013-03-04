<?php

namespace Psc\Form;

use \Psc\HTML\Tag as HTMLTag,
    \Webforge\Common\ArrayUtil AS A;

/**
 * Wir benutzen hier static::tag damit wir in \Psc\UI\fHTML sachen überschreiben können (z.b. addClass)
 */
class HTML {
  
  public static function checkbox($name, $checked = FALSE, $value = 'true', Array $attributes = NULL) {
    $name = self::getName($name);
    $type = 'checkbox';

    $t = static::tag('input')
      ->setAttributes(compact('type','name','value'))
      ->setAttributes((array) $attributes);
      
    if ($checked)
      $t->setAttribute('checked','checked');
    
    return $t;
  }
  
  public static function checkboxLabel($name, $label, $checked = FALSE, $value = 'true', Array $attributes = array()) {
    $checkbox = self::checkbox($name, $checked, $value, $attributes);
    
    $cl = static::tag('label', new \stdClass(), $attributes)->addClass('checkbox-label');
    $cl->contentTemplate = '%checkbox% %label%'; // man beachte hier dass %label% ein string ist und nicht %self%
    
    $cl->content->label = $label;
    $cl->content->checkbox = $checkbox;
    
    return $cl;
  }
  
  public static function text($name, $value = '', Array $attributes = NULL) {
    $name = self::getName($name);
    $type = 'text';
    return static::tag('input')
    ->setAttributes(compact('type','name','value'))
    ->setAttributes((array) $attributes);
  }

  public static function textarea($name, $value = '', Array $attributes = NULL) {
    $name = self::getName($name);
    return static::tag('textarea', self::esc($value), $attributes)
      ->setAttributes(compact('name'));
  }
  
  /**
   * @param mixed $content
   * @param string|HTMLTag $for das Element für das das Label bestimmt ist. hat das Tag keine Id, wird diese automatisch erzeugt
   */
  public static function label($content, $for = NULL, Array $attributes = NULL) {
    $label = static::tag('label', $content, $attributes);
    if (isset($for)) {
      
      if ($for instanceof HTMLTag) {
        $tag = $for;
        if (!$tag->hasAttribute('id')) {
          $tag->generateId();
        }
        
        $for = $tag->getAttribute('id');
      }
      
      $label->setAttribute('for',$for);
    }
    return $label;
  }

  public static function hidden($name, $value, Array $attributes = NULL) {
    $name = self::getName($name);
    return static::tag('input', NULL, array('type'=>'hidden','name'=>$name,'value'=>$value))
      ->setAttributes((array) $attributes);
  }

  /**
   * Erstellt ein Select Element
   * 
   * @param string $name
   * @param HTMLTag[] $options
   * @param mixed $selected das Ausgewählte elemt (muss die value einer option sein)
   * @param Array $attributes
   * @return HTMLTag
   */
  public static function select($name, $options, $selected = NULL, Array $attributes = NULL) {
    $name = self::getName($name);
    
    if (is_string($options)) {
      // nothing, wir haben hier vermutlich schon was zusammengebaut, was so hinkommt
    } else {
      $options = self::selectOptions($options, $selected);
    }
    
    $select = static::tag('select',$options, $attributes)
      ->setAttribute('name',$name)
      ->setGlueContent("%s \n");
    
    return $select;
  }
  
  public static function radio($name, $value, $selected = NULL, Array $attributes = NULL) {
    /* Interface Form Item */
    if($value instanceof \Psc\Form\Item) {
       $item = $value;
       $value = $item->getFormValue();
    }

    $name = self::getName($name);
    $radio = static::tag('input', NULL, $attributes)
      ->setAttribute('name',$name)
      ->setAttribute('type','radio')
      ->setAttribute('value',$value)
    ;
      
    if ($value === $selected) {
      $radio->setAttribute('checked','checked');
    }
    
    return $radio;
  }
  
  /**
   * @return array
   */
  public static function radios($name, Array $values, $selected = NULL, Array $commonAttributes = NULL) {
    $html = static::tag('div')->setClass('fhtml-radios');
    $html->glueContent = "<p>%s</p>\n";
    foreach ($values as $value => $label) {
      /* Interface Form Item */
      if($label instanceof \Psc\Form\Item) {
        $item = $label;
        $label = $item->getFormLabel();
        $value = $item->getFormValue();
      }
      
      $l = self::label(new \stdClass());
      $l->content->radio = self::radio($name, $value, $selected, $commonAttributes);
      $l->content->label = $label;
      $l->contentTemplate = '%radio% %label%';
      $html->content[] = $l;
    }
    
    return $html;
  }

  /**
   * Erstellt einen Array von <option> Elementen für select
   * 
   * @param array $optionsArray $value => $label , $label wird escaped
   * @param mixed $selected die Value von dem Element welches selected="selected" als attribut haben soll
   * @return HTMLTag[]
   */  
  public static function selectOptions($optionsArray, $selected = NULL) {
    $ret = array();
    foreach ($optionsArray as $value => $label) {
      if ($label instanceof HTMLTag) {
        $tag = $label;
        
        if ($tag->getAttribute('value') === $selected)
          $tag->setAttribute('selected','selected');

      } else {
        /* Interface Form Item */
        if($label instanceof \Psc\Form\Item) {
          $item = $label;
          $label = $item->getFormLabel();
          $value = $item->getFormValue();
        }
        
        $tag = static::tag('option',self::esc((string) $label),array('value'=>$value));
        if ($value === $selected)
          $tag->setAttribute('selected','selected');
      }
      
      $ret[] = $tag;
    }
    return $ret;
  }
  
  public static function upload($name, Array $attributes = array()) {
    $name = self::getName($name);
    $type = 'file';
    return static::tag('input')
      ->setAttributes(compact('type','name'))
      ->setAttributes($attributes);
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
  
  
  /**
   * @param string|array $name ist dies ein array wird der Name in PHP-Post-Array Syntax zurückgegeben $name = "var[key1][key2]"
   */
  public static function getName($name) {
    if (is_array($name)) {
      $root = array_shift($name);
      $name = $root.A::join($name,'[%s]');
      
      return $name;
    }
    
    return $name;
  }
  
  public static function esc($value) {
    return \Psc\HTML\HTML::esc($value);
  }
}

?>