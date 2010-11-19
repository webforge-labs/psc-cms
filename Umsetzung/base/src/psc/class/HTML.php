<?php

class HTML {
  
  public static function esc($value) {
    return htmlspecialchars($value,ENT_NOQUOTES, 'UTF-8');
  }
  
  public static function escAttr($value) {
    return htmlspecialchars($value,ENT_QUOTES, 'UTF-8');
  }
  
}

?>