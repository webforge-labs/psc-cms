<?php

namespace Psc\System\Console;

use Psc\Code\Generate\GParameter;
use Psc\Code\Compile\AddPropertyExtension;
use Webforge\Common\ArrayUtil AS A;

class AddClassPropertyCommand extends CompileCommand {
  
  protected function configure() {
    $this
      ->setName('compile:property')
      ->setDescription(
        'Fügt der Klasse in Property hinzu. Modifiziert den Constructor, erstellt Getter und Setter'
      );
    
    parent::configure();
    
    $this->addArgument('name', self::REQUIRED);
    $this->addArgument('type', self::OPTIONAL, 'Ein Psc\Data\Type\{$type}Type oder ein FQN', 'String');
    $this->addArgument('getter',self::OPTIONAL, 'Soll ein Getter erstellt werden', 'true');
    $this->addArgument('setter',self::OPTIONAL, 'Soll ein Setter erstellt werden', 'true');
    $this->addArgument('constructor',self::OPTIONAL, 'Soll der Parameter in den Constructor eingefügt werden? append|prepend|int(0-based-Position)', 'append');
    $this->addArgument('dependency-injection',self::OPTIONAL, 'Soll das Property wenn NULL mit einer Value (default: eine neue instanz der Klasse) initialisiert werden?', 'false');
    $this->addOption('upcase','',self::VALUE_REQUIRED, 'Der Name des Property mit erstem Großbuchstaben', NULL);
    $this->addOption('optional','',self::VALUE_OPTIONAL, 'Soll das Property im Constructor optional sein?', 'undefined');
  }
  
  protected function doExecute($input, $output) {
    $propertyName = $input->getArgument('name');
    $propertyUpcaseName = $input->getOption('upcase');
    $propertyType = $this->createType($input->getArgument('type'));
    $setter = $input->getArgument('setter') === 'true';
    $getter = $input->getArgument('getter') === 'true';
    $defaultValue = $input->getOption('optional') === 'NULL' ? NULL : GParameter::UNDEFINED;
    $dependencyInjection = AddPropertyExtension::DEPENDENCY_INJECTION_NONE;
    
    if (($dpi = $input->getArgument('dependency-injection')) === 'constructor') {
      $dependencyInjection = AddPropertyExtension::DEPENDENCY_INJECTION_CONSTRUCTOR;
      $defaultValue = NULL;
    } elseif ($dpi === 'getter') {
      $dependencyInjection = AddPropertyExtension::DEPENDENCY_INJECTION_GETTER;
      $defaultValue = NULL;
    }
    
    if (($cs = trim($input->getArgument('constructor'))) === 'false') {
      $constructor = FALSE;
    } elseif (ctype_digit($cs)) {
      $constructor = (int) $cs;
    } elseif ($cs === 'append') {
      $constructor = A::END;
    } elseif ($cs === 'prepend') {
      $constructor = 0;
    } else {
      throw new \InvalidArgumentException('Unbekannter Parameter: '.$cs);
    }
    
    $compiler = $this->getCompiler($input);
    
    $compiler->addExtension($extension = new AddPropertyExtension($propertyName, $propertyType, $dependencyInjection, $propertyUpcaseName));
    $extension
      ->setGenerateSetter($setter)
      ->setGenerateGetter($getter)
      ->setGenerateInConstructor($constructor)
      ->setConstructorDefaultValue($defaultValue)
    ;
    
    $compiler->compile($this->getOutFile());
  }  
}
?>