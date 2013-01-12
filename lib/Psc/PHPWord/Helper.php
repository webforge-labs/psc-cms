<?php

namespace Psc\PHPWord;

class Helper {
  
  const BORDER_LEFT = 'Left';
  const BORDER_RIGHT = 'Right';
  const BORDER_TOP = 'Top';
  const BORDER_BOTTOM = 'Bottom';
  
  public static function setAllBorders(Array $style, $size=NULL, $color=NULL, Array $borders = array('Top','Bottom','Left','Right')) {
    
    foreach ($borders as $t) {
      if (isset($size))
        $style['border'.$t.'Size'] = $size;
      if (isset($color))
        $style['border'.$t.'Color'] = $color;
    }
    
    return $style;
  }
  
  public static function pt2twip($pt) {
    return (int) round($pt * 20,1);
  }
}


?>