<?php

namespace Psc\Code;

class Numbers {
  
  const USE_LOCALE = 'use_local_for_input';
  
  public static function parseFloat($floatString, $thousandsSep = self::USE_LOCALE, $decimalPoint = self::USE_LOCALE) {
    $locale = localeconv();
    if ($thousandsSep === self::USE_LOCALE) {
      $thousandsSep = $locale['mon_thousands_sep'];
    }
    if ($decimalPoint === self::USE_LOCALE) {
      $decimalPoint = $locale['mon_decimal_point'];
    }

    if (mb_substr_count($floatString,',') > 1) {
      throw new \Psc\Exception('Parsing von '.$data.' war nicht möglich. Mehr als ein Komma vorhanden!');
    }
    
    $floatString = str_replace(array($thousandsSep, $decimalPoint), array('', '.'), $floatString);
    return floatval($floatString);
  }
  
  
  public static function formatBytes($bytes) {
    if ($bytes < 1024) {
      return sprintf('%02.2f %s', 0, 'KB');
    }
    
    foreach (array('GB'=>1073741824,
                   'MB'=>1048576,
                   'KB'=>1024
                   ) as $unit => $size) {
      if ($bytes >= $size) {
        return sprintf('%02.2f %s', $bytes/$size, $unit);
      }
    }
  }

    /**
     * Converts a number to its roman numeral representation
     *
     * @param  integer $num         An integer between 0 and 3999
     *                              inclusive that should be converted
     *                              to a roman numeral integers higher than
     *                              3999 are supported from version 0.1.2
     *           Note:
     *           For an accurate result the integer shouldn't be higher
     *           than 5 999 999. Higher integers are still converted but
     *           they do not reflect an historically correct Roman Numeral.
     *
     * @param  bool    $uppercase   Uppercase output: default true
     *
     * @param  bool    $html        Enable html overscore required for
     *                              integers over 3999. default true
     * @return string  $roman The corresponding roman numeral
     *
     * @author David Costa <gurugeek@php.net>
     * @author Sterling Hughes <sterling@php.net>
     */
    public static function toRoman($num) {
        $conv = array(
                      10 => array('X', 'C', 'M'),
                      5 => array('V', 'L', 'D'),
                      1 => array('I', 'X', 'C'));
        $roman = '';

        if ($num < 0) {
            return '';
        }

        $num = (int) $num;

        $digit = (int) ($num / 1000);
        $num -= $digit * 1000;
        while ($digit > 0) {
            $roman .= 'M';
            $digit--;
        }

        for ($i = 2; $i >= 0; $i--) {
            $power = pow(10, $i);
            $digit = (int) ($num / $power);
            $num -= $digit * $power;

            if (($digit == 9) || ($digit == 4)) {
                $roman .= $conv[1][$i] . $conv[$digit+1][$i];
            } else {
                if ($digit >= 5) {
                    $roman .= $conv[5][$i];
                    $digit -= 5;
                }

                while ($digit > 0) {
                    $roman .= $conv[1][$i];
                    $digit--;
                }
            }
        }
      return $roman;
    }
}
?>