<?php

namespace Psc\CMS\Controller;

use \Psc\CMS\Ajax\Response,
    \Psc\Doctrine\Helper as DoctrineHelper
;


/**
 * Wird vom Ajax Controller aufgerufen
 *
 * ruft einen beliebigen Controller auf und gibt die Variablen des Ajax Controllers weiter
 *
 * $ajaxC = new AjaxController()
 * delegate(new DelegateController($ajaxC));
 *
 * get[ctrl] wird vom ajaxController extrahiert
 * auf die gesamte ajaxData kann mit x() zugegriffen werden
 * die globalen g() und p() sind die wie vom ajaxController übernommen (mach beachte, dass hier todo nicht unser todo ist!)
 */
class DelegateController extends BaseFileController implements ResponseController, \Psc\Form\ValidatorDataProvider { // wir brauchen hier kein GPC, weil wir uns alles aus dem AjaxController holen
  
  /**
   * @var AjaxController
   */
  protected $ajaxC = NULL;
  
  /**
   * @var \Psc\CMS\Ajax\Response
   */
  protected $response;
  
  public function __construct(AjaxController $ctrl = NULL) {
    $this->ajaxC = $ctrl;
    parent::__construct('undefined');
  }
  
  public function init($mode = Controller::MODE_NORMAL) {
    parent::init($mode);
    if ($mode & Controller::INIT_AJAX_CONTROLLER) $this->initAjaxController();
    
    return $this;
  }
  
  public function initAjaxController() {
    $this->vars['get'] = $this->ajaxC->getGet(); // hehe
    $this->vars['post'] = $this->ajaxC->getPost();
    $this->vars['ajaxData'] = $this->ajaxC->getAjaxData();
    
    // $todo setzt der AjaxController selbst für uns (hat er vermutlich schon)
    $this->assertTodo();
    
    $this->name = $this->g(array('ctrl'));
  }
  
  public function run() {
    if ($this->name == 'undefined') {
      throw new SystemException('run() kann nicht mit Namen: "undefined" aufgerufen werden (wurde initAjaxController oder vergleichbares aufgerufen?');
    }
    
    $this->vars['ajaxTodo'] = $this->getTodo();
    
    // ruft die Datei des Items auf
    parent::run();
  }

  // gpx und xpg sind etwas anders als gewohnt, da
  // diese auch den default zurückgeben wenn der wert NULL ist
  
  public function g($keys, $do = self::RETURN_NULL) {
    return $this->vars['get']->get($keys,$do);
  }

  public function p($keys, $do = self::RETURN_NULL) {
    return $this->vars['post']->get($keys,$do);
  }
  
  public function x($keys, $do = self::RETURN_NULL) {
    return $this->vars['ajaxData']->get($keys,$do);
  }
  
  public function xpg($keys,$do = self::RETURN_NULL) {
    return $this->x($keys,$this->p($keys,$this->g($keys,$do)));
  }
  
  /**
   * @return Response
   */
  public function getResponse() {
    return $this->response;
  }
  
  /**
   * @param Response $response
   */
  public function setResponse(Response $response) {
    $this->response = $response;
    return $this;
  }
  
  /* BEGIN INTERFACE \Psc\Form ValidatorDataProvider */
  /**
   * Reihenfolge: xpg
   *
   * gibt NULL zurück wenn der Schlüssel nicht gesetzt ist (alias keine Exceptions)
   */
  public function getValidatorData($keys) {
    return $this->xpg($keys,self::RETURN_NULL);
  }
  /* END INTERFACE \Psc\Form ValidatorDataProvider */
}
?>