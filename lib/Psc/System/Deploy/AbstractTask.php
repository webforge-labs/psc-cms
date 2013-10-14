<?php

namespace Psc\System\Deploy;

use Psc\Code\Code;

abstract class AbstractTask implements LabelTask {

  private $taskName;

  public function __construct($taskName) {
    $this->taskName = $taskName ?: Code::getClassName(get_class($this));
  }

  public function label($l) {
    $this->label = $l;
    return $this;
  }

  public function getLabel() {
    if (isset($this->label)) {
      return $this->taskName.': '.$this->label;
    } else {
      return $this->taskName;
    }
  }
}
