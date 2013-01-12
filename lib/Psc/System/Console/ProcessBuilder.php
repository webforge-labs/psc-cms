<?php

namespace Psc\System\Console;

use Webforge\Common\System\File;

class ProcessBuilder {
  
  const UNIX = 'unix';
  const WINDOWS = 'windows';
  
  protected $bin;
  
  /**
   * All Arguments imploded with ' ' after the command line
   *
   * @var array these are already fully escaped
   */
  protected $args = array();
  
  protected $cwd;
  protected $env;
  protected $stdin;
  protected $timeout;
  protected $options;
  protected $inheritEnv;
  
  /**
   * For Which operating system should be the args escaped?
   *
   * (defaults to the current one, run on)
   * @var const self::UNIX|self::WINDOWS
   */
  protected $escapeFor;

  public function __construct(File $bin, array $cmdArgs = array(), array $cmdOptions = array(), $escapeFor = NULL) {
    $this->escapeFor($escapeFor ?: substr(PHP_OS, 0, 3) == 'WIN' ? self::WINDOWS : self::UNIX);
    $this->bin = $bin;
    
    foreach ($cmdArgs as $arg) {
      $this->addArgument($arg);
    }
    
    foreach ($cmdOptions as $key => $value) {
      if (is_numeric($key)) {
        $this->addOption($value);
      } else {
        $this->addOption($key, $value);
      }
    }
    
    $this->timeout = 60;
    $this->options = array();
    $this->env = array();
    $this->inheritEnv = true;
  }

  public static function create(File $bin, array $cmdArgs = array(), array $cmdOptions = array(), $escapeFor = NULL) {
    return new static($bin, $cmdArgs, $cmdOptions, $escapeFor);
  }
  
  public function escapeFor($type) {
    \Psc\Code\Code::value($type, self::WINDOWS, self::UNIX);
    
    if ($this->escapeFor != NULL && $type != $this->escapeFor && count($this->args) > 0) {
      throw new \RuntimeException('You switched to another escapeFor Mode, but you have already added arguments (through constructor?). Use escapeFor as last Argument from create/construct or as first chain-command');
    }
    $this->escapeFor = $type;
    
    return $this;
  }

  /**
   * Adds an unescaped argument to the command string.
   *
   * @param string $argument A command argument, will be escaped for process
   */
  public function addArgument($argument) {
    $this->args[] = $this->escapeShellArg($argument);
    return $this;
  }
  
  public function addOption($optionName, $value = NULL) {
    if (isset($value)) {
      $this->args[] = sprintf('--%s=%s', $optionName, $this->escapeShellArg($value));
    } else {
      $this->args[] = '--'.$optionName;
    }
    return $this;
  }

  public function setWorkingDirectory($cwd) {
    $this->cwd = $cwd;
    return $this;
  }

  public function inheritEnvironmentVariables($inheritEnv = true) {
    $this->inheritEnv = $inheritEnv;
    return $this;
  }

  public function setEnv($name, $value) {
    $this->env[$name] = $value;
    return $this;
  }

  public function setInput($stdin) {
    $this->stdin = $stdin;
    return $this;
  }

  /**
   * @param int seconds
   */
  public function setTimeout($timeout) {
    $this->timeout = $timeout;
    return $this;
  }

  /**
   * Sets an proc_open -option
   */
  public function setOption($name, $value) {
    $this->options[$name] = $value;
    return $this;
  }

  public function end() {
    if ($this->escapeFor === self::WINDOWS)
      $cmdLine = escapeshellarg((string) $this->bin); // php escaping is sufficient on windows for filenames
    else
      $cmdLine = (string) $this->bin;
    
    if (count($this->args)) {
      $cmdLine .= ' '.implode(' ', $this->args);
    }

    if ($this->inheritEnv) {
      $env = $this->env ? $this->env + $_ENV : null;
    } else {
      $env = $this->env;
    }

    return new Process($cmdLine, $this->cwd, $env, $this->stdin, $this->timeout, $this->options);
  }
  
  /**
   * This escapes shell arguments on windows correctly
   *
   * it does not strip multibytes (on windows)
   * it does not replace " with ' ' on windows
   * it does not replace % with ' ' on windows
   *
   * you got still no chance to give the literal argument %defined%  if the env variabled "defined" is set.
   *
   * for unix the default escapeshellarg is used (it does strip multibytes)
   *
   * as the php escapeshellarg, on windows " is used and ' is used
   * @return string
   */
  public function escapeShellArg($arg) {
    // ported: PHPAPI char *php_escape_shell_arg(char *str)
    
    if ($this->escapeFor === self::WINDOWS) {
      return '"'.str_replace('"', '\"', $arg).'"';
    
    } else {
/*
 * char* arg is the to copied string
 *
 	case '\'':
	  arg[y++] = '\'';
	  arg[y++] = '\\';
  	arg[y++] = '\'';

    that looks weird to me: escape ' with '\'
    e.g.: he said it isn't his fault
         'he said it isn'\'t his fault'
    
    well.. they will know..
*/
      // this will strap multibytes(!)
      return escapeshellarg($arg);
      //$arg = str_replace("'", "'\\'", $arg);
    }
  }
}
?>