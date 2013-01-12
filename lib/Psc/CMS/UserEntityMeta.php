<?php

namespace Psc\CMS;

class UserEntityMeta extends EntityMeta {
  
  public function __construct($userClass, $classMetadata = NULL) {
    parent::__construct($userClass ?: \Psc\PSC::getProject()->getUserClass(), $classMetadata ?: new \Doctrine\ORM\Mapping\ClassMetadata('Entities\User'), array('default'=>'User'));
  }
}
?>