<?php

namespace Psc\System\Console;

use Psc\Code\Generate\GClass;
use Psc\Code\Code;
use Psc\Code\Compile\ArrayCollectionInterfaceExtension;
use Webforge\Types\Type;
use Webforge\Types\CollectionType;

class ArrayCollectionInterfaceCompileCommand extends CompileCommand {
  
  protected function configure() {
    $this
      ->setName('compile:array-collection-interface')
      ->setDescription(
        'Erstellt eine Array Collection und das passende interface add() remove() has() dazu'
      );
    
    parent::configure();
    
    $this->addArgument('propertyPlural', self::REQUIRED);
    $this->addArgument('propertySingular', self::REQUIRED);
    $this->addArgument('itemType', self::OPTIONAL);
    $this->addArgument('implementation', self::OPTIONAL, 'array|collection fuer die Implementierung mit einem Array oder einer ArrayCollection', 'array');
  }
  
  protected function doExecute($input, $output) {
    $compiler = $this->getCompiler();
    
    $compiler->addExtension(
      $extension = new ArrayCollectionInterfaceExtension(
        $input->getArgument('propertyPlural'),
        $input->getArgument('propertySingular'),
        $this->createType($input->getArgument('itemType')) // damit convenience wie Psc\Bla\Bla auch als Type geht
      )
    );
    
    
    if ($input->getArgument('implementation') === 'collection') {
      $extension->setCollectionType(new CollectionType(CollectionType::PSC_ARRAY_COLLECTION));
    } // kein else: array ist eh schon der default
    
    $compiler->compile($this->getOutFile());
  }
}
