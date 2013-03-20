<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\ContentStreamRepository")
 * @ORM\Table(name="content_streams")
 */
class ContentStream extends CompiledContentStream {

  /**
   * @return string
   */
  public function getTypeClass($typeName) {
    return __NAMESPACE__.'\\'.$typeName;
  }
  
/*  public static function convertTypeName($typeName) {
    return __NAMESPACE__.'\\'.$typeName;
  }
  
  public static function convertClassName($classFQN) {
    return Code::getClassName($classFQN);
  }
  */

  
  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel($context);
    }
    
    return parent::getContextLabel($context);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\ContentStream';
  }
}
?>