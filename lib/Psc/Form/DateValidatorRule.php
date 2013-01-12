<?php

namespace Psc\Form;

use Psc\DateTime\Date;

/**
 *
 * kann im Moment nur d.m.Y  und d.m. weil date::parse() scheise is
 *
 * zu d.m.
 * Es ist natürlich schwierig ein Datum nur mit d.m. zu parsen, weil das Ergebnis sehr undefiniert ist. Deshalb Konvention:
 * ist d.m. der 29.2. wird 29.2.1972 (denn dies war ein Schaltjahr) zurückgegeben
 * ansonsten wird das datum im Jahr 1970 zurückgegeben
 */
class DateValidatorRule implements ValidatorRule {
  
  protected $format;
  
  public function __construct($dateFormat = 'd.m.Y') {
    if ($dateFormat != 'd.m.Y' && $dateFormat != 'd.m.') {
      throw new \Psc\Code\NotImplementedException('Im Moment gehen nur die Formate: d.m.Y oder d.m.');
    }
    
    $this->format = $dateFormat;
  }
  
  public function validate($data) {
	if ($data === NULL) throw new EmptyDataException();
    // date::parse schmeisst keine Exceptions für invalide Datum! Das ist kacke
    
    if ($this->format === 'd.m.') {
      /* Schaltjahrproblematik macht stress mit DateTime::parse */
      if (($match = \Psc\Preg::qmatch($data,'/^([0-9]+)\.([0-9]+)\.$/', array(1,2))) !== NULL) {
        list($d, $m) = $match;
        if ($d == 29 && $m == 2) {
          $date = new Date($d.'.'.$m.'.1972');
        } else {
          $date = new Date($d.'.'.$m.'.1970');
        }
      } else {
        throw new \Psc\DateTime\ParsingException('Aus '.$data.' kann kein Datum extrahiert werden. Erwartetes Format: '.$this->format);
      }
    } else {
      $date = new Date($data);
    }
    
    return $date;
  }
  
  /**
   * @param string $format
   * @chainable
   */
  public function setFormat($format) {
    $this->format = $format;
    return $this;
  }

  /**
   * @return string
   */
  public function getFormat() {
    return $this->format;
  }
}
?>