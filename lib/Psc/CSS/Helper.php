<?php

namespace Psc\CSS;

use \Psc\HTML\HTML;

class Helper {

  public static function load($cssFile, $media = 'all') {
    if (empty($media)) $media = 'all';
    
    return HTML::tag('link', NULL, array('rel'=>'stylesheet','media'=>$media,'type'=>'text/css','href'=>$cssFile))
      ->setOption('selfClosing',TRUE);
    ;
  }
}
?>