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
  }
  
  protected function doExecute($input, $output) {
    $this->ccompiler = new \Psc\CMS\CommonProjectCompiler($this->dc);
    
    $this->modelCompiler = new ModelCompiler();
    $this->modelCompiler->setOverwriteMode(TRUE);
    
    $output->writeLn('compiling TestEntities..');
    $output->writeLn('  '.$this->compilePerson().' geschrieben');
    $output->writeLn('  '.$this->compileImage().' geschrieben');
    $output->writeLn('  '.$this->compileFile().' geschrieben');
    $output->writeLn('  '.$this->compileTag().' geschrieben');
    $output->writeLn('  '.$this->compileArticle().' geschrieben');
    $output->writeLn('  '.$this->compileCategory().' geschrieben');
    $output->writeLn('  '.$this->compilePage().' geschrieben');
    $output->writeLn('  '.$this->compileNavigationNode().' geschrieben');
    $output->writeLn('  '.$this->compileContentStream().' geschrieben');
    $output->writeLn('  '.$this->compileContentStreamEntry().' geschrieben');
    $output->writeLn('  '.$this->compileCSHeadline().' geschrieben');
    $output->writeLn('  '.$this->compileCSParagraph().' geschrieben');
    $output->writeLn('  '.$this->compileCSImage().' geschrieben');

    $output->writeLn('  '.$this->compileCSSimpleTeaser().' geschrieben');
    $output->writeLn('finished.');
  }
  
  protected function compilePerson() {
    extract($this->modelCompiler->getClosureHelpers());
    
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

  public function compileCSHeadline() {
    return $this->ccompiler->doCompileCSHeadline()->getWrittenFile();
  }

  public function compileCSParagraph() {
    return $this->ccompiler->doCompileCSParagraph()->getWrittenFile();
  }

  public function compileCSImage() {
    return $this->ccompiler->doCompileCSImage()->getWrittenFile();
  }

/*  public function compileCSImage() {
    return $this->ccompiler->doCompileCSImage()->getWrittenFile();
  }
*/
  public function compileCSSimpleTeaser() {
    extract($this->modelCompiler->getClosureHelpers());
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

      $build($csSerialize('headline', 'text', 'link', 'image')),
      $build($csLabel('Normaler Teaser'))


    )->getWrittenFile();
  }

  protected function compileTag() {
    extract($this->modelCompiler->getClosureHelpers());
    
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
    extract($this->modelCompiler->getClosureHelpers());
    
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
    extract($this->modelCompiler->getClosureHelpers());
    
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
}
