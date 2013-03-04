<?php

namespace Psc\System;

use Psc\Code\Event\Event;
use Psc\Code\Event\CallbackSubscriber;

abstract class AbstractLogger extends \Psc\Object { // wir könnten hier auch Dispatch implements einfügen, das soll aber das ableitene Objekt entscheiden
  
  protected $prefix;
  
  protected $manager;

  public function __construct(\Psc\Code\Event\Manager $manager = NULL) {
    $this->manager = $manager ?: new \Psc\Code\Event\Manager();
    $this->setUpListener();
  }
  
  public function setUpListener() {
  }
  
  protected function dispatch($functionName, $msg) {
    if ($functionName === 'write' || $functionName === 'br' || $functionName === 'writeln') {
      $this->manager->dispatchEvent('Logger.Written', array('msg'=>$msg,'type'=>$functionName), $this);
    } else {
      throw new \Psc\Exception('Kenne die Funktion: '.$functionName.' nicht für event-dispatching');
    }
  }
  
  public function writef($format) {
    $args = func_get_args();
    return $this->writeln(vsprintf($format, array_slice($args, 1)));
  }

  protected function prefixMessage($msg) {
    return \Webforge\Common\String::prefixLines($msg, $this->prefix, "\n");
  }

  public function getManager() {
    return $this->manager;
  }
  
  public function listenTo(DispatchingLogger $subLogger) {
    // damit wir nicht selbst der subscriber werden müssen (und das eine Menge verwirrung auslösen kann)
    // benutzen wir hier den Callbacksubscriber um "onSubloggerWritten" auszulösen
    
    $subscriber = new CallbackSubscriber($this,'onSubloggerWritten', array($subLogger));
    
    $subLogger->getManager()->bind($subscriber, 'Logger.Written');
    return $this;
  }
  
  public function onSubloggerWritten(DispatchingLogger $subLogger, Event $e) {
    switch ($e->getData()->type) {
      case 'writeln':
      case 'write':
        return $this->write($e->getData()->msg);
      case 'br':
        return $this->br();
    }
  }
  
  public function setPrefix($prefix = NULL) {
    $this->prefix = $prefix;
  }
}
?>