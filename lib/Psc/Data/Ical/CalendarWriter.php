<?php

namespace Psc\Data\Ical;

use Webforge\Common\System\File;

/**
 *
 * http://tools.ietf.org/html/rfc2445
 */
class CalendarWriter extends \Psc\SimpleObject {
  
  const OVERWRITE = 'overwrite__FILE';
  
  public function __construct(Calendar $calendar) {
    $this->calendar = $calendar;
  }
  
  /**
   * @var Psc\Data\Ical\Calendar
   */
  protected $calendar;
  
  /**
   * @param Psc\Data\Ical\Calendar $calendar
   * @chainable
   */
  public function setCalendar(Calendar $calendar) {
    $this->calendar = $calendar;
    return $this;
  }

  /**
   * @return Psc\Data\Ical\Calendar
   */
  public function getCalendar() {
    return $this->calendar;
  }


  public function write(File $file, $overwrite = NULL) {
    if ($overwrite !== self::OVERWRITE && $file->exists()) {
      throw BuilderException::create("Die Datei '%s' existiert. Es muss \$overwrite = self::OVERWRITE übergeben werden, um die Datei zu überschreiben", $file);
    }
    
    $file->writeContents($this->calendar->ical());
    
    return $this;
  }  
}
?>