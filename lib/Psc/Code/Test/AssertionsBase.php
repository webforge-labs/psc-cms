<?php

namespace Psc\Code\Test;

use Closure;
use Webforge\Common\System\File;
use Psc\Code\Code;
use Doctrine\Common\Collections\Collection;
use Psc\PHPUnit\InvokedAtMethodIndexMatcher;
use Psc\PHPUnit\InvokedAtMethodGroupIndexMatcher;

class AssertionsBase extends \PHPUnit_Framework_TestCase {

  /* CUSTOM ASSERTIONS */
  /**
   * So wie assertEquals jedoch werden die arrays canonicalized (normalisiert, bzw sortiert)
   */
  public function assertArrayEquals($expected, $actual, $message = '', $maxDepth = 10) {
    return $this->assertEquals($expected, $actual, $message, 0, $maxDepth, TRUE);
  }
  
  public function assertHasDCAnnotation(\Psc\Code\Generate\DocBlock $docBlock, $dcAnnotationName) {
    return $this->assertHasAnnotation($docBlock, 'Doctrine\ORM\Mapping\\'.$dcAnnotationName);
  }
  
  /**
   * @return Annotation
   */
  public function assertHasAnnotation(\Psc\Code\Generate\DocBlock $docBlock, $annotationFQN) {
    $this->assertTrue($docBlock->hasAnnotation($annotationFQN),
                      'Docblock hat nicht die DoctrineAnnotation: '.$annotationFQN);
    return $docBlock->getAnnotation($annotationFQN);
  }

  
  /**
   * vorher $this->chainClass setzen!
   */
  public function assertChainable($actual, $message = NULL) {
    $this->assertNotEmpty($this->chainClass,'$this->chainClass muss für assertChainable gesetzt sein!');
    return $this->assertInstanceOf($this->chainClass, $actual, $message ?: 'Chainable Test failed, da die Methode kein Objekt der Klasse '.$this->chainClass.' zurück gibt.');
  }
  
  /**
   * @return CaughtException wenn sie gefangen wird
   */
  public function assertException($class, Closure $closure, $expectedCode = NULL, $expectedMessage = NULL, $debugMessage = NULL) {
    try {
      $closure();
      
    } catch (\Exception $e) {
      $this->assertInstanceOf($class, $e,
                              sprintf("Exception hat nicht die richtige Klasse (expected: '%s' actual:'%s'):\n%s\n%s",
                                      $class, \Psc\Code\Code::getClass($e), $e, $debugMessage)
                             );
      
      if (isset($expectedCode)) {
        $this->assertEquals($expectedCode, $e->getCode(),'Code ist falsch');
      }
      
      if (isset($expectedMessage)) {
        $this->assertEquals($expectedMessage, $e->getMessage(), 'Message ist falsch');
      }
      
      return $e;
    }
    
    $this->fail('Exception: '.$class.' erwartet. Es wurde aber keine gecatched. '.$debugMessage);
  }
  
  public function assertAssertionFail(Closure $closure, $expectedMessage = NULL, $debugMessage = NULL) {
    return $this->assertException('PHPUnit_Framework_AssertionFailedError', $closure, NULL, $expectedMessage, $debugMessage);
  }
  
  public function expectAssertionFail() {
    $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
  }

