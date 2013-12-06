<?php

namespace Psc\Form;

use \Psc\Code\Code;

class Validator extends \Psc\Object {
  
  const APPEND = 'append';
  const PREPEND = 'prepend';
  
  const OPTIONAL        = 0x000002;
  
  protected $rules = array();
  
  /**
   * @var array
   */
  protected $optionalFields = array();
  
  /**
   * Wird für die ValidatorException benutzt
   *
   * 1. Name des Feldes
   * 2. Message der inneren Exception (von der Rule)
   * 3. Code der Exception (nummer)
   * @var string sprintf-format
   */
  public $standardMessage = "Feld '%s' konnte nicht validiert werden. %s";
  public $standardEmptyMessage = "Feld '%s' muss ausgefüllt werden.";
  
  /**
   * Wenn gesetzt wird er für jeden validate($field)-Aufruf mit getValidatorData($field) befragt
   *
   * @var ValidatorDataProvider
   */
  protected $dataProvider;
  
  public function __construct() {

  }
  
  /**
   * Validiert die Daten zu einem Feld
   *
   * gibt die Daten zurück!
   * Wenn das Feld nicht validiert werden, wird eine Exception geworfen!
   * @throws ValidatorException wenn die Daten nicht zum Feld passen
   *
   * ist $flags OPTIONAL gesetzt und wird die Rule eine EmptyDataException werfen, wird der DefaultWert (normal: NULL) der Exception zurückgegeben
   * wenn $this->dataProvider gesetzt ist, wird IMMER $this->dataProvider->getValidatorData() benutzt und nicht der $data Parameter
   */
  public function validate($field, $data = NULL, $flags = 0x000000) {
    $key = $this->getKey($field);
    
    if (isset($this->dataProvider)) {
      $data = $this->dataProvider->getValidatorData($field);
    }
    
    if ($this->hasField($field)) {
      
      try {
          
        foreach ($this->rules[$key] as $rule) {
          $data = $rule->validate($data); // wenn wir hier data zurückgeben, können wir die rules chainen
        }
        
      } catch (EmptyDataException $e) {
        if (($flags & self::OPTIONAL) == self::OPTIONAL || $this->isOptional($field)) {
          return $e->getDefaultValue();

        } else {
          if ($e->getMessage() == NULL) {
            $e->setMessage(sprintf($this->standardEmptyMessage,$this->getKey($field)));
          }

          throw $this->convertToValidatorException($e, $field);
        }

      } catch (\Exception $e) {
        throw $this->convertToValidatorException($e, $field);
      }
      
    } else {
      throw new \Psc\Exception('Feld: '.Code::varInfo($field).' ist dem Validator nicht bekannt.');
    }
    
    return $data;
  }

  protected function convertToValidatorException($e, $field) {
    $fex = new ValidatorException(
      sprintf(
        $this->standardMessage,
        $this->getLabel($field), $e->getMessage(), $e->getCode()
      ), 
      (int) $e->getCode(),
      $e
    );
    $fex->field = $field;
    $fex->data = $data;
    return $fex;
  }
  
  /**
   * @param traversable $fieldsData die Daten sind nach FeldNamen geschlüsselt Schlüssel
   * @return array $validatedData
   * @throws Psc\Form\ValidatorExceptionList
   */
  public function validateFields($fieldsData) {
    $fieldsData = (array) $fieldsData;
    $validated = $exceptions = array();
    
    foreach ($this->getFields() as $field) {
      try {
        
        $validated[$field] = $this->validate(
          $field,
          array_key_exists($field, $fieldsData) ? $fieldsData[$field] : NULL
        );
        
      } catch (ValidatorException $e) {
        $exceptions[] = $e;
      }
    }
    
    if (count($exceptions) > 0) {
      throw new ValidatorExceptionList($exceptions);
    }
    
    return $validated;
  }
  
  /**
   * Fügt einem Feld eine ValidationRule hinzu
   *
   * die Reihenfolge der Rules ist wichtig, denn die zweite Rule bekommt die Rückgabe der ersten Rule
   */
  public function addRule(ValidatorRule $rule, $field, $flags = 0x00000) {
    $key = $this->getKey($field);
    $this->rules[$key][] = $rule;
    
    if ($rule instanceof FieldValidatorRule) {
      $rule->setField($field);
    }
    
    if ($flags & self::OPTIONAL) {
      $this->setOptional($field);
    }
  }
  
  /**
   * @param Closure $validate mixed function ($data)
   */
  public function addClosureRule(\Closure $validate, $field, $flags = 0x000000) {
    $rule = new StandardValidatorRule();
    $rule->setCallback(new \Psc\Code\Callback($validate));
    
    $this->addRule($rule, $field, $flags);
    return $rule;
  }
  
  public function addSimpleRules(Array $fieldRules) {
    foreach ($fieldRules as $field => $ruleSpecification) {
      if ($ruleSpecification instanceof \Psc\Form\ValidatorRule) {
        $this->addRule($ruleSpecification, $field);  
      } else {
        $this->addRule(
                       \Psc\Form\StandardValidatorRule::generateRule($ruleSpecification),
                       $field
                      );
      }
    }
  }
  
  /**
   * Fügt allen Feldern eine bestimmte Rule zurück
   *
   * @param style bestimmt die Position der Rule (append oder prepend)
   */
  public function addRuleToAll(ValidatorRule $rule, $position = self::APPEND) {
    if ($position == self::APPEND) {
      foreach ($this->rules as $key => $NULL) {
        array_push($this->rules[$key],$rule);
      }
    }
    
    if ($position == self::PREPEND) {
      foreach ($this->rules as $key => $NULL) {
        array_unshift($this->rules[$key], $rule);
      }
    }
    
    if (is_numeric($position)) {
      foreach ($this->rules as $key => $NULL) {
        A::insert($this->rules[$key], $rule, (int) $position);
      }
    }
    
    throw new \Psc\Exception('unbekanter Parameter für position: '.Code::getInfo($position));
  }
  
  /**
   * @return bool
   */
  public function hasField($field) {
    $key = $this->getKey($field);
    
    return array_key_exists($key,$this->rules);
  }
  
  /**
   *
   * @return string
   */
  public function getLabel($field) {
    return $this->getKey($field);
  }
  
  /**
   * @return string
   */
  protected function getKey($field) {
    if (is_array($field))
      return implode(':',$field);
    else 
      return $field;
  }
  
  public function getFields() {
    $fields = array();
    foreach (array_keys($this->rules) as $field) {
      $fields[$this->getKey($field)] = $field;
    }
    return $fields;
  }
  
  public function setOptional($field, $value = TRUE) {
    $this->optionalFields[$this->getKey($field)] = $value;
    return $this;
  }
  
  public function isOptional($field) {
    $k = $this->getKey($field);
    return array_key_exists($k, $this->optionalFields) && $this->optionalFields[$k] == TRUE;
  }
}
?>