<?php

namespace Psc\TPL\ContentStream;

use Psc\Code\Code;
use Webforge\Common\JS\JSONConverter;
use Webforge\Common\JS\JSONParsingException;
use Webforge\Common\System\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateWidgetTemplateCommand extends \Psc\System\Console\DoctrineCommand {

  protected function configure() {
    $this->setName('cms:create-cs-widget');

    $this->addArgument('name', self::REQUIRED, 'name of widget in CamelCase');
    $this->addArgument('contexts', self::OPTIONAL | self::IS_ARRAY, 'where to show the widget in layoutManager values: sidebar-content|page-content');

    $this->addOption('section','', self::VALUE_REQUIRED, 'section of widget in layoutManager (class-name)');
    $this->addOption('label','', self::VALUE_REQUIRED, 'label for button control');

    $this->setDescription('Creates a new ContentStream Widget Template (and an Entity for that)');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->execInput = $input;
    $this->execOutput = $output;
    $this->initDoctrine($input, $output);

    $project = $this->getProject();
    $package = $this->getPackage();

    $name = $input->getArgument('name');
    $contexts = (array) $input->getArgument('contexts');

    if (empty($contexts)) $contexts = array('page-content');

    $section = $this->validateOptionalString($input->getOption('section'));
    $label = $this->validateOptionalString($input->getOption('label'));

    $this->widgetsDir = $project->dir('resources')->sub('widgets/');

    $this->info('Creating Widget '.$name.' in '.$this->widgetsDir);

    $this->jsonc = new JSONConverter();

    $specification = $this->getSpecification($name, $contexts, $section, $label);

    $template = $this->getTemplate($specification);


    $dashName = Code::camelCaseToDash($name);
    $tableName = 'cs_'.str_replace('-', '_', $dashName).'s';

    $tableName = $this->askDefault('TableName for Entity', $tableName);

    $entityExists = FALSE;
    try {
      $this->callCommand('cms:create-entity', array(
        'name'=>'ContentStream\\'.$name,
        'tableName'=>$tableName
      ), $output);

    } catch (\Psc\Code\Generate\ClassWritingException $e) {
      $entityExists = TRUE;
      $this->warn('Your entity was already created before. I will not recreate it.');
    }

    $this->comment('put this to the discrimination map from ContentStream\Entry'.($entityExists? ' (if not already)' : '').': ');
    $this->comment('  "'.$dashName.'" = "'.$name.'",');
    $this->br();

    if ($this->confirm('Should I compile the entities, now?')) {
      $this->callCommand('project:compile', array('--con'=>'tests'), $output);
      $this->br();
    }

    if ($this->confirm('Should I compile js templates, now?')) {
      system('grunt hogan');
      $this->br();
    }

    $this->info('finished.');

    return 0;
  }

  protected function getSpecification($name, $contexts, $section, $label) {
    $file = $this->widgetsDir->getFile($name.'.json');

    if (!$file->exists()) {
      $specificationFile = $this->writeJSON($name, $contexts, $section, $label);
      $firstMsg = 'Adjust the .json specification in then continue...';
    } else {
      $firstMsg = 'I\'m using the existing specification in: '.$file.' Check the specification and then continue...';
    }

    if ($this->confirm($firstMsg)) {
      return $this->readSpecification($file);
    }
  }

  protected function readSpecification(File $file) {
    try {
      return $this->jsonc->parseFile($file);
    } catch (JSONParsingException $e) {
      $this->out($e->getMessage());
      if ($this->confirm('You have an error in your file: '.$file.' Please correct it and continue...')) {
        return $this->readSpecification($file);
      } else {
        throw $this->exitException('Abbort: Cannot continue without specification');
      }
    }
  }

  protected function getTemplate($specification) {
    $file = $this->widgetsDir->getFile($specification->name.'.mustache');

    if ($file->exists()) {
      if (!$this->confirm('I\'m using the existing template in: '.$file.'. Confirm to continue..')) {
        throw $this->exitException('Abbort');
      }

    } else {
      $file->writeStupidTemplate($file, $specification);
    }

    return $file;
  }

  protected function writeJSON(File $file, Array $contexts, $section = NULL, $label = NULL) {
    $json = new \stdClass;
    $json->name = $name;

    if ($section) {
      $json->section = $section;
    }

    if ($label) {
      $json->label = $label;
    }

    if (count($contexts) > 0) {
      $json->contexts = $contexts;
    }

    $json->fields = 

    // write some examples
    $this->jsonc->parse('{
      "headline": { "type": "string", "label": "Überschrift", "defaultValue": "die Überschrift", "optional": true },
      "image": { "type": "image", "label": "Bild", "optional": true },
      "text": { "type": "text", "label": "Inhalt", "defaultValue": "Hier ist ein langer Text, der dann in der Teaserbox angezeigt wird..." },
      "link": {"type": "link", "label": "Link-Ziel", "optional": true}
    }');


    $file->getDirectory()->create();
    $file->writeContents($this->jsonc->stringify($json, JSONConverter::PRETTY_PRINT));
    $this->out('  wrote '.$file);

    return $file;
  }

  protected function writeStupidTemplate($file, \stdClass $specification) {   
    $part = <<<'HTML'
<div class="row-fluid">
  <div class="span2">{{%1$s.label}}</div>
  <div class="span10">{{&%1$s.input}}</div>
</div>
HTML;

    $html = '';
    foreach ($specification->fields as $fieldName => $field) {
      $html .= sprintf($part, $fieldName)."\n";
    }
    $html = mb_substr($html, 0, -1);

    $file->getDirectory()->create();
    $file->writeContents($html);
    $this->out('  wrote '.$file);

    return $file;
  }
}
