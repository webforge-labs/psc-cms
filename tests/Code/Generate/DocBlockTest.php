<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\DocBlock;
use Psc\Doctrine\Annotation;

/**
 * @group generate
 * @group class:Psc\Code\Generate\DocBlock
 */
class DocBlockTest extends \Psc\Code\Test\Base {
  
  protected $docBlock;
  
  protected $summary;
  
  public function setUp() {
    $this->docBlock = new DocBlock();
    $this->docBlock->setSummary($this->summary = 'A nice Docblock documents your code');
    $this->chainClass = 'Psc\Code\Generate\DocBlock';
    $this->test->object = $this->docBlock;
  }
  
  public function testSetAndGetSummary() {
    $this->test->setter('summary',$this->createType('String'));
  }
  
  public function testToString() {
    $this->docBlock->addAnnotation($m2n = $this->createManyToManyAnnotation('persons'));
    
    $actual = $this->docBlock->toString();
    
    $expected = array('/**',
                      ' * '.$this->summary,
                      ' * ',
                      ' * '.$m2n->toString(),
                      ' */',
                      ''
                      );
    
    $this->assertEquals(implode("\n",$expected), $actual);
  }

  public function testToString_withBody() {
    $this->docBlock->addAnnotation($m2n = $this->createManyToManyAnnotation('persons'));
    $this->docBlock->append('returns always the correct things.');
    
    $actual = $this->docBlock->toString();
    
    $expected = array('/**',
                      ' * '.$this->summary,
                      ' * ',
                      ' * returns always the correct things.',
                      ' * '.$m2n->toString(),
                      ' */',
                      ''
                      );
    
    $this->assertEquals(implode("\n",$expected), $actual);
  }
  
  public function testToString_withSummWithoutBodyWithoutAnno() {
    $actual = $this->docBlock->toString();
    $expected = array('/**',
                      ' * '.$this->summary,
                      ' */',
                      ''
                      );
    
    $this->assertEquals(implode("\n",$expected), $actual);
  }

  public function testToString_withSummWithoutBodyWithAnno() {
    $this->docBlock->addAnnotation($m2n = $this->createManyToManyAnnotation('persons'));
    
    $actual = $this->docBlock->toString();
    $expected = array('/**',
                      ' * '.$this->summary,
                      ' * ',
                      ' * '.$m2n->toString(),
                      ' */',
                      ''
                      );
    
    $this->assertEquals(implode("\n",$expected), $actual);
  }

  public function testToString_withoutSummWithoutBodyWithAnno() {
    $docBlock = new DocBlock();
    $docBlock->addAnnotation($m2n = $this->createManyToManyAnnotation('persons'));
    
    $actual = $docBlock->toString();
    $expected = array('/**',
                      ' * '.$m2n->toString(),
                      ' */',
                      ''
                      );
    
    $this->assertEquals(implode("\n",$expected), $actual);
  }
  
  public function testAddAnnotation() {
    $this->assertFalse($this->docBlock->hasAnnotations());
    $this->assertCount(0,$this->docBlock->getAnnotations());
    
    $annotation = $this->createManyToManyAnnotation('persons');
    $this->assertChainable($this->docBlock->addAnnotation($annotation));
    
    $this->assertTrue($this->docBlock->hasAnnotations());
    $this->assertCount(1,$annotations = $this->docBlock->getAnnotations());
    $this->assertEquals(array($annotation), $annotations);
  }

  public function testHasAnnotation() {
    $this->assertFalse($this->docBlock->hasAnnotations());
    
    $this->assertFalse($this->docBlock->hasAnnotation('Doctrine\ORM\Mapping\Id'));
    $this->docBlock->addAnnotation(Annotation::createDC('Id'));
    $this->assertTrue($this->docBlock->hasAnnotation('Doctrine\ORM\Mapping\Id'));

    $this->assertFalse($this->docBlock->hasAnnotation('Doctrine\ORM\Mapping\Column'));
    $this->docBlock->addAnnotation(Annotation::createDC('Column'));
    $this->assertTrue($this->docBlock->hasAnnotation('Doctrine\ORM\Mapping\Column'));
    
    $this->assertCount(2, $this->docBlock->getAnnotations());
  }
  
