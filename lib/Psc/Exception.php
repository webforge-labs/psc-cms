<?php

namespace Psc;

use stdClass;
use Webforge\Types\Type;
use InvalidArgumentException;
use Psc\Code\Code;

class Exception extends \Webforge\Common\Exception {
  
  // Exception Helpers:
  /**
   *
   * @param integer $num 1-basiserend
   * @param string $context Class::Method()|Method()|something parseable
   * @param mixed $actual
   * @param Psc\Data\Type\Type $expected
   * @return InvalidArgumentException
   */
  public static function invalidArgument($num, $context, $actual, Type $expected) {
    return new InvalidArgumentException(
      sprintf('Argument %d passed to %s must be a %s, %s given', $num, $context, $expected->getName(Type::CONTEXT_DEBUG), Code::varInfo($actual))
    );
  }
  
  // Exception Helpers  
  
  public function getClass() {
    return get_class($this);
  }
  
  public function setCode($code) {
    $this->code = $code;
    return $this;
  }
  
  public function setPrevious() {
    throw new \Psc\Exception('Es ist nicht möglich previous zu benutzen. Bitte Exception::build() benutzen um previous fluent hinzuzufügen, oder im Constructor übergeben');
  }
  
  /**
   *
   * nach $formattedMessage können beliebig viele sprintf-Parameter für $formattedMessage angegeben werden
   * @param mixed $messageParam, ...
   * @return static()
   */
  public static function create($formattedMessage, $messageParam = NULL) {
    $builder = new \Psc\Code\ExceptionBuilder(get_called_class(), $formattedMessage, array_slice(func_get_args(),1));
    return $builder->end();
  }

  /**
   *
   * nach $formattedMessage können beliebig viele sprintf-Parameter für $formattedMessage angegeben werden
   * @param mixed $messageParam, ...
   * @return Psc\Code\ExceptionBuilder
   */
  public static function build($formattedMessage, $messageParam) {
    return new \Psc\Code\ExceptionBuilder(get_called_class(), $formattedMessage, array_slice(func_get_args(),1));
  }
  
  /*
   public function __construct ($message = "", $code = 0, Exception $previous = NULL) {
     parent::__construct($message,$code,$previous);
   }
  */
  
  public static function handler (\Exception $e) {
    try {
      $project = PSC::getProject();
    } catch (\Exception $noProjet) {
      $project = NULL;
    }
    
    $format = PHP_SAPI === 'cli' ? 'text' : 'html';
    
    $text = self::getExceptionText($e, $format, $project);
    
    print $text;
    
    PSC::getEnvironment()->getErrorHandler()->handleCaughtException($e);
  }
  
  
  public static function getExceptionText(\Exception $e, $format = 'html', $project = NULL, $label = 'ROOT') { // webforge-common extends much cooler, don't type $project here
    $cr = $format == 'html' ? "<br />" : "\n";

    if (isset($project)) {
      // versuche die Pfade übersichtlich zu machen
      $root = $project->getRootDirectory();
      $trace = str_replace(array((string) $root,"\n"),
                           array('{'.$label.'}'.DIRECTORY_SEPARATOR, $cr),
                           $e->getTraceAsString());
      $file = str_replace(array((string) $root),
                          array('{'.$label.'}'.DIRECTORY_SEPARATOR),
                          $e->getFile());
    } else {
      $trace = $e->getTraceAsString();
      $file = $e->getFile();
    }
    
    $text = NULL;
    if ($format == 'html') {
      $text = '<pre class="php-error">'."\n";
      $text .= $cr.'<b>Fatal Error:</b> ';
    }
    
    $text .= 'Uncaught exception \''.get_class($e).'\' with message:'.$cr;
    if ($e instanceof \Psc\Code\ExceptionExportable) {
      $text .= str_replace("\n",$cr, wordwrap($e->exportExceptionText(),140,"\n") ).$cr;
    } else {
      $text .= "'".str_replace("\n",$cr, wordwrap($e->getMessage(),140,"\n") )."'".$cr;
    }
    
    $text .= 'in '.$file.':'.$e->getLine().$cr;
    $text .= 'StackTrace: '.$cr.$trace.$cr;

    if ($format == 'html') {
      $text .= 'in <b>'.$file.':'.$e->getLine().'</b>'.'</pre>';
    } else {
      $text .= 'in '.$file.':'.$e->getLine();
    }
    
    if ($e->getPrevious() instanceof \Exception) {
      $text .= $cr.'Previous Exception:'.$cr;
      $text .= self::getExceptionText($e->getPrevious(), $format).$cr;
    }
    
    return $text;
  }
  
  public static function registerHandler() {
    set_exception_handler(array('\Psc\Exception','handler'));
  }
}
