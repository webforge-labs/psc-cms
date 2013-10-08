<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;
use Psc\Data\Cache;
use Psc\Data\FileCache;
use Psc\Doctrine\EntityRepository;
use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Webforge\Framework\Project as WebforgeProject;

/**
 * 
 */
class UploadManager extends \Psc\SimpleObject {
  
  const IF_NOT_EXISTS = 0x000001;
  const UPDATE_ORIGINALNAME = 0x000002;
  
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var Psc\Data\Cache\Cache
   */
  protected $cache;
  
  /**
   * Der Name des Entities die der UploadManager verwaltet
   *
   * das DoctrineModule wird mit getEntityName() mit diesem Parameter befragt. Gibt es also ein Entity File im Projekt, ist der Default okay
   * @var string ist hier gesetzt immer der FQN als setter kann aber auch der shortname übergeben werden
   */
  protected $entityName;
  
  /**
   * @var Psc\Doctrine\EntityRepository
   */
  protected $repository;
  
  public function __construct($entityName, DCPackage $dc, Cache $cache) {
    if (!isset($entityName)) $entityName = 'file';
    $this->setDoctrinePackage($dc);
    $this->setCache($cache);
    $this->setEntityName($entityName);
  }
  
  public static function createCache(Dir $dir) {
    return new FileCache($dir, $direct = TRUE);
  }

  public static function createForProject(WebforgeProject $project, DCPackage $dc, $entityName = NULL) {
    return new static(
      $entityName,
      $dc,
      static::createCache($project->dir('cms-uploads'))
    );
  }
  
  /**
   * Speichert eine gewöhnliche Datei als UploadedFile
   *
   */
  public function store(File $file, $description = NULL, $flags = 0x000000) {
    $hash = $file->getSha1();
    
    if ($flags & self::IF_NOT_EXISTS) {
      try {
        $uplFile = $this->load($hash);
        
        if (($flags & self::UPDATE_ORIGINALNAME) && $file instanceof \Psc\System\UploadedFile) {
          $uplFile->setOriginalName($file->getOriginalName());
        }
        
        return $uplFile;
      } catch (UploadedFileNotFoundException $e) {
        
      }
    }
    
    $uplFile = $this->newInstance($file, $description);
    $uplFile->setHash($hash); // wir speichern das hier "doppelt", damit wir in der datenbank danach indizieren können
    
    if ($file instanceof \Psc\System\UploadedFile) {
      $uplFile->setOriginalName($file->getOriginalName());
    }
    
    /* im Cache ablegen */
    $cacheFile = $this->cache->store(array(mb_substr($hash,0,1), $hash),
                                     $file
                                     );
    // afaik brauchen wir sourcePath hier nicht, denn es ist eignetlich egal wo der Cache seinen Krams ablegt
    // mit derselben Instanz des Caches kommen wir immer wieder an die SourceFile ran
    
    $this->persist($uplFile);
    
    return $uplFile;
  }
  
  /**
   * Lädt eine Datei aus der Datenbank
   * 
   * @params $input
   * 
   * @param int    $input die ID des Entities
   * //@param string $input der gespeicherte sourcePath des Entities (muss / oder \ enthalten)
   * @param string $input der sha1 hash des Contents der Datei
   * @return Psc\CMS\UploadedFile
   * @throws Psc\CMS\UploadedFileNotFoundException
   */
  public function load($input) {
    try {
      if ($input instanceof File) {
        $input = $input->getSha1();
      }
      
      if (is_numeric($input)) {
        $uplFile = $this->getRepository()->hydrate((int) $input);
      /*
      } elseif (is_string($input) && (mb_strpos($input,'/') !== FALSE || mb_strpos($input,'\\') !== FALSE)) {
        $uplFile = $this->repository->hydrateBy(array('sourcePath'=>(string) $input));
      */
      } elseif (is_string($input)) { // hash
        $uplFile = $this->getRepository()->hydrateBy(array('hash'=>(string) $input));
      } elseif ($input instanceof UploadedFile) {
        $uplFile = $input;
      
      } else {
        throw new \Psc\Exception('Input kann nicht analyisiert werden: '.Code::varInfo($input));
      }
      
      $this->doAttach($uplFile);
      return $uplFile;
    
    } catch (\Psc\Doctrine\EntityNotFoundException $e) {
      throw UploadedFileNotFoundException::fromEntityNotFound($e, $input);
    }
  }

  /**
   * @return Psc\CMS\UploadedFile
   */
  protected function newInstance(File $file, $description = NULL) {
    $c = $this->entityName;
    return new $c($file, $description);
  }
  
  protected function persist($uplFile) {
    $this->doAttach($uplFile);
    $this->dc->getEntityManager()->persist($uplFile);
    return $this;
  }
  
  protected function doAttach(UploadedFile $uplFile) {
    $uplFile->setManager($this);
  }
  
  public function attach(UploadedFile $uplFile) {
    $this->doAttach($uplFile);
    return $this;
  }
  
  public function flush() {
    $this->dc->getEntityManager()->flush();
  }

  public function clear() {
    $this->dc->getEntityManager()->clear();
  }
  

  /**
   * Gibt für eine UploadedFile die SourceFile (Binary) zurück
   *
   * @TODO bei anderen Caches die kein FileCache sind muss hier noch die Datei temporär erzeugt werden (sense?)
   * @return Webforge\Common\System\File
   */
  public function getSourceFile(UploadedFile $uplFile) {
    $hash = $uplFile->getHash();
    $loaded = FALSE;
    return $this->cache->load(array(mb_substr($hash, 0, 1), $hash), $loaded);
  }
  
  public function getURL(UploadedFile $uplFile, $filename = NULL) {
    $fakeFilename = NULL;
    
    if ($filename) {
      //$file = $this->getSourceFile($uplFile);
      // unsere file hat keine extension
      $fakeFilename = '/'.$filename;
    } elseif ($uplFile->getOriginalName() != NULL) {
      $fakeFilename = '/'.$uplFile->getOriginalName();
    }
    
    return '/cms/uploads/'.$uplFile->getHash().$fakeFilename;
  }

  /**
   * @param Psc\Doctrine\DCPackage $dc
   */
  public function setDoctrinePackage(DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }
  
  /**
   * @param Psc\Data\Cache\Cache $cache
   */
  public function setCache(Cache $cache) {
    $this->cache = $cache;
    return $this;
  }
  
  /**
   * @return Psc\Data\Cache\Cache
   */
  public function getCache() {
    return $this->cache;
  }
  
  /**
   * @param string $entityName
   */
  public function setEntityName($entityName) {
    $this->entityName = $this->dc->expandEntityName($entityName);
    return $this;
  }
  
  /**
   * @return string
   */
  public function getEntityName() {
    return $this->entityName;
  }
  
  /**
   * @param Psc\Doctrine\EntityRepository $repository
   */
  public function setRepository(EntityRepository $repository) {
    $this->repository = $repository;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\EntityRepository
   */
  public function getRepository() {
    if (!isset($this->repository)) {
      $this->repository = $this->dc->getRepository($this->entityName);
    }
    
    return $this->repository;
  }
}
?>