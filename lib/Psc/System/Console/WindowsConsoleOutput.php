<?php

namespace Psc\System\Console;

class WindowsConsoleOutput extends \Symfony\Component\Console\Output\ConsoleOutput {
  
  public function doWrite($message, $newline) {
    return parent::doWrite(mb_convert_encoding($message, 'CP850', 'UTF-8'), $newline);
  }
}
?>