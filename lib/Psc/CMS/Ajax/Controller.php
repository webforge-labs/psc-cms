<?php

namespace Psc\CMS\Ajax;

use \Psc\CMS\Controller\AjaxController,
    \Psc\Form\DataInput,
    \Psc\CMS\Controller\TabsContentItemController,
    \Psc\TPL\Template,
    \Psc\TPL\TPL,
    \Psc\GPC,
    \Psc\CMS\HTMLPage,
    \Psc\Code\Code,
    \Psc\PSC,
    
    \Psc\Exception,
    
    \Psc\CMS\Ajax\Response,
    \Psc\CMS\Ajax\ExceptionResponse
  ;

/**
 * Das ist etwas verwirrend hier
 *
 * dies ist aber der tolle AjaxController den man in ajax.php einbindet
 */
class Controller extends \Psc\CMS\Controller\AjaxController {

  public function __construct($name = 'general') {
    parent::__construct($name);
    
    $this->addTodos(array('tabs','tpl','ctrl'));
  }
  
  public function run() {
    
    /* die tabs. Funktionen Arbeiten mit TabsContentItem - Objekten */
    if ($this->getTodo() == 'tabs') {
      $itemC = new TabsContentItemController($this);
    
      $this->delegate($itemC);
      parent::run();
    }
  
    if ($this->getTodo() == 'ctrl') {
      $itemC = new TabsContentItemController($this); // ist eher so ein Entity-Item-Controller?
      $ctrlTodo = $this->g('ctrlTodo',AjaxController::THROW_EXCEPTION);
    
      // wir geben das ctrlTodo direkt weiter ohne es zu prefixen
      $this->setDelegateTodo($ctrlTodo);
      
      $this->delegate($itemC);
      parent::run();
    }
    
    if ($this->getTodo() == 'tpl') { // ein dummes Template anzeigen
      $template = new Template($this->g('tpl',DataInput::THROW_EXCEPTION));
      $template->setVars((array) $this->g('vars',DataInput::RETURN_NULL));
      
      $this->response = TPL::getResponse($template);
    }
    
    $response = $this->getResponse();
    
    if ($response instanceof Response) {
      if ($response instanceof ExceptionResponse && ($resEx = $response->getException()) != NULL) {
        PSC::getEnvironment()->getErrorHandler()->handleCaughtException($resEx);
      }
    
      header('Pragma: no-cache');
      header('Cache-Control: private, no-cache');
      header('Content-Disposition: inline; filename="files.json"');
      header('X-Content-Type-Options: nosniff');
      header('Vary: Accept');
      
      if (GPC::GET('debug') == 'true') {
        Header('Content-Type: text/html');
        $p = new HTMLPage();
        $p->body->content = $response->export();
        print $p;
      } else {
        Header('Content-Type: '.$response->getContentType());
        
        print $response->export();
      }
      
    } else {
      throw new Exception('Keine Response zum ausgeben '.'<pre>'.Code::varInfo($_GET).'</pre>');
    }
  }
}

?>