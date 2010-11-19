<?php
/**
 * Helper HTML Klasse
 * 
 * Wenn Kohana nicht geladen ist
 */
if (!class_exists('html')) {

  class html {

    /**
     * Convert special characters to HTML entities
     *
     * @param   string   string to convert
     * @param   boolean  encode existing entities
     * @return  string
     */
    public static function specialchars($str, $double_encode = TRUE)
    {
      // Force the string to be a string
      $str = (string) $str;

      // Do encode existing HTML entities (default)
      if ($double_encode === TRUE)
        {
          $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        }
      else
        {
          // Do not encode existing HTML entities
          // From PHP 5.2.3 this functionality is built-in, otherwise use a regex
          if (version_compare(PHP_VERSION, '5.2.3', '>='))
            {
              $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8', FALSE);
            }
          else
            {
              $str = preg_replace('/&(?!(?:#\d++|[a-z]++);)/ui', '&amp;', $str);
              $str = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), $str);
            }
        }

      return $str;
    }
  }
}

?>