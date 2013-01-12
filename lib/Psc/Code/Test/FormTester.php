<?php

namespace Psc\Code\Test;

use Psc\Doctrine\EntityDataRow;
use Doctrine\ORM\EntityManager;
use Psc\URL\Request;

class FormTester extends \Psc\Object {
  
  /**
   * @var FormTesterData
   */
  protected $data;
  
  /**
   * @var FormTesterFrontend
   */
  protected $form;
  
  /**
   * @var Psc\URL\Request
   */
  protected $request;
  
  /**
   * die mappings helfen uns ein Feld aus der EntiyDataRow zu einem Formularfeld zu mappen
   */
  protected $mappings = array();
  
  /**
   * @var FormTesterHydrator
   */
  protected $hydrator;
  
  public function __construct(FormTesterFrontend $form, Request $curl) {
    $this->form = $form;
    $this->request = $curl;
  }
  
  /**
   * Füllt die Form anhand der übergebenen Daten aus und bereitet den Request vor
   *
   * @param FormTesterData $data die Daten, die in das FormTesterFrontend (Formular) eingefügt werden sollen
   */
  public function prepareRequest(FormTesterData $data) {
    $this->data = $data;
    
    /*
      Alle Felder die im Formular vorkommen, müssen auch in den Request gelangen
      wir "füllen" allerdings die Felder aus, die in der EntityDataRow vorkommen
    */
    if ($this->request->getURL() == NULL) {
      $this->request->setURL($this->form->getAction());
    }
    $this->request->setPost(array());
    
    $data = array();
    foreach ($this->form->getFields() as $formField => $defaultValue) {
      
      if ($this->hasMapping($formField)) {
        $property = $this->mappings[$formField];
      } else {
        $property = $formField;
      }
      
      if ($this->data->getFormRow()->has($property)) {
        $formValue = $this->data->getFormValue($property);
      } else {
        $formValue = $defaultValue;
      }
      
      $data[$formField] = $formValue;
      
    }
    $data = array_merge((array) $this->request->getData(), $data);
    $this->request->setData($data);
    
    return $this;
  }

  /**
   * Führt den Request aus und gibt das Ergebnis als Response zurück
   *
   * @return Response
   */
  public function run() {
    $this->request->init()->process();
    
    return $this->request->getResponse();
  }
  
  public function assert(FormTesterData $expectedData) {
    
  }
  
  /**
    * Fügt ein Mapping von einem Feld in der Form zu einem Property in der Data
    *
    * Mappings müssen nur hinzugefügt werden, wenn der name des Feldes nicht der Name in Data ist
   */
  public function map($formField, $rowProperty) {
    $this->mappings[$formField] = $rowProperty;
    return $this;
  }
  
  public function hasMapping($formField) {
    return array_key_exists($formField, $this->mappings);
  }
  
  public function getRequest() {
    return $this->request;
  }
}
?>