<?php

namespace Psc\Data;

use Psc\Data\RandomGenerator;
use Psc\Data\Type\StringType;
use Psc\Data\Type\IntegerType;
use Psc\Data\Type\BooleanType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\Type;

/**
 * Das ist mal echt kein schöner Test, ich bin aber gerne offen für neuere Vorschläge
 *
 * die "eigenlichen" Daten muss man hier tatsächlich per "sichttest" testen
 * weil wie soll da eine assertion aussehen: assertEquals ungefähr nicht gleich? ;)
 * @group class:Psc\Data\RandomGenerator
 */ 
class RandomGeneratorTest extends \Psc\Code\Test\Base {
  
  protected $it = 100;

  public function setUp() {
    $this->chainClass = 'Psc\Data\RandomGenerator';
    parent::setUp();
    
    $this->verbose = FALSE;
  }

  /**
   * @group string
   */
  public function testRandomDataGeneration_String() {
    if ($this->verbose) print "randomData: String";
    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData('String');
      $this->assertInternalType('string',$data);
      $this->assertGreaterThan(0,mb_strlen($data));
      if ($this->verbose) print $data."\n";
    }
  }

  /**
   * @group string
   */
  public function testGenerateString_length() {
    $generator = $this->createRandomGenerator();
    for ($i = 1; $i<= 20; $i++) {
      $this->assertEquals($i, mb_strlen($generator->generateString($i)));
    }
  }

  /**
   * @group integer
   */
  public function testRandomDataGeneration_Integer() {
    if ($this->verbose) print "randomData: Integer";
    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData('Integer');
      $this->assertInternalType('int',$data);
      if ($this->verbose) print $data."\n";
    }
  }

  public function testRandomDataGeneration_Boolean() {
    if ($this->verbose) print "randomData: Bool";
    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData('Boolean');
      $this->assertInternalType('bool',$data);
      if ($this->verbose) print ($data?'true':'false')."\n";
    }
    // haha geiler test: wie groß ist die wahrscheinlichkeit, dass ein run von 9 bei 20 iterationen kommt? ;D
  }

  public function testRandomDataGeneration_Array_WithoutType() {
    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData('Array');
      $this->assertInternalType('array',$data);
      $this->assertGreaterThan(0,count($data));
      if ($this->verbose) var_dump($data);
      if ($this->verbose) print "\n";
    }
  }
  
  public function testRandomDataGeneration_Array_WithType() {
    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData(new ArrayType(new IntegerType()));
      $this->assertInternalType('array',$data);
      $this->assertGreaterThan(0,count($data));
      foreach ($data as $randomEntry) {
        $this->assertInternalType('int',$randomEntry);
      }
      //var_dump($data);
      //print "\n";
    }

    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData(new ArrayType(new BooleanType()));
      $this->assertInternalType('array',$data);
      $this->assertGreaterThan(0,count($data));
      foreach ($data as $randomEntry) {
        $this->assertInternalType('bool',$randomEntry);
      }
      //var_dump($data);
      //print "\n";
    }

    for ($i = 1; $i<=$this->it; $i++) {
      $data = $this->getRandomData(new ArrayType(new StringType()));
      $this->assertInternalType('array',$data);
      $this->assertGreaterThan(0,count($data));
      foreach ($data as $randomEntry) {
        $this->assertInternalType('string',$randomEntry);
      }
      //var_dump($data);
      //print "\n";
    }
  }
  
  /**
   * @expectedException Psc\Code\NotImplementedException
   */
  public function testRandomGeneratorIsNotFullyImplemented() {
    $this->getRandomData(new \Psc\Data\Type\LinkType());
  }

  protected function getRandomData($typeName) {
    if (!($typeName instanceof \Psc\Data\Type\Type)) {
      $type = Type::create($typeName);
    } else {
      $type = $typeName;
    }
    
    $generator = $this->createRandomGenerator();
    return $generator->generateData($type);
  }

  public function createRandomGenerator() {
    return new RandomGenerator();
  }
}
?>