  public function assertInstanceOfCollection($actual, $message = '') {
    $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $actual, $message);
  }
  
  public function assertInstanceOfMock($actual, $message = '') {
    $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockObject', $actual, $message);
  }


  public function assertEntityCollectionSame(Collection $expectedCollection, Collection $actualCollection, $collectionName = NULL, $compareFunction = \Psc\Data\ArrayCollection::COMPARE_OBJECTS) {
    if (!($expectedCollection instanceof \Psc\Data\ArrayCollection)) {
      $expectedCollection = new \Psc\Data\ArrayCollection($expectedCollection->toArray());
    }
    
    $this->assertTrue($expectedCollection->isSame($actualCollection, $compareFunction),
                      "Failed asserting that Collections ".($collectionName ? "'".$collectionName."'" : '(not-named)')." are the same:\n".
                      "\n".
                      "expectedCollection:\n".
                      \Psc\Doctrine\Helper::debugCollection($expectedCollection)."\n".
                      "\n".
                      "actualCollection:\n".
                      \Psc\Doctrine\Helper::debugCollection($actualCollection)."\n"
                     );
  }

  public function assertEntityCollectionEquals(Collection $expectedCollection, Collection $actualCollection, $collectionName = NULL, $compareFunction = \Psc\Data\ArrayCollection::COMPARE_OBJECTS) {
    if (!($expectedCollection instanceof \Psc\Data\ArrayCollection)) {
      $expectedCollection = new \Psc\Data\ArrayCollection($expectedCollection->toArray());
    }
    
    $this->assertTrue($expectedCollection->isEqual($actualCollection, $compareFunction),
                      
                      "Failed asserting that Collections ".($collectionName ? "'".$collectionName."'" : '(not-named)')." are equal:\n".
                      "\n".
                      "expectedCollection:\n".
                      \Psc\Doctrine\Helper::debugCollection($expectedCollection)."\n".
                      "\n".
                      "actualCollection:\n".
                      \Psc\Doctrine\Helper::debugCollection($actualCollection)."\n"
                     );
  }


  public function assertInstanceOfComponent($expectedComponentClass, $actual, $message = '') {
    $expectedComponentClass = Code::expandNamespace($expectedComponentClass, 'Psc\UI\Component');
    $this->assertInstanceOf($expectedComponentClass, $actual, $message);
  }
  
  public static function assertFileEquals($expected, $actual, $message = '', $canonincalize = FALSE, $ignoreCase = FALSE) {
    if ($expected instanceof \Webforge\Common\System\File)
      $expected = (string) $expected;
      
    if ($actual instanceof \Webforge\Common\System\File)
      $actual = (string) $actual;
      
    return parent::assertFileEquals($expected, $actual, $message, $canonincalize, $ignoreCase);
  }
  
  
  public function assertJavaScriptEquals($expected, $actual) {
    $parser = new \Psc\JS\JParser();
    
    $parse = function($js, $label) use ($parser) {
      try {
        $ast = $parser->parse_string($js);
      } catch (\Exception $e) {
        throw new \Exception("Fehler (".$e->getMessage().") beim Parsen von ".$label."-javascript:\n".
                             "\n".
                             \Webforge\Common\String::lineNumbers($js),
                             0,
                             $e
                            );
      }
      return $ast;
    };
    
    $actualAST = $parse($actual, 'actual');
    $expectedAST = $parse($expected, 'expected');
    
    // equals mit minified code, welches den whitespace normalisiert
    return $this->assertEquals((string) $expectedAST, (string) $actualAST);

    //return $this->assertXMLStringEqualsXMLString(\Psc\JS\JParser::dumpNode($expectedAST),
    //                                             \Psc\JS\JParser::dumpNode($actualAST),
    //                                             
    //                                             \Webforge\Common\String::debugEquals((string) $expectedAST, (string) $actualAST) // debug minified
    //                                             );
  }
  
  public function assertTestOK(\Psc\Code\Test\Base $test) {
    return $this->assertTest($test, TRUE);
  }

  public function assertTestFailure(\Psc\Code\Test\Base $test) {
    return $this->assertTest($test, FALSE);
  }

  public function assertClosureTest(Closure $testCode, $expectedSuccess = TRUE, $testCaseLabel = 'unknown OK Closure TestCase') {
    return $this->assertTest($this->doublesManager->createClosureTestCase($testCode, $testCaseLabel), $expectedSuccess);
  }
  
  /**
   * @see Psc\Code\Test\ClosureTestCase
   * @return Psc\Code\Test\Base
   */
  protected function assertTest(\Psc\Code\Test\Base $test, $expectedSuccess = TRUE) {
    $test->setUseOutputBuffering(FALSE);

    /* RUN + Ergebnis */
    ob_start();
    print "********** inner Test *************\n";
    
    $result = $test->run();
    print "\n";
    print "********** Result *************";
    $printer = new \PHPUnit_TextUI_ResultPrinter();
    $printer->printResult($result);
    print "\n";
    print "********** /inner Test *************\n";
    $message = ob_get_contents();
    ob_end_clean();

    $this->assertEquals($expectedSuccess,
                        $result->wasSuccessful(),
                        sprintf("Test Ergebnis '%s' erwartet aber der Test %s.\nOutput:\n%s",
                                $expectedSuccess ? 'OK' : 'Failure',
                                $result->wasSuccessful() ? 'war OK' : 'schlug fehl',
                                //$test->getStatusMessage(),
                                \Webforge\Common\String::indent($message,4)
                                )
                       );
    $this->assertEquals(0,
                        $result->errorCount(),
                        sprintf('Inner Test hatte Errors! (False Positive check)'."\n".
                                "Test Ergebnis war wie erwartet aber der Test enthielt Fehler (nicht Failures!).\n".
                                "Output:\n\n%s",
                                \Webforge\Common\String::indent($message,4)
                                ));

    return $test;
  }

    /**
     * Returns a matcher that matches when *the method* it is evaluated for is invoked at the given $index.
     *
     * @param  integer $index
     * @param  string  $method
     * @return Psc\PHPUnit\InvokedAtMethodIndexMatcher;
     */
    public static function atMethod($method, $index)
    {
        return new InvokedAtMethodIndexMatcher($index, $method);
    }

    /**
     * Returns a matcher that matches when *the method* it its group is evaluated for ,is invoked at the given $groupIndex.
     *
     * @param  integer $index
     * @param  string  $method
     * @param  string[]  $methodGroup an array of methods that should be counted for the groupIndex
     * @return Psc\PHPUnit\InvokedAtMethodGroupIndexMatcher;
     */
    public static function atMethodGroup($method, $groupIndex, array $methodGroup)
    {
        return new InvokedAtMethodGroupIndexMatcher($groupIndex, $method, $methodGroup);
    }

}
?>