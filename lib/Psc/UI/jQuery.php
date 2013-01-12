<?php

namespace Psc\UI;

use Psc\JS\Helper AS JSHelper,
    Psc\JS\Code,
    Psc\JS\Expression,
    Psc\HTML\Tag
  ;

class jQuery extends \Psc\JS\jQuery {

  /**
   * Fügt dem Tag den Initialisierer für ein JQueryUI-Widget hinzu
   *
   * z. B. so:
   * <span id="element"></span>
   * <script type="text/javascript">$('#element').droppable({..})</script>
   */
  public static function widget(Tag $tag, $name, $options = array()) {
    //$tag->addClass('\Psc\jquery-widget');
    
    $js = sprintf("%s(%s)",
                  $name, JSHelper::convertHashMap((object) $options)
                  );
    
    $tag->chain(new Code($js));
    
    return $tag;
  }
  
  /**
   * Fügt dem Tag jquery Data hinzu
   */
  public static function data(Tag $tag, $name, $data) {
    $js = sprintf("data(%s, %s)",
                  JSHelper::convertString($name), JSHelper::convertValue($data)
                  );
    
    $tag->chain(new Code($js));
    
    return $tag;
  }
}
?>