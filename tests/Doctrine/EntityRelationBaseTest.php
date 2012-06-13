<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Doctrine\ORM\Mapping\ClassMetadata;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Code;
use Psc\Code\Generate\DocBlock;

/**
 * Der Base Test liefert jede Menge Assertions für den eigentlichen Test
 */
abstract class EntityRelationBaseTest extends \Psc\Code\Test\Base {
  
  protected $relation;
  
  /**
   * @var Doctrine\ORM\Mapping\ClassMetadataInfo
   */
  protected $cm;

  /**
   * Die momentan zu untersuchende Association aus der ClassMetadataInfo
   * @var array
   */
  protected $assoc;
  
  public function setUp() {
    parent::setUp();

    $this->reader = new \Doctrine\Common\Annotations\AnnotationReader(new \Doctrine\Common\Cache\ArrayCache());
    $this->annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($this->reader);
  }

  protected function assertSides($sourceSideType, $targetSideType, EntityRelation $relation = NULL) {
    $relation = $relation ?: $this->relation;
    $this->assertEquals($sourceSideType, $relation->getSourceSide(), 'Failed asserting that sourceSide is: '.($sourceSideType === EntityRelation::NONE ? 'none' : $sourceSideType));
    $this->assertEquals($targetSideType, $relation->getTargetSide(), 'Failed asserting that targetSide is: '.($targetSideType === EntityRelation::NONE ? 'none' : $targetSideType));
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\Annotation
   */
  protected function assertHasRelationAnnotation($expectedName, $expectedProperties, DocBlock $actualDocBlock) {
    Code::value($expectedName, 'OneToMany','ManyToOne','OneToOne','ManyToMany');
    $this->assertEquals(Annotation::createDC($expectedName, $expectedProperties),
                        $annotation = $this->assertHasDCAnnotation($actualDocBlock, $expectedName)
                       );
    return $annotation;
  }
  
  // ab hier alles in den EntityBuilder Test (denn der dreht sich um die Klasse)
  
  

  //protected function assertRelationSource(EntityRelation $relation) {
  //  $gClass = new GClass('CompiledSourceEntity');
  //  $relation->buildSourceIn($gClass);
  //  $this->cm = $this->createClassMetadata($gClass);
  //  
  //  return $this;
  //}
  
  //protected function hasAssociation($fieldName) {
  //  $this->assertTrue($this->cm->hasAssociationMapping($fieldName), 'has Association '.$fieldName);
  //  $this->assoc = $this->cm->getAssociationMapping($fieldName);
  //  return $this;
  //}

  protected function end() {
    $this->assoc = NULL;
    return $this;
  }
  
  public function createClassMetadata(GClass $gClass) {
    // da wir gClass mehrmals im Test evalen könnten, erzeugen wir einen unique hash für den classname und übergeben den
    // der class metadata
    
    $className = uniqid($gClass->getClassName());
    $gClass->setClassName($className);

    $gClass->createDocBlock()
      ->addAnnotation(Annotation::createDC('Entity'))
      //->addAnnotation(Annotation::createDC('Table', array('name'=>\Doctrine\Common\Util\Inflector::tableize($this->getEntityName()).'s')))
    ;
    
    $classWriter = new ClassWriter($gClass);
    $classWriter->setUseStyle('lines');
    $classWriter->addImport(new GClass('Doctrine\ORM\Mapping'),'ORM'); // braucht einen AnnotationReader nicht SimpleAnnotationReader

    $classWriter->write($file = $this->newFile('entity.'.$gClass->getClassName().'.php'));
    require $file;

    $cm = new ClassMetadata($gClass->getFQN());
    $cm->initializeReflection(new \Doctrine\Common\Persistence\Mapping\RuntimeReflectionService);
    $this->annotationDriver->loadMetadataForClass($gClass->getFQN(), $cm);
    
    $file->delete();
    
    return $cm;
  }
}
?>