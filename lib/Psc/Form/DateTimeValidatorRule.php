<?php

namespace Psc\Form;

use Psc\DateTime\DateTime;

/**
 * 
 */
class DateTimeValidatorRule extends \Psc\SimpleObject implements ValidatorRule {
  
  /**
   * @var bool
   */
  protected $timeIsOptional;
  
  public function __construct($timeIsOptional = FALSE) {
    $this->setTimeIsOptional($timeIsOptional);
  }
  
  /**
   *
   * Empty Data ist NULL
   * 
   * @return DateTime
   */
  public function validate($data) {
    if (is_integer($data) && $data > 0) {
      return new DateTime($data);
    }
    
    if ($data === NULL
        || !is_array($data)
        || !array_key_exists('date',$data)
        || $data['date'] == NULL
        
        || !$this->timeIsOptional && (!array_key_exists('time',$data) || $data['time'] == NULL)
       ) {
      
      throw EmptyDataException::factory(NULL);
    }
    $data['time'] = !isset($data['time']) ? NULL : trim($data['time']);
    
    $dateRule = new DateValidatorRule('d.m.Y');
    $date = $dateRule->validate($data['date']);
    
    // cooler: time validator rule
    if ($this->timeIsOptional && $data['time'] == NULL) {
      return new DateTime($date); // sets time to 00:00
    } else {
      return DateTime::parse('d.m.Y H:i', $date->format('d.m.Y').' '.$data['time']);
    }
  }
  
  /**
   * @param bool $timeIsOptional
   */
  public function setTimeIsOptional($timeIsOptional) {
    $this->timeIsOptional = (bool) $timeIsOptional;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getTimeIsOptional() {
    return $this->timeIsOptional;
  }
}
?>