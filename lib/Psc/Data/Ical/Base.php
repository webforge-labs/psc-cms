<?php

namespace Psc\Data\Ical;

use \Psc\DateTime\DateTime;
use \Psc\DateTime\Date;
use \Psc\DateTime\DateInterval;

/**
 * Klasse für vielleicht ein paar Date Funktionen oder ähnliches was mit dem Ical Format zu tun hat und in mehreren Klassen nützlich sein könnte
 *
 * RFC 2445
 */
class Base extends \Psc\SimpleObject {
  
  const ICAL_DATE = 'Ymd';
  const ICAL_TIME = 'His\Z';
  
  public function contentLine($key, $value, Array $params = array()) {
    return sprintf("%s%s:%s\r\n", mb_strtoupper($key), $this->icalParams($params), $this->foldValue($value));
  }
  
  public function foldValue($value) {
    $value = \Webforge\Common\String::fixEOL($value);
    $value = wordwrap($value, 74, "\n", TRUE);
    $value = str_replace("\n","\n ",$value);
    return $value;
  }
  
  /**
   *
   *  xparam = x-name "=" param-value *("," param-value)
   */
  public function icalParams(Array $params) {
    if (count($params) === 0) return NULL;
    
    $xparams = NULL;
    foreach ($params as $name => $value) {
      $value = is_array($value) ? implode(',',$value) : $value;
      $xparams .= sprintf(';%s=%s',$name,$value);
    }
    return $xparams;
  }
  
  /**
   *
   * date               = date-value
     date-value         = date-fullyear date-month date-mday
     date-fullyear      = 4DIGIT
     date-month         = 2DIGIT        ;01-12
     date-mday          = 2DIGIT        ;01-28, 01-29, 01-30, 01-31
                                        ;based on month/year
                                        
    
     time               = time-hour time-minute time-second [time-utc]

     time-hour          = 2DIGIT        ;00-23
     time-minute        = 2DIGIT        ;00-59
     time-second        = 2DIGIT        ;00-60
     ;The "60" value is used to account for "leap" seconds.

     time-utc   = "Z"     
   */
  public function icalDateTime(\Psc\DateTime\DateTime $dateTime) {
    return $dateTime->format(self::ICAL_DATE.'\T'.self::ICAL_TIME);
  }
  
  public function icalDate(\Psc\DateTime\Date $date) {
    return $date->format(self::ICAL_DATE);
  }

  /**
   *       recur      = "FREQ"=freq *(

                ; either UNTIL or COUNT may appear in a 'recur',
                ; but UNTIL and COUNT MUST NOT occur in the same 'recur'

                ( ";" "UNTIL" "=" enddate ) /
                ( ";" "COUNT" "=" 1*DIGIT ) /

                ; the rest of these keywords are optional,
                ; but MUST NOT occur more than once

                ( ";" "INTERVAL" "=" 1*DIGIT )          /
                ( ";" "BYSECOND" "=" byseclist )        /
                ( ";" "BYMINUTE" "=" byminlist )        /
                ( ";" "BYHOUR" "=" byhrlist )           /
                ( ";" "BYDAY" "=" bywdaylist )          /
                ( ";" "BYMONTHDAY" "=" bymodaylist )    /
                ( ";" "BYYEARDAY" "=" byyrdaylist )     /
                ( ";" "BYWEEKNO" "=" bywknolist )       /
                ( ";" "BYMONTH" "=" bymolist )          /
                ( ";" "BYSETPOS" "=" bysplist )         /
                ( ";" "WKST" "=" weekday )              /
                ( ";" x-name "=" text )
                )

        freq       = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY"
                / "WEEKLY" / "MONTHLY" / "YEARLY"
        
        
  */
  public function icalRecurrence(\Psc\DateTime\DateInterval $di) {
    if ($di->m > 0 ||
        $di->d > 0 ||
        $di->h > 0 ||
        $di->i > 0 ||
        $di->s > 0) {
      throw new \Psc\Code\NotImplementedException('Ich kann noch nichts anderes außer Jahre! KISSS ');
    }
    
    if ($di->y === 1) {
      return $this->contentLine('RRULE', 'FREQ=YEARLY');
    }
    
    throw new \Psc\Exception('DateInterval kann nicht umgewandelt werden: '.$di);
  }
}
?>