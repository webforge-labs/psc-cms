<?php

namespace Psc\Net;

use Psc\Net\HTTP\HTTPException;
use Psc\Code\Code;
use Psc\Form\ValidatorException;
use Psc\Net\HTTP\HTTPResponseException;
use Psc\CMS\Service\MetadataGenerator;

/**
 * Ein kleines Package für die Abstraktion von ein paar Errors die in jedem Controller / Service auftreten können
 *
 */
class ServiceErrorPackage extends \Psc\SimpleObject {
  
  public $standardMessage = "Feld '%s' konnte nicht validiert werden. %s";
  
  protected $controller;
  
  // dont hint this, controller can also be a requesthandler
  public function __construct($controller) {
    $this->controller = $controller;
  }
  
  /**
   * FEHLER: Ein Parameter oder eine Subresource einer Methode ist nicht korrekt gewesen
   *
   * die Syntax des Requests ist falsch
   * @param string $method
   * @param string $argument
   */
  public function invalidArgument($method, $argumentName, $argumentValue, $msg = '') {
    return HTTPException::BadRequest('Falscher Parameter '.Code::varInfo($argumentValue).' für '.$method.'( :$'.$argumentName." )\n".$msg.' in Controller: '.Code::getClass($this->controller));
  }
  
  
  public function validationError($field, $data, \Exception $e) {
    $fex = new ValidatorException(sprintf($this->standardMessage,
                                          $field, $e->getMessage(), $e->getCode())
                                          ,$e->getCode()
                                          ,$e
                                  );
    $fex->field = $field;
    $fex->data = $data;
    return $fex;
  }
  
  
  /**
   * Throws an ValidationResponse
   *
   * it handles the JSON case and the HTML case
   * @param ValidatorException[]
   * @param const $format im moment nur JSON oder HTML
   * @return HTTPException
   */
  public function validationResponse(\Psc\Form\ValidatorExceptionList $list, $format = ServiceResponse::JSON, MetadataGenerator $metadataGenerator = NULL) {
    if ($format === ServiceResponse::JSON) {
      $body = (object) array('errors'=>array());
    
      foreach ($list->getExceptions() as $validatorException) {
        $body->errors[$validatorException->field] = $validatorException->getMessage();
      }
    
      return HTTPResponseException::BadRequest(json_encode($body), NULL, array('Content-Type'=>'application/json'));
    } else {
      $metadataGenerator = $metadataGenerator ?: new MetadataGenerator();
      
      return HTTPException::BadRequest(
          $list->getMessage(),
          $list,
          array_merge(
            array('Content-Type'=>'text/html'),
            $metadataGenerator->validationList($list)->toHeaders()
          )
      );
    }
  }
  
  /**
   * FEHLER: Eine Resource die zum ausführen des Requests benötigt wird, wurde niicht gefunden
   *
   * z. B. Ein PUT Request wird für ein Entity gemacht welches nicht mehr vorhanden ist
   * für ein Entity soll ein Formular geladen werden, und der Identifier ergibt kein ergebnis in der Datenbank
   */
  public function resourceNotFound($method, $resourceType, $argumentValues, \Exception $e = NULL) {
    return HTTPException::NotFound(
      sprintf("Die Resource '%s' konnte nicht mit den Parametern %s gefunden werden. Der Request konnte nicht vollständig ausgeführt werden. Möglicherweise ist die Resource nicht mehr vorhanden.", $resourceType, Code::varInfo($argumentValues)),
      $e
    );
  }
}
?>