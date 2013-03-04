<?php

namespace Psc\System;

use Psc\PSC,
    Psc\Environment,
    Psc\System\ExecuteException as SystemExecuteException,
    Psc\A,
    Webforge\Common\String
;

class System extends \Psc\Object {
  
  const FORCE_UNIX =  0x000001;
  const REQUIRED =    0x000002;
  const DONTQUOTE =   0x000004;
  
  const COPY_INI = 'copy_ini_value';
  
  /**
   * Gibt den Ort kompletten Pfad zur ausführbaren Datei zurück
   *
   * Wird der Befehl nicht gefunden wird NULL zurückgegeben,
   * ist das flag REQUIRED gesetzt, wird eine exception stattdessen geschmissen
   */
  public static function which($command, $flags = 0x000000) {
    $win = ($flags ^ self::FORCE_UNIX) && PSC::getEnvironment()->getOS() == Environment::WINDOWS;
    
    $locate = function ($cmd) use ($flags, $win) {
      $location = exec($cmd);
      
      if (mb_strlen($script = trim($location)) > 0) {
        if ($win && ($flags ^ \Psc\System\System::DONTQUOTE) && mb_strpos($script,' ')) 
          return '"'.$script.'"';
        else
          return $script;
      }
      
      throw new \Psc\System\Exception('CMD did not return');
    };

    if ($win) {
      
      foreach (Array(
        'for %i in ('.$command.'.bat) do @echo.   %~$PATH:i',
        'for %i in ('.$command.'.exe) do @echo.   %~$PATH:i'
      ) as $cmd) {
      
        try {
          return $locate($cmd);
      
        } catch (\Psc\System\Exception $e) {
        }
      }

    } else {
      /* UNIX */
      $cmd = 'which '.$command;
      
      try {
        return $locate($cmd);
      } catch (\Psc\System\Exception $e) {
      }
    }
    
    //failure
    if ($flags & self::REQUIRED) {
      throw new Exception('Es konnte kein Pfad für Befehl: "'.$command.'" gefunden werden');
    } else {
      return $command;
    }
  }
  

  public static function execute($cmd, $cwd = NULL, $stdin = NULL, &$stdout = NULL, &$stderr = NULL, Array $env = NULL, &$retval = NULL) {
    if (!isset($env)) $env = $_ENV;
    if (!isset($cwd)) $cwd = getcwd();
    
    $descriptorspec = array(
      0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
      1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
      2 => array("pipe", "w")   // stderr is a file to write to
    );
  
    $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env, array('bypass_shell'=>TRUE));
    $stderr = 'cannot open proc';
    $stdout = NULL;
    
    if (is_resource($process)) {
      if (isset($stdin)) {
        fwrite($pipes[0],$stdin);
      }
      fclose($pipes[0]);

      $stdout = stream_get_contents($pipes[1]);
      fclose($pipes[1]);

      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
      
      $retval = proc_close($process);
      
      if ($retval == 0) {
        return $stdout;
      }
    }
    
    $v  = NULL;
    if ($stderr != '') {
      $v .= 'stderr: '.$stderr;
    }
    if ($stdout != '') {
      $v .= 'stdout: '.$stdout;
    }
    
    $e = new SystemExecuteException('Aufruf von "'.$cmd.'" nicht erfolgreich. '.$v);
    $e->cwd = $cwd;
    $e->env = $env;
    $e->stderr = $stderr;
    $e->stdout = $stdout;
    
    $e->verbose = $v;
    throw $e;
  }
  
  public static function executeBackground($cmd, $flags = 0x000000) {
    $win = ($flags ^ self::FORCE_UNIX) && PSC::getEnvironment()->getOS() == Environment::WINDOWS;
    
    if (!$win) {
      throw new \Psc\Exception('Unix Implementation nicht da ');
    } else {
      
      $cmd = System::which('start').' /B '.$cmd. ' ';
      return pclose(popen($cmd,'r'));
    }
  }

  /**
   *
   * $inis = array('include_path' => System::COPY_INI)
   */
  public static function executePHP($file, $cwd = NULL, Array $inis = array(), $stdin = NULL, &$stdout = NULL, &$stderr = NULL, Array $env = NULL) {
    $php = self::which('php');
    
    $directives = ' ';
    foreach ($inis as $directive => $value) {
      if ($value == self::COPY_INI) {
        $value = ini_get($directive);
      }
      
      $directives .= '-d '.$directive.'='.self::escapeArgument($value).' ';
    }
    print "\n";
    $cmd = $php.$directives.'-f '.$file;
    
    print $cmd;
    
    return self::execute($cmd, $cwd, $stdin, $stdout, $stderr, $env);
  }
  
  public static function escapeArgument($argument, $commandQuotes = String::DOUBLE_QUOTE) {
    $otherQuote = $commandQuotes == String::DOUBLE_QUOTE ? String::SINGLE_QUOTE : String::DOUBLE_QUOTE;
    
    /* wenn das letzte zeichen von argument ein backslash ist würde dies das quoting escapen.
       also escapen wird den backslash indem wir einen weiteren backslash anfügen
    */
    if (String::endsWith($argument,String::BACKSLASH) && !String::endsWith(mb_substr($argument,0,-1),String::BACKSLASH))
      $argument = $argument.String::BACKSLASH; 
    
    
    $argument = $otherQuote.addcslashes($argument,$otherQuote).$otherQuote;
    
    return $argument;
  }
  
  public static function escapeExecutable(File $executable) {
    $win = PSC::getEnvironment()->getOS() == Environment::WINDOWS;
    $script = (string) $executable;
    if ($win && mb_strpos($script,' ')) 
      return '"'.$script.'"';
    elseif (!$win)
      return addcslashes($script,'\ ');
    else
      return $script;
  }
}
?>