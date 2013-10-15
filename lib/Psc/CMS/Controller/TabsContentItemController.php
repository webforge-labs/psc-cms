<?php

namespace Psc\CMS\Controller;

use 
    \Psc\Doctrine\Helper as DoctrineHelper,
    
    \Psc\CMS\TabsContentItem,

    \Psc\CMS\Ajax\Response,
    \Psc\CMS\Ajax\TabsContentItemInfoResponse,
    \Psc\CMS\Ajax\TabsContentItemButtonResponse,
    \Psc\CMS\Ajax\AutoCompleteResponse,
    \Psc\CMS\Ajax\StandardResponse,
    \Psc\CMS\Ajax\ExceptionResponse,
    \Psc\CMS\Ajax\ValidationResponse,
    
    \Psc\Form\Validator,
    \Psc\Form\ValidatorException,
    \Psc\Form\DoctrineEntityValidatorRule,
    \Psc\Form\ValuesValidatorRule,

    \Psc\ExceptionDelegator,
    \Psc\ExceptionListener,
    
    \Psc\Code\Code,
    \Psc\PSC,
    
    \Psc\TPL\TPL,
    \Psc\TPL\Template
;

/**
 * Wird vom Ajax Controller aufgerufen
 *
 * ruft einen beliebigen Controller auf und gibt die Variablen des Ajax Controllers weiter
 *
 * $ajaxC = new AjaxController()
 * delegate(new DelegateController($ajaxC));
 *
 * ajaxData[type] und ajaxData[identifier] werden vom ajaxController extrahiert
 * auf die gesamte ajaxData kann mit x() zugegriffen werden
 * die globalen g() und p() sind die wie vom ajaxController übernommen (mach beachte, dass hier todo nicht unser todo ist!)
 *
 *
 * im Controller kann dann sowas wie
 *
 * if ($this->todo == 'save') {
 *   $this->response = 'bla bla bla';
 * }
 *
 * gemacht werden.
 *
 * Wenn response nicht gesetzt ist, werden in diesem Controller Hilfs-Funktionen aufgerufen, die als Standardfunktionen die Schreibarbeit erleichtern sollen. Dies ist ein bißchen so wie extenden, denn wenn man einfach die Funktion selbst ausführen will, fängt man das todo ab.
 * Alle Hilfsfunktionen laufen nach dem run() des controllers (also der ctrl.item.php datei) und laufen nur dann wen $this->response nicht gesetzt ist
 *
 * - Führt ein Try {} Catch {} für exception => ExceptionResponse und ValidationException => ValidationResponse aus sofern man nicht
 *   option: dontCatch auf TRUE setzt
 *
 */
class TabsContentItemController extends DelegateController implements \Psc\ExceptionDelegator { // wir brauchen hier kein GPC, weil wir uns alles aus dem AjaxController holen
  
  /**
   * Doctrine\ORM\EntityManager
   */
  protected $em;
  protected $entityName;
  
  protected $fv;
  
  protected $identifier;
  protected $type;
  protected $itemData;
  
  protected $exceptionListeners = array();
  
  protected $debug = array(); // siehe log()
  
  protected $main;
  
  public function __construct(AjaxController $ctrl = NULL) {
    if (\Psc\PSC::getProject()->isDevelopment()) {
      throw new \Webforge\Common\DeprecatedException('Dont use this anymore');
    }

    $this->ajaxC = $ctrl;
    parent::__construct($ctrl);
    
    $this->em = DoctrineHelper::em();
  }
  
  public function initAjaxController() {
    parent::initAjaxController();
    
    // den namen müssen wir selbst setzen
    $this->name = $this->type = $this->x(array('type'),self::THROW_EXCEPTION);
    $this->identifier = $this->x(array('identifier'),self::RETURN_NULL);
    $this->itemData = $this->xpg(array('data'),array());
    
    if (count($this->itemData) == 0 && ($jsonData = $this->xpg(array('dataJSON'),self::RETURN_NULL)) != NULL) {
      $this->itemData = json_decode($jsonData, TRUE); // assoc
      // auch "normal" verfügbar machen
      $this->vars['ajaxData']->setDataWithKeys(array('data'), $this->itemData);
    }
    
    $this->entityName = DoctrineHelper::getEntityName($this->type);
  }
  
