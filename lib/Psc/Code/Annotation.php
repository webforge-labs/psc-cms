<?php

namespace Psc\Code;

use Psc\Code\Generate\AnnotationWriter;

/**
 * Base Klasse für alle Annotations aus dem Psc - CMS Framework
 *
 */
abstract class Annotation extends \Psc\SimpleObject {
  
  public function getAnnotationName() {
    return Code::getClass($this);
  }

  public function toString($annotationWriter = NULL) {
    if (!isset($annotationWriter)) {
      $annotationWriter = new AnnotationWriter();
      //$annotationWriter->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping');
      // siehe auch bei EntityBuilder
      $annotationWriter->setAnnotationNamespaceAlias('Doctrine\ORM\Mapping', 'ORM');
    }
    
    return $annotationWriter->writeAnnotation($this->getInnerAnnotation());
  }
  
  public function getInnerAnnotation() {
    // das sieht etwas dumm aus, brauchen wir aber für \Psc\Doctrine\Annotation damit wir toString() nicht neu ableiten müssen
    return $this;
  }
}
?>