<?php

namespace Psc\Code\Test;

use Psc\JS\jQuery;

/**
 * Funktionale erweiterung des CodeTesters für Frontend Sachen
 *
 * HTML Überprüfung usw
 */
class FrontendCodeTester extends CodeTester {
  
  /**
   *
   * für die Constraints siehe CSSTester
   * $test->css('form.myclass', '<form class="myclass">[...]</form>')
   *            ->count(1)  // OK
   * 
   * $test->css('form.otherclass', '<form class="myclass">[...]</form>')
   *            ->count(1) // Failure
   *
   * $test->css('form.otherclass', '<form class="myclass">[...]</form>')
   *            ->count(0) // OK
   * 
   * $test->css('form.myclass', '<form class="myclass">[...]</form>')
   *            ->count(0) // Failure
   *
   * @params string $selector, mixed $html
   * @params jQuery $jQuery
   * @return Psc\Code\Test\CSSTester
   */
  public function css($selector, $html = NULL) {
    return new CSSTester($this->testCase, $selector, $html);
  }
  
  /**
   * @return Psc\Code\Test\JSTester
   */
  public function js(\Psc\JS\JooseWidget $html) {
    return new JSTester($this->testCase, $html->getJoose());
  }
  
  public function joose(\Psc\JS\JooseSnippet $snippet) {
    $jsTester = new JSTester($this->testCase);
    $jsTester->setJooseSnippet($snippet);
    return $jsTester;
  }
  
  /**
   * @return Psc\Code\Test\CMSAcceptanceTester
   */
  public function acceptance($entityName) {
    $tester = new CMSAcceptanceTester($this->testCase, $entityName);
    $this->testCase->initAcceptanceTester($tester);
    return $tester;
  }  
  
  /**
   * Gibt das JSON für den String zurück
   *
   * asserted dass das dekodieren auch klappt
   * @param $array macht alle objekte im json zu arrays! (huh)
   * @return object|array
   */
  public function json($json, $array = FALSE, $canBeEmpty = FALSE) {
    $data = json_decode($json, $array);
    
    $json_errors = array(
      JSON_ERROR_NONE => 'Es ist kein Fehler zuvor aufgetreten, aber der Array ist leer. Es kann mit dem 3ten Parameter TRUE umgangen werden, dass der Array überprüft wird',
      JSON_ERROR_DEPTH => 'Die maximale Stacktiefe wurde erreicht',
      JSON_ERROR_CTRL_CHAR => 'Steuerzeichenfehler, möglicherweise fehlerhaft kodiert',
      JSON_ERROR_SYNTAX => 'Syntax Error',
    );
    
    if ($canBeEmpty && (is_array($data) || is_object($data))) {
      return $data;
    }
    
    $this->testCase->assertNotEmpty($data, "JSON Error: ".$json_errors[json_last_error()]." für JSON-String: '".\Psc\String::cut($json,100,'...'));

    return $data;
  }

  /**
   * Testet ob ein Widget im HTML aufgerufen wird und gibt dessen Parameter als objekt zurück
   * 
   * @return object
   */
  public function jqueryWidget($widgetName, $html) {
    $json = \Psc\Preg::qmatch($html, '/\.'.$widgetName.'\((.*?)\)/s',1);
    $this->testCase->assertNotEmpty($json,'JSON Parameter für .'.$widgetName.' konnte nicht aus dem HTML ausgelesen werden: '.$html);
    
    $data = $this->json($json, FALSE);
    
    return $data;
  }

  public function formInput($html, $label, $name, $value, $type = 'text') {
    $input = $this->css($sel = sprintf('input[type="%s"][name="%s"]',$type,$name), $html)
      ->count(1, 'Count-Selector: '.$sel)
      ->hasAttribute('name', $name)
      ->hasAttribute('value', $value)
      ->getJQuery();
    
    // label muss die id vom input haben
    $this->formLabelFor($html, $label, $input->attr('id'));
  }

  public function formInputArray($html, $label, $name, $value, $type = 'text') {
    $input = $this->css($sel = sprintf('input[type="%s"][name^="%s"]',$type,$name), $html)
      ->atLeast(1, 'Count-Selector: '.$sel)
      ->hasAttribute('value', $value)
      ->getJQuery();
    
    // label muss die id vom input haben
    $this->formLabelFor($html, $label, $input->attr('id'));
  }
  
  public function formLabelFor($html, $label, $for) {
    if ($label != NULL) {
      $this->css($sel = sprintf('label[for="%s"]', $for), $html)
        ->count(1, 'Count-Selector: '.$sel)
        ->hasClass('psc-cms-ui-label')
        ->hasText($label)
        ;
    }
  }
  
  /**
   *
   * Reihenfolge der Optionen ist wichtig
   */
  public function formSelect($html, $label, $name, $selectedValue = NULL, $options = NULL) {
    $select = $this->css($sel = sprintf('select[name="%s"]', $name), $html)
      ->count(1, 'Count-Selector: '.$sel)
      ->hasAttribute('name', $name)
      ->getJQuery();
      
    // options parsen
    $actualSelected = NULL;
    $actualOptions = array();
    foreach ($select->find('option') as $option) {
      $option = new jQuery($option);
      if ($option->attr('selected') != NULL) {
        $this->testCase->assertEmpty($actualSelected, 'Attribut "selected" ist mehrmals in <select> angegeben!');
        $actualSelected = $option->attr('value'); // kann auch leer sein
      }
      
      // @TODO reread: name hier "überschreiben" okay? wiesn das in html?
      $actualOptions[$option->attr('value')] = $option->text();
    }
    
    if (func_num_args() >= 5) {
      $this->testCase->assertEquals($options, $actualOptions, 'Optionen stimmen nicht. (Reihenfolge relevant)');
    }
    
    if (func_num_args() >= 4) {
      $this->testCase->assertEquals($selectedValue, $actualSelected, 'Selected value stimmt nicht');
    }
    
    // label muss die id vom input haben
    if ($label != NULL) {
      $this->formLabelFor($html, $label, $select->attr('id'));
    }
  }
}
?>