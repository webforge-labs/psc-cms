<?php

namespace Psc\TPL\ContentStream;

use Psc\Code\Code;
use Webforge\Common\JS\JSONConverter;

class CreateWidgetTemplateCommand extends \Psc\System\Console\Command {

  protected function configure() {
    $this->setName('cms:create-cs-widget');

    $this->addArgument('name', self::REQUIRED, 'name of widget in CamelCase');
    $this->addArgument('contexts', self::OPTIONAL | self::IS_ARRAY, 'where to show the widget in layoutManager values: sidebar-content|page-content');

    $this->addOption('section','', self::VALUE_REQUIRED, 'section of widget in layoutManager (class-name)');
    $this->addOption('label','', self::VALUE_REQUIRED, 'label for button control');

    $this->addOption('overwrite','', self::VALUE_NONE, 'overwrite existing json');
    $this->addOption('just-entity','', self::VALUE_NONE, 'overwrite existing json');
    
    $this->setDescription('Creates a new ContentStream Widget Template (and an Entity for that)');
  }

  protected function doExecute($input, $output) {
    $project = $this->getProject();
    $package = $this->getPackage();
    $module = $project->getModule('Doctrine');

    $name = $input->getArgument('name');
    $contexts = (array) $input->getArgument('contexts');

    $section = $this->validateOptionalString($input->getOption('section'));
    $label = $this->validateOptionalString($input->getOption('label'));

    $this->widgetsDir = $package->getRootDirectory()->sub('application/js-src/SCE/Widgets/');

    $this->info('Creating Widget '.$name.' in '.$this->widgetsDir);

    $this->jsonc = new JSONConverter();

    if (!$input->getOption('just-entity')) {
      $specifictionFile = $this->writeJSON($name, $contexts, $section, $label);

      if ($this->confirm('Adjust the .json specification in then continue...')) {
        $specification = $this->jsonc->parseFile($specifictionFile);
        
        $this->writeStupidTemplate($specification);
      }
    }

    $dashName = Code::camelCaseToDash($name);
    $tableName = 'cs_'.str_replace('-', '_', $dashName).'s';

    $tableName = $this->askDefault('TableName for Entity', $tableName);

    $this->callCommand('cms:create-entity', array(
      'name'=>'ContentStream\\'.$name,
      'tableName'=>$tableName
    ), $output);
    

    $this->comment('put this to the discrimination map from ContentStream\Entry: ');
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

  protected function writeJSON($name, Array $contexts, $section = NULL, $label = NULL) {
    $file = $this->createNewFile($name.'.json');

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


    $file->writeContents($this->jsonc->stringify($json, JSONConverter::PRETTY_PRINT));
    $this->out('  wrote '.$file);

    return $file;
  }

  protected function writeStupidTemplate(\stdClass $specification) {
    $file = $this->createNewFile($specification->name.'.mustache');

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

    $file->writeContents($html);
    $this->out('  wrote '.$file);

    return $file;
  }

  protected function createNewFile($name) {
    $file = $this->widgetsDir->getFile($name);

    if ($file->exists() && !$this->execInput->getOption('overwrite')) {
      throw $this->exitException('will not overwrite: '.$file.'. use --overwrite', 1);
    } else {
      $file->getDirectory()->create();
    }

    return $file;
  }
}
