<?php

namespace Psc;

use Psc\Code\Event\Event;
use Psc\Data\Storage;
use Psc\Data\PHPStorageDriver;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\CMS\Configuration;

/**
 * In 99% der Fälle willst Du \Psc\CMS\Project und nicht dieses hier
 *
 * Dies ist die Projektableitung für Projekt
 * Diese Klasse wird gebootstrapped für die cli und für die Tests im psc-cms selbst. Dies ist also das private Projekt von psc-cms.
 *
 * verwaltet das Psc.SourceFileChanged Event
 * @Psc\Event SourceFileChanged
 */
class Project extends \Psc\CMS\Project implements \Psc\Code\Event\Subscriber {
  
  public function setUp() {
    parent::setUp();
  }
  
  public function bootstrap() {
    parent::bootstrap();
    
    /* wir registrieren das sourcefilechanged event */
    PSC::getEventManager()->bind($this, 'Psc.SourceFileChanged');
    
    $this->getCache()->create();
    
    return $this;
  }
  
  protected function bootstrapClassLoader() {
    return $this; // wir wollen keinen ClassLoader für unsere klassen weil wor schon einen PharAutoLoader oder einen AutoLoader haben
  }
  
  /**
   *
   * ich kann mich einfach nicht enscheiden ob das hier die aufgabe vom doctrine modul ist oder von mir
   */
  public function createModule($name) {
    $module = parent::createModule($name);
    
    //if ($module instanceof \Psc\Doctrine\Module) {
    //  $module
    //    ->registerEntityClassesMetadataDriver()
    //    ->getEntityClassesMetadataDriver()
    //      ->addClass('Psc\Doctrine\TestEntities\Tag');
    //
    //}
    
    return $module;
  }
  
  public function getInstallPharFile() {
    return $this->getRoot()->sub('dist')->getFile('psc-cms.phar.gz');
  }
  
  public function getNamespace() {
    return 'Psc';
  }
  
  public function trigger(Event $event) {
    switch($event->getIdentifier()) {
      case 'Psc.SourceFileChanged':
        return $this->onSourceFileChange($event);
      case 'Psc.Doctrine.ModuleBootstrapped':
        return $this->onDoctrineModuleBootstrapped($event);
    }
    parent::trigger($event);
  }
  
  public function onDoctrineModuleBootstrapped($event) {
    // test entities für dieses project
    $module = $event->getTarget();
    $module->getEntityClassesMetadataDriver()
      ->addClass('Psc\Doctrine\TestEntities\Article')
      ->addClass('Psc\Doctrine\TestEntities\Person')
      ->addClass('Psc\Doctrine\TestEntities\Tag')
      ->addClass('Psc\Doctrine\TestEntities\Category')
      
      // mal so zum testen, ob das das phar anschiebt (jupp)
      ->addClass('Psc\Entities\Image')
      ->addClass('Psc\Entities\User')
      ->addClass('Psc\Entities\File')
    ;
  }
  
  public function onSourceFileChange(Event $event) {
    $file = $event->getData()->file;
    
    $storage = new Storage(
      new PHPStorageDriver(
        new File($this->getCache(),'storage.changedFiles.php')
      )
    );
    
    $data = $storage->init()->getData();
    $data->set(array('files',(string) $file), TRUE);
    $data->set(array('mtime'),time());
    
    $storage->persist();
    
    // wir haben die geänderte Datei noch gespeichert, jetzt compilen wir (denn das war gewünscht)
    if ($event->getData()->compile) {
      $out = new File(PSC::get(PSC::ROOT), 'psc-cms.phar.gz');
      $libraryBuilder = new \Psc\Code\Build\LibraryBuilder($this, $logger = new \Psc\System\BufferLogger());
      
      if ($libraryBuilder->isCompilingNeeded()) {
        $libraryBuilder->compile($out);
        $libraryBuilder->resetCompilingNeeded();
      }
    }
  }
}
?>