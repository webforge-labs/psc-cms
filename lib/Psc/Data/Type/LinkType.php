<?php

namespace Psc\Data\Type;

class LinkType extends \Psc\Data\Type\CompositeType implements InterfacedType {
  
  public function __construct() {
    $this->setComponents(new URIType(), new StringType());
    parent::__construct();
  }
  
  public function getInterface() {
    return $this->getInterfaceDefinition('Link');
  }
  
  protected function defineHint() {
    $this->phpHint = $this->getInterface();
  }
}
?>