  public function run() {
    
    if ($this->getOption('dontCatch',FALSE) == TRUE) {
      /* ohne Catchen */
      
      parent ::run();
    
    /* mit Catchen */
    } else {

      $connection = $this->em->getConnection();
      
      try {
        try {
          
          parent::run();
          
          /* PDO Exceptions */
          } catch (\PDOException  $e) {
            throw \Psc\Doctrine\Exception::convertPDOException($e);
          }
          

      /* Validation */    
      } catch (\Psc\Form\ValidatorException $e) {
        if ($connection->isTransactionActive()) {
          $connection->rollback();
        }
        $this->em->close();
     
        $this->response = new ValidationResponse($e);

      /* alle Anderen */    
      } catch (\Exception $e) {
        if ($connection->isTransactionActive()) {
          $connection->rollback();
        }
        $this->em->close();
        
        $this->response = new ExceptionResponse($e);
        $exceptionClass = Code::getClass($e);
        
        if (array_key_exists($exceptionClass,$this->exceptionListeners)) {
          foreach ($this->exceptionListeners[$exceptionClass] as $listener) {
            if (($le = $listener->listenException($e)) != NULL) {
              
              if ($le instanceof ValidatorException) {
                $this->response = new ValidationResponse($le);
              } else {
                $this->response = new ExceptionResponse($le);
              }
            }
          }
        }
      }
    }
  }
  
  public function getItem($do = self::THROW_EXCEPTION) {
    if (!isset($this->identifier)) {
      throw new NoIdException('Kann das Item nicht zurückgeben, wenn identifier leer ist');
    }
    
    if (!isset($this->type)) {
      throw new NoObjectException('Kann das Item nicht zurückgeben, wenn type nicht gesetzt ist');
    }
    
    if (!isset($this->entityName)) {
      throw new NoObjectException('Kann das Item nicht zurückgeben, wenn der EntityName nicht ermittelt werden konnte');
    }
    
    $item = $this->em->find($this->entityName,$this->identifier);
    
    if ($item instanceof \Psc\CMS\TabsContentItem) {
      $item->setTabsData(Code::castArray($this->itemData));
    } else {
      if ($do === self::THROW_EXCEPTION) {
        throw new NoIdException('Entity ist kein TabsContentItem. Find gab null zurück: '.Code::varInfo(array($this->entityName, $this->identifier)).'. Item kann nicht zurückgegeben werden');
      }
      
      return $do;
    }
    
    return $item;
  }


  /**
   * $spec['entities'] = array(
   *  list($entityType|$entityName, $idName)|$entityType
   * )
   *
   * $spec['types'] = array(
   *  String $fullQualifiedTypeClassName
   * )
   */
  public function getDefaultValidator(Array $spec = array()) {
    
    $this->fv = new Validator();
    
    /* @TODO hier könnten wir irgendwann mal die Meta Daten vom Entity auslesen und schonmal die Felder setzen!
      obergeil!!!
    */
    if (array_key_exists('entities',$spec)) {
      foreach ($spec['entities'] as $entity) {
        
        if (is_array($entity)) {
          list($entityType,$idName) = $entity;
        } else {
          $entityType = $entity;
          $idName = 'Id';
        }
        
        $field = $entityType.$idName;
        $this->fv->addRule(new DoctrineEntityValidatorRule($entityType),$field);
        $this->fv->addRule(new DoctrineEntityValidatorRule($entityType),array('data',$field));
      }
      unset($spec['entities']);
    }
    
    if (array_key_exists('types',$spec)) {
      foreach ($spec['types'] as $fullClassName) {
        $field = lcfirst(Code::getClassName($fullClassName)); // sowas wie \tiptoi\SpeakerType => speakerType
        $values = $fullClassName::instance()->getValues();
        
        $this->fv->addRule(new ValuesValidatorRule($values),$field);
      }
      unset($spec['types']);
    }
    
    $this->fv->addSimpleRules(array(
        'label'=>'nes',
        'term'=>'nes', // ac
        'ac_length'=>'pi' // ac
    ));
    
    $this->fv->addSimpleRules($spec); // die restlichen einträge
    
    $this->fv->setOptional('ac_length');
    
    $this->fv->setDataProvider($this);
    
    return $this->fv;
  }

  
  protected function doTabsButton() {
    $this->response = new TabsContentItemButtonResponse($this->getItem());
  }
  
  protected function doTabsSearch() {
  }
  
  protected function doTabsInfo() {
    $this->response = new TabsContentItemInfoResponse($this->getItem());
  }
  
