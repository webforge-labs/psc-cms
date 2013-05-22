<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Command\Command AS SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;

class Command extends \Symfony\Component\Console\Command\Command {
  
  const MUST_EXIST = 0x000001;
  const RESOLVE_RELATIVE = 0x000002;
  
  const VALUE_NONE     = InputOption::VALUE_NONE;
  const VALUE_REQUIRED = InputOption::VALUE_REQUIRED;
  const VALUE_OPTIONAL = InputOption::VALUE_OPTIONAL;
  const VALUE_IS_ARRAY = InputOption::VALUE_IS_ARRAY;

  const REQUIRED = InputArgument::REQUIRED;
  const OPTIONAL = InputArgument::OPTIONAL;
  const IS_ARRAY = InputArgument::IS_ARRAY;
  
  protected $messages = array();
  protected $msgPrefix;
  
  protected $execOutput;
  protected $execInput;
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->execInput = $input;
    $this->execOutput = $output;

    try {
      //print 'Excecute: '.get_called_class().' ';
      return $this->doExecute($input, $output);
    } catch (CommandExitException $e) {
      $this->getApplication()->renderException($e, $output);
      return max(1,$e->getCode());
    }
  }
  
  protected function doExecute($input, $output) {  
  }
  
  
  public function warn($msg) {
    $this->out('<error>'.$msg.'</error>');
  }

  public function info($msg) {
    $this->out('<info>'.$msg.'</info>');
  }

  public function comment($msg) {
    $this->out('<comment>'.$msg.'</comment>');
  }
  
  public function br() {
    $this->out('');
  }
  
  public function out($msg) {
    $this->execOutput->writeln($this->msgPrefix.$msg);
  }
  
  public function ask($question, $default = NULL) {
    $dialog = $this->getHelper('dialog');
    return $dialog->ask($this->execOutput,
                        rtrim($question, ' ').' ',
                        $default
                       );
  }

  public function askDefault($question, $default) {
    return $this->ask(sprintf($question.' (default %s): ', $default), $default);
  }
  
  public function confirm($question, $default = TRUE) {
    $dialog = $this->getHelper('dialog');
    return $dialog->askConfirmation($this->execOutput,
                                    $question.' ',
                                    $default
                                   );
  }
  
  public function askAndValidate($question, \Closure $validator, $attempts = FALSE) {
    $dialog = $this->getHelper('dialog');
    return $dialog->askAndValidate($this->execOutput,
                                   $question,
                                   $validator,
                                   $attempts
                                  );
  }
  
  public function getDialogMessage($messageName, Array $params = array()) {
    return vsprintf('<question>'.$this->messages[$messageName].'</question>',
                    $params);
  }
  
  public function writeMessage($messageName, Array $params = array()) {
    return $this->execOutput->writeln($this->getDialogmessage($messageName, $params));
  }
  
  public function hasHelper($helperName) {
    return $this->getApplication()->getHelperSet()->has($helperName);
  }
  
  public function callCommand($name, Array $args, $output) {
    $command = $this->getApplication()->find($name);
    $arguments = array_merge(array('command'=>$name), $args);

    $input = new ArrayInput($arguments);
    return $command->run($input, $output ?: $this->execOutput);
  }
  
  public function getProject() {
    return $this->getHelper('project')->getProject();
  }

  public function getPackage() {
    return $GLOBALS['env']['container']->webforge->getLocalPackage();
  }

  public function getDoctrineModule() {
    return $this->getHelper('dc')->unwrap()->getModule();
  }
  
  public function validateDirectory($path, $flags = self::MUST_EXIST) {
    $errorDetail = NULL;
    if (!empty($path)) {
      $dir = Dir::factoryTS($path);

      // workaround file
      if (self::RESOLVE_RELATIVE && !Dir::isAbsolutePath((string) $dir) && !$dir->isRelative()) {
        $dir = new Dir('.'.DIRECTORY_SEPARATOR.$dir);
      }

      $dir->resolvePath();
    
      if (!($flags & self::MUST_EXIST) || $dir->exists()) {
        
        return $dir;
      
      } else {
        $errorDetail = ' It must exist!';
      }
    }
    
    throw $this->exitException(sprintf("Directory from path: '%s' cannot be found.%s", $path, $errorDetail));
  }

  public function validateFile($path, $flags = self::MUST_EXIST) {
    $errorDetail = NULL;
    if (!empty($path)) {
      $file = File::factory($path);
      
      // workaround file
      if (self::RESOLVE_RELATIVE && !Dir::isAbsolutePath((string) $file->getDirectory()) && !$file->isRelative()) {
        $file->setDirectory(new Dir('.'.DIRECTORY_SEPARATOR.$file->getDirectory()));
      }
      
      $file->resolvePath();
    
      if (!($flags & self::MUST_EXIST) || $file->exists()) {
        return $file;
      
      } else {
        $errorDetail = ' It must exist!';
      }
    }
    
    throw $this->exitException(sprintf("File from path: '%s' cannot be found.%s", $path, $errorDetail));
  }
  
  public function validateEnum($value, Array $allowedValues) {
    if (!in_array($value, $allowedValues)) {
      throw exitException("the value '%s' is not allowed. Allowed are only: %s", implode(',', $allowedValues));
    }
    return $value;
  }


  public function validateOptionalString($value) {
    $value = (string) $value;
    if (trim($value) === "") {
      return NULL;
    }
    return $value;
  }
  
  public function exitException($msg, $code = 1, \Exception $previous = NULL) {
    return new CommandExitException($msg, $code, $previous);
  }
}
