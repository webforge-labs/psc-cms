<?php

namespace Psc\System\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\ClassReader;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Compile\Compiler;
use Psc\Code\Compile;
use Webforge\Common\System\File;

class CompileCommand extends Command {
  
  protected $file;
  protected $outFile;
  
  protected function configure() {
    $this->addArgument('file',self::REQUIRED);
    $this->addArgument('class',self::REQUIRED);
    $this->addOption('out','o',self::VALUE_REQUIRED);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->execInput = $input;
    $output->writeln('Start Compiling file: '.$this->getInFile());
    parent::execute($input, $output);
    $output->writeln('finished.');
  }
  
  protected function getInFile() {
    if (!isset($this->file)) {
      $this->file = new File($this->execInput->getArgument('file'));
    
      if (!$this->file->exists()) {
        $output->writeLn('Datei: '.$file.' existiert nicht.');
        return 1;
      }
    }
    
    return $this->file;
  }
  
  protected function getCompiler() {
    $input = $this->execInput;
    $output = $this->execOutput;
    
    $class = $input->getArgument('class');
    $file = $this->getInFile();
    
    if ($class === 'inFile') {
      $parser = new \Psc\Doctrine\PhpParser;
      if (($class = $parser->findFirstClass((string) $file)) == NULL) {
        throw new \Psc\Exception('inFile kann keine Klasse ermitteln. Es wurden keine Klassen in der Datei gefunden');
      }
    }
    
    if (!class_exists($class, FALSE)) {
      require $file;
    }
    
    if (!class_exists($class, FALSE)) {
      throw new \InvalidArgumentException('Klasse '.$class.' konnte nicht in Datei '.$file.' gefunden werden');
    }

    $gClass = GClass::factory($class);
    $classReader = new ClassReader($file, $gClass);
    
    $compiler = new Compiler($classReader);
    
    return $compiler;
  }
  
  protected function getOutFile() {
    if (!isset($this->outFile)) {
      if (($out = $this->execInput->getOption('out')) !== NULL) {
        $this->outFile = new File($out);
      } else {
        // backup?
        $this->outFile = $this->getInFile();
      }
    }
    
    return $this->outFile;
  }

  protected function createType($typeDef) {
    if (mb_strpos($typeDef, '\\') !== FALSE) {
      $typeDef = sprintf('Object<%s>', $typeDef);
    }
    return \Psc\Data\Type\Type::create($typeDef);
  }
}
?>