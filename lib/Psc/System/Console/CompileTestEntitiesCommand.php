<?php

namespace Psc\System\Console;

use \Symfony\Component\Console\Input\InputOption,
    \Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    
    Psc\Code\Generate\TestCreater,
    Psc\Code\Generate\GClass,
    Psc\Code\Generate\ClassWritingException,
    Psc\PSC,
    
    Webforge\Common\System\Dir,
    Webforge\Common\System\File
  ;

use Psc\Doctrine\ModelCompiler;

class CompileTestEntitiesCommand extends DoctrineCommand {

  public static function getTeaserSpecification() {
    return json_decode('
{
  "name": "TeaserHeadlineImageTextLink",

  "fields": {
    "headline": { "type": "string", "label": "Überschrift", "defaultValue": "die Überschrift" },
    "image": { "type": "image", "label": "Bild" },
    "text": { "type": "text", "label": "Inhalt", "defaultValue": "Hier ist ein langer Text, der dann in der Teaserbox angezeigt wird..." },
    "link": {"type": "link", "label": "Link-Ziel"}
  }
}
');    
  }
  
  protected $modelCompiler;
  protected $ccompiler; // commonProjectCompiler

  protected function configure() {
    parent::configure();
    $this
      ->setName('compile:test-entities')
      ->setDescription(
        'Erstellt alle Test Entities in Psc\Doctrine\TestEntities'
      )
    ;

    $this->addOption('filter');
  }
  
  protected function doExecute($input, $output) {
    $this->ccompiler = new \Psc\CMS\CommonProjectCompiler($this->dc);
    
    $this->modelCompiler = new ModelCompiler();
    $this->modelCompiler->setOverwriteMode(TRUE);

    $filter = mb_strtolower($input->getOption('filter'));

    $output->writeLn('compiling TestEntities..');

    $methods = array(
      'Person',
      'Image',
      'File',
      'Tag',
      'Article',
      'Category',
      'Page',
      'NavigationNode',
      'ContentStream',
      'ContentStreamEntry',
      'CSHeadline',
      'CSParagraph',
      'CSLi',
      'CSImage',
      'CSTeaserWidget',
      'CSSimpleTeaser',
      'CSWrapper'
    );

    foreach ($methods as $method) {
      if (!$filter || mb_strpos($filter, mb_strtolower($method)) !== FALSE) {
        $output->writeln('  compile'.$method);
        $output->writeLn('   wrote '.$this->{'compile'.$method}());
      }
    }

    $output->writeLn('finished.');
    return 0;
  }
  
  protected function compilePerson() {
    extract($this->help());
    
    $entityBuilder = $this->modelCompiler->compile(
      $entity(new GClass('Psc\Doctrine\TestEntities\Person')),
        $defaultId(),
        $property('name', $type('String')),
        $property('firstName', $type('String')),
        $property('email', $type('Email')),
        $property('birthday', $type('Birthday')),
        $property('yearKnown', $type('Boolean')),
      $constructor(
        $argument('name'),
        $argument('email', NULL),
        $argument('firstName', NULL),
        $argument('birthday', NULL)
      )
    );
    return $entityBuilder->getWrittenFile();
  }
  
  public function compileImage() {
    return $this->ccompiler->doCompileImage('Image', function ($help) {
      extract($help);
        
    })->getWrittenFile();
  }

  public function compileFile() {
    return $this->ccompiler->doCompileFile('File', function ($help) {
      extract($help);
        
    })->getWrittenFile();
  }

  public function compilePage() {
    return $this->ccompiler->doCompilePage()->getWrittenFile();
  }

  public function compileNavigationNode() {
    return $this->ccompiler->doCompileNavigationNode()->getWrittenFile();
  }

  public function compileContentStream() {
    return $this->ccompiler->doCompileContentStream()->getWrittenFile();
  }

  public function compileContentStreamEntry() {
    return $this->ccompiler->doCompileContentStreamEntry()->getWrittenFile();
  }

  public function compileCSWrapper() {
    return $this->ccompiler->doCompileCSWrapper()->getWrittenFile();
  }

  public function compileCSHeadline() {
    return $this->ccompiler->doCompileCSHeadline()->getWrittenFile();
  }

  public function compileCSParagraph() {
    return $this->ccompiler->doCompileCSParagraph()->getWrittenFile();
  }

  public function compileCSLi() {
    return $this->ccompiler->doCompileCSLi()->getWrittenFile();
  }

  public function compileCSImage() {
    return $this->ccompiler->doCompileCSImage()->getWrittenFile();
  }

/*  public function compileCSImage() {
    return $this->ccompiler->doCompileCSImage()->getWrittenFile();
  }
*/
  public function compileCSSimpleTeaser() {
    extract($this->help());
    extract($this->ccompiler->csHelp());

    return $this->modelCompiler->compile(
      $entity('ContentStream\SimpleTeaser', $extends($expandClass('ContentStream\Entry'))),

      $property('headline', $type('String')),
      $property('text', $type('MarkupText')),

      $constructor(
        $argument('headline'),
        $argument('text', NULL)
      ),

      $build($relation($targetMeta($expandClass('ContentStream\Image')), 'OneToOne', 'unidirectional')->setNullable(TRUE)),
      $build($relation($targetMeta('NavigationNode')->setAlias('Link'), 'OneToOne', 'unidirectional')->setNullable(TRUE)),

      $build($csSerialize(array('headline', 'text', 'link', 'image'))),
      $build($csLabel('Normaler Teaser'))


    )->getWrittenFile();
  }

  protected function compileTag() {
    extract($this->help());
    
    $entityBuilder = $this->modelCompiler->compile(
      $entity(new GClass('Psc\Doctrine\TestEntities\Tag')),
        $defaultId(),
        $property('label', $type('String')),
        $property('created', $type('DateTime')),
      $constructor(
        $argument('label')
      ),
      $manyToMany('Psc\Doctrine\TestEntities\Article', FALSE) // not owning side
    );
    
    return $entityBuilder->getWrittenFile();
  }

  protected function compileCategory() {
    extract($this->help());
    
    $entityBuilder = $this->modelCompiler->compile(
      $entity(new GClass('Psc\Doctrine\TestEntities\Category')),
        $defaultId(),
        $property('label', $type('String')),
      $constructor(
        $argument('label')
      ),
      $OneToMany('Psc\Doctrine\TestEntities\Article')  // ein Artikel hat nur eine Kategorie
    );
    
    return $entityBuilder->getWrittenFile();
  }
  
  protected function compileArticle() {
    extract($this->help());
    
    $entityBuilder = $this->modelCompiler->compile(
      $entity(new GClass('Psc\Doctrine\TestEntities\Article')),
        $defaultId(),
        $property('title', $type('String')),
        $property('content', $type('MarkupText')),
        $property('sort', $type('Integer'), $nullable()),
        
      $constructor(
        $argument('title'),
        $argument('content')
      ),
      $manyToMany('Psc\Doctrine\TestEntities\Tag', TRUE), // owning side
      
      $build($relation('Psc\Doctrine\TestEntities\Category', 'ManyToOne', 'bidirectional')->setNullable(TRUE))
    );
    
    return $entityBuilder->getWrittenFile();
  }

  protected function compileCSTeaserWidget() {
    return $this->ccompiler->doCompileCSWidget(self::getTeaserSpecification())->getWrittenFile();
  }

  protected function help() {
    if (!isset($this->help)) {
      $this->help = $this->modelCompiler->getClosureHelpers();
    }

    return $this->help;
  }
}
