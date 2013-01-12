<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;

class CreateAndWipeTargetTask implements \Psc\System\Deploy\Task {
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $target;
  
  public function __construct(Dir $target) {
    $this->target = $target;
  }

  public function run() {
    $this->target->create(); // wenn existierend ist egal
    $this->target->wipe();   // löscht alles daraus
  }
}
?>