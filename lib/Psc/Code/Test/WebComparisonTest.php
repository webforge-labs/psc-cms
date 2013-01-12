<?php

namespace Psc\Code\Test;

use \Psc\URL\Request,
    \Psc\Code\Code,
    \Psc\PSC
;

class WebComparisonTest extends \PHPUnit_Framework_TestCase {
  
  protected $debugCounter = 1;
  
  public function setUp() {
    $this->debugOutDir = PSC::get(PSC::PATH_TESTDATA)->sub('webCompOut');
    if (!$this->debugOutDir->exists()) $this->debugOutDir->make('-p');
  }
  
  /**
   * Die URLs können auch Psc\URL\Requests sein, falls man passwortschutz hat oder schweinerein
   */
  protected function assertWebEquals($expectedURL, $actualURL, $message = '') {
    $this->assertNotEmpty($expectedURL, 'Expected URL muss gesetzt sein');
    $this->assertNotEmpty($actualURL,'Actual URL muss gesetzt sein');
    
    $reqExpected = $expectedURL instanceof Request ? $expectedURL : new \Psc\URL\Request($expectedURL);
    $reqActual = $actualURL instanceof Request ? $actualURL : new \Psc\URL\Request($actualURL);
    
    print "checking: ".$expectedURL."\n";
    $expectedContent = $reqExpected->init()->process();
    $actualContent = $reqActual->init()->process();
    
    
    if (!empty($expectedContent) && ($expectedContent != $actualContent)) {
      if (isset($this->debugOutDir)) {
        file_put_contents($this->debugOutDir.'webcomp_'.$this->debugCounter.'_actual.txt',$actualContent);
        file_put_contents($this->debugOutDir.'webcomp_'.$this->debugCounter.'_expected.txt',$expectedContent);
        $this->debugCounter++;
      }
    }
    
    $this->assertTrue(!empty($expectedContent) && ($expectedContent === $actualContent),
                      $message ?: 'Contents von '.$expectedURL.' und '.$actualURL.' sind nicht gleich. Output der Diffs in: '.$this->debugOutDir
                     );
  }
  
  protected function passwordRequest($username, $password, $url) {
    $req = new Request($url);
    $req->setAuthentication($username, $password);
    return $req;
  }
  
  protected function assertWebEqualsRelative($expectedURL, $actualURL, $relative, $message = '') {
    $this->assertNotEmpty($relative, 'Es muss eine relative URL einer Seite angegeben werden, die in beiden URLs existiert');
    $expectedURL = rtrim($expectedURL,'/').'/'.ltrim($relative,'/');
    $actualURL = rtrim($actualURL,'/').'/'.ltrim($relative,'/');
    $this->assertWebEquals($expectedURL, $actualURL, $message);
  }
}
?>