<?php

namespace Psc\UI;

interface CalendarEvent extends \Psc\Data\Exportable {
  
  public function getTitle($lang);

  /**
   * @return Psc\DateTime\DateTime
   */
  public function getStart();

  /**
   * @return Psc\DateTime\DateTime
   */
  public function getEnd();
  
  /**
   * @return Psc\Data\Color
   */
  //public function getColor();
  
  /**
   * @return bool
   */
  public function isAllDay();
  
}
?>