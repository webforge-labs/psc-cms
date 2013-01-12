<?php

namespace Psc\Code\Test;

/*
 * siehe auch CMSAcceptanceTester
 * 
  public function testPageNewForm() {
    $this->test->acceptance('page')->form(NULL)
      ->hasStandardButtons()
      ->hasInput('num')
      ->hasComboDropBox('modes')
      ->hasComboDropBox('oids')
    ;
  }
  
  public function testPageForm() {
    $this->test->acceptance('page')->form(1)
      ->saves(1)
      ->hasStandardButtons()
      ->hasInput('num')
      ->hasComboDropBox('modes')
      ->hasComboDropBox('oids') 
    ;
  }
*/
abstract class Acceptance extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $tester;
  
  public function initAcceptanceTester($tester) {
    $this->tester = $tester;
    //$tester->setUrlPrefix(NULL);
  }

  protected function onNotSuccessfulTest(\Exception $e) {
    try {
      parent::onNotSuccessfulTest($e);
    } catch (\Exception $e) {
      if (isset($this->tester)) {
        print '------------ Acceptance (Fail) ------------'."\n";
        print "\n";
        print $this->tester->getLog();
        print '------------ /Acceptance ------------'."\n";
      }
    }
    
    throw $e;
  }

  protected function dispatchPublic($method, $url, $body = NULL, $type = 'html', $code = 200) {
    $dispatcher = $this->test->acceptance(NULL)->dispatcher($method, $url, $type, $publicRequest = TRUE);
    
    if ($body) {
      $dispatcher->getRequest()->setData($body);
    }
    
    if (!self::$htdocsIsHtaccessProtected)
      $dispatcher->removeAuthentication();
    
    return $this->test->acceptance(NULL)->result($dispatcher, $type, $code);
  }
}
?>