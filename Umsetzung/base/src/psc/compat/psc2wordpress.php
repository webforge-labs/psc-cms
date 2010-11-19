<?php

define ('compat_mode','psc2wordpress');

class HTML {
  
  public static function esc($value) {
    return esc_html($value);
  }
  
  public static function escAttr($value) {
    return esc_attr($value);
  }
}

?>