<?php

namespace Psc\Code\Test;

use Psc\JS\jQuery;

/**
 * Das Frontent für einen FormTest
 *
 * kann erstellt werden durch pures HTML einer form von <form> bis </form>
 */
class FormTesterFrontend extends \Psc\SimpleObject {
  
  /**
   * Die Felder des Frontends
   *
   * jeder Schlüssel ist der Name, kann auch [] enthalten
   * jeder Wert ist der Default Wert des Feldes
   */
  protected $fields;
  
  
  protected $action;
  
  
  protected $httpHeaders = array();
  
  public function __construct($action = NULL) {
    $this->action = $action;
    $this->fields = array();
  }
  
  public static function factory($html, $action = NULL) {
    $frontend = new FormTesterFrontend($action);
    $frontend->parseFrom($html);
    return $frontend;
  }
  
  /**
   * @param string $html das HTML von <form> bis </form> (alles außerhalb wird ignoriert)
   */
  public function parseFrom($html) {
    $form = new jQuery('form', $html);
    
    try {  
    
      foreach ($form->find("input") as $input) {
        $input = new jQuery($input);
        
        $name = $value = NULL;
        switch ($input->attr('type')) {
          case 'text':
          case 'password':
            $name = $input->attr('name');
            $value = $input->attr('value');
            break;
          
          case 'hidden':
            $name = $input->attr('name');
            $value = $input->attr('value');
            
            if ($input->hasClass('psc-cms-ui-http-header')) {
              $this->httpHeaders[$name] = $value;
            }
            break;
          
          case 'checkbox':
            $name = $input->attr('name');
            $value = $input->isChecked() ? $input->attr('value') : NULL;
            break;
          
          case 'radio':
            $name = $input->attr('name');
            if ($input->isChecked()) {
              $value = $input->attr('value');
            } elseif ($this->hasField($name)) {
              continue(2);
            } else {
              $value = NULL;
            }
            break;
          
          case 'button':
          case 'submit':
            continue(2);
            
        }
        if (!isset($name) || is_array($name)) {
          throw new \Psc\Exception('Feld: '.$input->export().' konnte nicht analysisiert werden');
        }
        
        $this->addField($name, $value);
      }
    
      foreach ($form->find("textarea") as $textarea) {
        $textarea = new jQuery($textarea);

        $this->addField($textarea->attr('name'), $textarea->html());
      }
      
      foreach ($form->find("select") as $select) {
        $select = new jQuery($select);
        
        $selected = $select->find('option[selected="selected"]');
        if ($selected->length) {
          $value = $selected->attr('value');
        } else {
          $value = $select->find('option:first-child')->attr('value'); // kann auch NULL sein
        }
        $this->addField($select->attr('name'),$value);
      }
      
      if (($action = $form->attr('action')) != '') {
        $this->action = $action;
      }
    
    } catch (\InvalidArgumentException $e) {
      throw new \Psc\Exception('Formular hat input mit leerem Namen als Feld: '.$input->export());
    }
    return $this;
  }
  
  /**
   * Gibt die Action zurück die das Frontend Angibt
   *
   * dies kann z. B. eine URL sein
   */
  public function getAction() {
    return $this->action;
  }
  
  public function getFields() {
    return $this->fields;
  }
  
  
  public function addField($name, $default = NULL) {
    if (mb_strlen($name) === 0) throw new \InvalidArgumentException('Feldname darf nicht leer sein');
    $this->fields[$name] = $default;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasField($name) {
    return array_key_exists($name,$this->fields);
  }
  
  public function removeField($name) {
    if ($this->hasField($name)) {
      unset($this->fields[$name]);
    }
    return $this;
  }
  
  /**
   * @param array $httpHeaders
   * @chainable
   */
  public function setHttpHeaders(Array $httpHeaders) {
    $this->httpHeaders = $httpHeaders;
    return $this;
  }

  /**
   * @return array
   */
  public function getHttpHeaders() {
    return $this->httpHeaders;
  }


}
?>