  public function testGetAnnotionsFilter() {
    $this->docBlock->addAnnotation(Annotation::createDC('Id'));
    $this->docBlock->addAnnotation(Annotation::createDC('Id'));
    $this->docBlock->addAnnotation(Annotation::createDC('Column'));
    $this->assertCount(3, $this->docBlock->getAnnotations());
    $this->assertCount(1, $this->docBlock->getAnnotations('Doctrine\ORM\Mapping\Column'));
    $this->assertCount(2, $this->docBlock->getAnnotations('Doctrine\ORM\Mapping\Id'));
  }
  
  /**
   * @group parse
   * @dataProvider provideStripCommentAsteriks
   */
  public function testStripCommentAsteriks($comment, $stripped) {

    $this->assertEquals($stripped, $this->docBlock->stripCommentAsteriks($comment));
  }
  
  public static function provideStripCommentAsteriks() {
    $tests = array();

$tests[] = array('
/**
 * @TODO inline Kommentare werden verschluckt, inline Kommentare verwirren den Einrücker
 * @TODO imports aus der OriginalKlasse müssen geparsed werden und beibehalten werden!
 *       => wir wollen einen ClassReader haben der aus der Datei die Imports zwischenspeichert udn dann an den ClassWriter geben kann
 *          der dann alles schön schreibt
 *          der ClassReader kann dann auch kommentare die "verloren gehen" verwalten und kann sogar so styles wie "use" oder sowas auslesen
 */
 ',

'@TODO inline Kommentare werden verschluckt, inline Kommentare verwirren den Einrücker
@TODO imports aus der OriginalKlasse müssen geparsed werden und beibehalten werden!
      => wir wollen einen ClassReader haben der aus der Datei die Imports zwischenspeichert udn dann an den ClassWriter geben kann
         der dann alles schön schreibt
         der ClassReader kann dann auch kommentare die "verloren gehen" verwalten und kann sogar so styles wie "use" oder sowas auslesen'
    );

    $tests[] = array(
'/**
  * Die input GClass
  * 
  * @var Psc\Code\Generate\GClass
  */
',
'Die input GClass

@var Psc\Code\Generate\GClass'
);

    $tests[] = array(
'/** BrokenDocBlock Headline

 * Summary continues
*/',
'BrokenDocBlock Headline

Summary continues'
);
    

    $tests[] = array(
'/**
 * Headline
 *
 * Summary is here
 */
',

'Headline

Summary is here');


    $tests[] = array(
'/** Headline */',
'Headline'
);
    

    $tests[] = array(
'/** Headline
*/',
'Headline'
);

    return $tests;
  }

  
  protected function createManyToManyAnnotation($name) {
    if ($name == 'persons') {
      $m2m = new Annotation('Doctrine\ORM\Mapping\ManyToMany');
      $m2m->targetEntity = 'Person';
      $m2m->mappedBy = 'addresses';
      $m2m->cascade = array();
      return $m2m;
    }
  }

  public function testWithGClass() {
    $gClass = new GClass('\ePaper42\Entities\Journal');
    $gClass->setParentClass(new GClass('\Psc\XML\Object'));
    
    $gClass->createProperty('title')
      ->createDocBlock(
        "Titel des Objektes (Name der Zeitschrift)\n"
      )->addSimpleAnnotation('xml:XmlElement');

    $gClass->createProperty('key')
      ->createDocBlock(
        "Key des Objektes\n"
      )->addSimpleAnnotation('xml:XmlElement');

    $gClass->createProperty('volumes')
      ->createDocBlock(
        "Volumes des Journals (Jahreszahlen)\n"
      )->addSimpleAnnotation('xml:XmlElement', array('name'=>'volumne',
                                                     'wrapper'=>'volumeList',
                                                     'type'=>'ePaper42\Entities\Volume'
                                                     ));

$classCode = <<< 'CLASS_END'
class Journal extends \Psc\XML\Object {
  
  /**
   * Titel des Objektes (Name der Zeitschrift)
   * 
   * @xml:XmlElement
   */
  protected $title;
  
  /**
   * Key des Objektes
   * 
   * @xml:XmlElement
   */
  protected $key;
  
  /**
   * Volumes des Journals (Jahreszahlen)
   * 
   * @xml:XmlElement(name="volumne", wrapper="volumeList", type="ePaper42\Entities\Volume")
   */
  protected $volumes;
}
CLASS_END;

    //file_put_contents('D:\fixture.txt',$classCode);
    //file_put_contents('D:\actual.txt',$gClass->php());
    $this->assertEquals($classCode,$gClass->php());
  }
}
?>