  protected function doTabsContent() {
    $vars = array();
    $vars[$this->name] = $this->getItem();
    $vars['controller'] = $this;
    
    $this->setTemplateResponse(array(PSC::getProject()->getLowerName(), $this->name.'.form'), $vars);
  }
  
  protected function doTabsDelete() {
    $this->em->getConnection()->beginTransaction();
    DoctrineHelper::enableSQLLogging();

    $item = $this->getItem(self::THROW_EXCEPTION);
    $item->remove();
    
    $this->em->flush();
    $this->em->commit();
    
    $this->response = new StandardResponse(Response::STATUS_OK);
    $this->response->getData()->sql = DoctrineHelper::printSQLLog('/^(INSERT INTO|DELETE|UPDATE)/',TRUE);
    $this->response->getData()->id = $item->getIdentifier();
    $this->response->getData()->tab = $c = new \stdClass();
      $c->close = true;
  }
  
  
  protected function doAutoComplete($term = NULL) {
    /* werden abgefragt im repository bei autoCompleteTerm */
    $fields = $this->getOption('ac.fields', array('id','label'));

    // es ist total wichtig, dass wir alle daten, die wir im labelhook haben, tatsächlich auch im autoCompleteTerm() abfragen
    // denn sonst hat man den Effekt: "hä, warum findet der das nicht, es steht doch da"
    $labelHook = $this->getOption('ac.labelHook', function ($item) {
      $label = '[#'.$item->getIdentifier().'] '.(string) $item;
      return $label;
    });
    
    $rep = $this->getRepository();
    
    if (!isset($term)) {
      $items = $rep->findAll();
    } else {
      $items = $rep->autoCompleteTerm($fields,$term);
    }

    return new AutoCompleteResponse($items, $labelHook);
  }
  
  protected function runStandardTodoActions() {
    switch ($this->todo) {
      case 'tabs.content.button':
        $this->doTabsButton();
        break;

      case 'tabs.content.search':
        $this->doTabsSearch();
        break;

      case 'tabs.content.info':
        $this->doTabsInfo();
        break;
      
      case 'tabs.content.data':
        $this->doTabsContent();
        break;
      
      case 'tabs.content.delete':
        $this->doTabsDelete();
        break;
    }
  }
  
  /* INTERFACE ExceptionDelegator */
  /**
   *   Der ExceptionListener sollte in listenException eine Exception zurückgeben, wenn er will, dass diese umgewandelt wird
   *   im Moment werden nur ValidatorExceptions separat behandelt (alle anderen werden in ExceptionResponse umgewandelt)
   *   gibt er NULL zurück, passiert nichts
   *
   *   spätere Listener, die auch etwas zurückgeben überschreiben die vorigen 
   */
  public function subscribeException($exception, ExceptionListener $listener) {
    $this->exceptionListeners[$exception][] = $listener;
  }
  /* END INTERFACE ExceptionDelegator */
  
  
  public function trigger($e) {
    
    if ($e == 'run.after' && $this->response == NULL) {
      $this->runStandardTodoActions();
    }
    
    parent::trigger($e); // bubble
  }


  public function getRepository() {
    //if (!isset($this->entityName)) $this->entityName = DoctrineHelper::getEntityName($this->type);
    return $this->em->getRepository($this->entityName);
  }
  
  /**
   * Setzt die Response mit einem Template
   *
   * @param array|string $tpl wird an new Template() übergeben
   * @param array $vars für das Template
   * @return Template
   */
  public function setTemplateResponse($tpl, Array $vars) {
    $template = new Template($tpl);
    $template->setVars($vars);

    $this->response = TPL::getResponse($template);
    return $template;
  }
  
  /**
   * @return Response->getData()
   */
  public function setStandardResponse() {
    $this->response = new StandardResponse(Response::STATUS_OK, Response::CONTENT_TYPE_JSON);
    
    $data = $this->response->getData();
    $data->debug = $this->debug;
    
    return $data;
  }

  /**
   * @return Response
   */
  public function setHTMLResponse($html) {
    $this->response = new StandardResponse(Response::STATUS_OK, Response::CONTENT_TYPE_HTML);
    $this->response->setContent($html);
    return $this->response;
  }
  
  public function log($msg) {
    $this->debug[] = $msg;
  }
  
  /**
   * @return \Psc\UI\TCIControllerCallTabsItem
   */
  public static function call(TabsContentItem $item, $todo, Array $data = array()) {
    return new \Psc\UI\TCIControllerCallTabsItem($item, $todo, $data);
  }
  
}
?>