<?php

namespace Psc\System\Console;

class DoctrinePackageHelper extends \Symfony\Component\Console\Helper\Helper {
  
  protected $dc;
  
  public function __construct(\Psc\Doctrine\DCPackage $dc) {
    $this->dc = $dc;
  }
  
  public function unwrap() {
    return $this->dc;
  }

  /**
   * @see Helper
   */
  public function getName() {
    return 'dc';
  }
}
?>