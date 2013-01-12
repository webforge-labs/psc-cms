<?php

namespace Psc\UI;

use \stdClass;

class fHTML extends \Psc\Form\HTML {

  /**
   * Erzeugt ein neues Tag
   * 
   * @param string $name       
   * @param mixed $content
   * @param Array $attributes
   * @return \Psc\UI\HTMLTag
   */
  public static function tag($name, $content = NULL, Array $attributes = NULL) {
    return \Psc\UI\HTML::tag($name, $content, $attributes);
  }
  /**
   * @return array
   */
  public static function radios($name, Array $values, $selected = NULL, Array $commonAttributes = NULL) {
    $html = self::tag('div');
    $name = self::getName($name);
    
    $idCounter = 1;
    foreach ($values as $value => $label) {
      /* Interface Form Item */
      if($label instanceof \Psc\Form\Item) {
        $item = $label;
        $label = $item->getFormLabel();
        $value = $item->getFormValue();
      }
      
      $radio = self::radio($name, $value, $selected, $commonAttributes);
      if (isset($commonAttributes['id'])) {
        $radio->setAttribute('id',$radio->getAttribute('id').'-'.$idCounter);
        $idCounter++;
      }
      
      $l = self::label($label,$radio);
      $l->template = '%radio% %self%';
      $l->templateContent->radio = $radio;
      
      $html->content[] = $l;
    }

    //
    //$html = parent::radios($name,$values,$selected, $commonAttributes);
    //$html->glueContent = "%s\n";
    //
    //foreach ($html->content as $htmlLabel) {
    //  $htmlLabel->contentTemplate = NULL;
    //  $htmlLabel->template = '%radio% %self%';
    //  $htmlLabel->templateContent->radio = $htmlLabel->content->radio;
    //  unset($htmlLabel->content->radio);
    //}
    
    return $html;
  }
}