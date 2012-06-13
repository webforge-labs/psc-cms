<?php

namespace Psc\TPL;

use \Psc\TPL\TPL;

/**
 * @group class:Psc\TPL\TPL
 */
class TPLTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\TPL\TPL';
  
  public function testMiniTemplate() {
    
    $tpl = "OIDs %usedOIDs% are used in this game";
    $repl = "OIDs 11601,11602,11603,11604,11605,11606,11617,11618,11619,11620,11621,11653,11624,11625,11626,11627,11633,11639,11640,11647,11648,11650,11654,11655,11656,11657,11658,11659,11660 are used in this game";
    
    $vars = array('usedOIDs'=>
                  '11601,11602,11603,11604,11605,11606,11617,11618,11619,11620,11621,11653,11624,11625,11626,11627,11633,11639,11640,11647,11648,11650,11654,11655,11656,11657,11658,11659,11660'
                  );
    
    $this->assertEquals($repl, TPL::miniTemplate($tpl, $vars));
  }
}

?>