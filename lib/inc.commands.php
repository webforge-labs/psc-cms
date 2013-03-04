<?php

use Psc\Code\Code;
use Psc\Code\Generate\GClass;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Webforge\Setup\ConfigurationTester\ConfigurationTester;
use Psc\System\System;
use Psc\Code\Generate\ClassWriter;

use Psc\JS\jQuery;

/**
 *
 * $createCommand = function ($name, array|closure $configure, closure $execute) {
 * 
 * $arg = function ($name, $description = NULL, $required = TRUE) // default: required
 * $opt = function($name, $short = NULL, $withValue = TRUE, $description = NULL) // default: mit value required
 * $flag = function($name, $short = NULL, $description) // ohne value
 */

$createCommand('compile:komodo-command-call',
  array(
    $arg('extension-name')
  ),
  function ($input, $output, $command) {
    $extensionClass = Code::expandNamespace(\Webforge\Common\String::expand($input->getArgument('extension-name'), 'Extension'), 'Psc\Code\Compile');
    $extension = GClass::factory($extensionClass);
    
    $fields = array();
    if ($extension->hasMethod('__construct')) {
      foreach($extension->getMethod('__construct')->getParameters() as $param) {
        $fields[] = $param->getName();
      }
    }
    
    // das nimmt vielleicht zu viele, weis nicht, alternativ würds auch ne statische methode zur extension tun
    foreach ($extension->getProperties() as $property) {
      $fields[] = $property->getName();
    }
    
    $json = array();
    foreach (array_unique($fields) as $field) {
      $json[$field] = '%(ask:'.$field.')';
    }
    
    $command->comment(sprintf('Komodo-Command für %s:', $extension->getClassName()));
    $output->writeln(\Psc\TPL\TPL::miniTemplate(
      'cli.bat compile:generic %F inFile %extension% %json%',
      array(
        'extension'=>$extension->getClassName(),
        'json'=>json_encode((object) $json)
      )
    ));
  }
);

$createCommand('git:pre-push-hook',
  array(
    $arg('someFile'),
    $arg('someFile2'),
    $arg('workingTree')
  ),
  function ($input, $output, $command) {
    $workingTree = Dir::factoryTS($input->getArgument('workingTree'));
    
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $root = $dir->up();
    
    if ($workingTree->equals($root)) {
      $dist = $root->getFile('dist/psc-cms.phar.gz');
      $lastMessage = exec('git log -n 1 --format=%s');
      
      $updateComposer = 'composer update --prefer-dist --dev';
      $buildPhar = $root->getFile('bin/cli').' build-phar';
      $cd = 'cd '.$root;
      $ammend = 'git commit -m"'.$lastMessage.'" --amend -o '.$dist.' composer.lock ';
      
      system($cd.' && '.$updateComposer.' && '.$buildPhar.' && '.$ammend);
      return 0;
    }
    
    throw new Exception($workingTree. ' is not registered as hook script');
  }
);

$createCommand('composer:update',
  array(
  ),
  function ($input, $output, $command) {
    system('cd '.$command->getProject()->getSrc().' &&php '.\Psc\PSC::getRoot()->getFile('composer.phar').' update');
  }
);


$createCommand('test-configuration',
  array(
  ),
  function ($input, $output, $command) {
    $cfgTest = new ConfigurationTester();
    
    $cfgTest->INI('mbstring.internal_encoding', 'UTF-8');
    $cfgTest->INI('post_max_size', '30M', '>=');
    $cfgTest->INI('upload_max_filesize', '30M', '>=');
    
    $cfgTest->INI('memory_limit', '200M', '>=');
    $cfgTest->INI('suhosin.memory_limit', '200M', '>=');
    
    $cfgTest->INI('display_errors', TRUE);
    
    // curl needs to be installed
    // mb_string needs to be installed
    
    $output->writeln((string) $cfgTest);
  }
);

$createCommand('compile:jqx-widget-specification',
  array(
    $arg('html-table-file', 'Die Datei in der das HTMl für table.documentation-table ist'),
    $arg('widgetName', 'Der Name des Widgets ohne jq davor')
  ),
  function ($input, $output, $command) {
    $argFile = File::factory($input->getArgument('html-table-file'));
    $widgetName = ucfirst($input->getArgument('widgetName'));
    $html = $argFile->getContents();
    
    $scraper = new \Psc\XML\Scraper();
    
    $table = $scraper->table($html, 'table.documentation-table')
      ->useHeader(function ($th) {
        return trim(mb_strtolower($th->text()));
      })
      ->tdConverter(function ($td) {
        return trim($td->text());
      })
      ->rowFilter(function ($row, $headerFound) {
        return !$headerFound || $row->find('td.documentation-option-type-click')->length > 0;
      })
      ->scrape();
      
    $class = new \Psc\Code\Generate\ClassBuilder(new \Psc\Code\Generate\GClass(sprintf('Psc\UI\jqx\%sSpecification', $widgetName)));
    $class->setParentClass(new \Psc\Code\Generate\GClass('Psc\UI\jqx\Widget'));
    
    $properties = array();
    $undefined = '__spec__undefined';
    foreach ($table->rows as $row) {
      if ($row['type'] != 'Event' && $row['type'] != 'Method') {
        $type = str_replace(array('Number/String', 'Number', 'function'), array('Mixed', 'Integer', 'Mixed'), $row['type']);
        $property = $class->addProperty($row['name'], \Psc\Data\Type\Type::create($type))->setDefaultValue($undefined);
        $properties[] = $property->getName();
      }
    }
    $class->generateGetters();
    $class->generateSetters();
    $class->generateDocBlocks();
    
    $optionsCode = array();
    $optionsCode[] = '$options = new \stdClass;';
    $optionsCode[] = sprintf('foreach (%s as $option) {', $class->getCodeWriter()->exportList($properties));
    $optionsCode[] = sprintf('  if ($this->$option !== "%s") {', $undefined);
    $optionsCode[] = sprintf('    $options->$option = $this->$option;');
    $optionsCode[] = '  }';
    $optionsCode[] = '}';
    $optionsCode[] = 'return $options;';
    $class->createMethod('getWidgetOptions', array(), $optionsCode)
            ->createDocBlock()
              ->setSummary('Gibt alle gesetzten Optionen des Widgets zurück')
              ->addSimpleAnnotation('return stdClass');
    
    $class->getClassDocBlock()->addSimpleAnnotation('compiled jqx-widget-specification console-command on '.date('d.m.Y H:i'));
    
    $cw = new ClassWriter($gClass = $class->getGClass());
    $cw->setUseStyle(ClassWriter::USE_STYLE_LINES);
    
    $phpFile = $command->getProject()->getClassFile($gClass);
    $cw->write($phpFile, array(), $overwrite = ClassWriter::OVERWRITE);
    
    $output->writeln($phpFile.' written.');
  }
);
?>