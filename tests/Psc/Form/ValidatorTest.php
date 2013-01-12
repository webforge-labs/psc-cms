<?php

namespace Psc\Form;

/**
 * @group class:Psc\Form\Validator
 */
class ValidatorTest extends \Psc\Code\Test\Base {
  
  protected $validator;
  
  public function setUp() {
    $this->chainClass = 'Psc\Form\Validator';
    parent::setUp();
    $this->validator = new Validator();
  }
  
  public function assertValid($field, $data, $expectedReturn) {
    $this->assertEquals($expectedReturn, $this->validator->validate($field, $data));
  }
  
  public function assertInvalid($field, $data) {
    $this->setExpectedException('Psc\Form\ValidatorException');
    $this->assertValid($field, $data, NULL);
  }
  
  public function testValidatorCanValidateClosureRules() {
    $this->validator->addClosureRule(function ($spamEmail) {
        if ($spamEmail !== 'p.scheit@ps-webforge.com') { // all spammers except me
          throw new \Psc\Form\ValidatorRuleException('Spam Email: '.$spamEmail.' was found and is considered spam.');
        }
        
        return $spamEmail;
      },
      'email'
    );
    
    $this->assertValid('email', 'p.scheit@ps-webforge.com', 'p.scheit@ps-webforge.com');
    $this->assertInvalid('email', 'spammer@viagra.com');
  }
  
  public function testThatOptionalValueIsReturnedAsValueWhenRuleThrowsEmptyException() {
    $this->validator->addClosureRule(function ($something) {
        if ($something === NULL) {
          throw EmptyDataException::factory(array());
        }
        
        return $something;
      },
      'some'
    );
    $this->validator->setOptional('some');
    
    $this->assertValid('some', NULL, array());
  }
}
?>