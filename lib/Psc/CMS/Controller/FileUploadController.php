<?php

namespace Psc\CMS\Controller;

use Psc\CMS\UploadManager;
use Psc\Net\ServiceResponse;
use Psc\Net\Service;
use Webforge\Common\System\File;
use Psc\Doctrine\DCPackage;
use stdClass;

/**
 * 
 */
class FileUploadController extends \Psc\SimpleObject {
  
  const IF_NOT_EXISTS = 1;
  
  /**
   * @var Psc\CMS\UploadManager
   */
  protected $manager;
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  public function __construct(DCPackage $dc, UploadManager $uploadManager = NULL) {
    $this->setDoctrinePackage($dc);
    
    // der Default für diesen UploadManager ist Project::getFiles()->sub('uploads/')
    $this->setUploadManager($uploadManager ?: new UploadManager(NULL, $this->dc));
  }
  
  /**
   * @controller-api
   * @return Psc\CMS\UploadableFile
   */
  public function getFile($idOrHash, $filename = NULL) {
    $uplFile = $this->manager->load($idOrHash);
    
    if (isset($filename)) {
      $uplFile->setDownloadFilename($filename);
    }
    
    return new ServiceResponse(Service::OK, $uplFile, ServiceResponse::SOME_FILE);
  }
  
  /**
   * 
   * specification.description die Beschreibung der Datei (optional)
   *
   * wenn bestehende Dateien erneut hochgeladen werden (durch hash) wird der dateiname der uploaded file upgedated!
   * @param \Webforge\Common\System\File am besten eine \Psc\System\UploadedFile
   * @controller-api
   */
  public function insertFile(File $file, stdClass $specification) {
    $description = isset($specification->description) ? $specification->description : NULL;
    $uplFile = $this->manager->store($file, $description, UploadManager::IF_NOT_EXISTS | UploadManager::UPDATE_ORIGINALNAME);
    $uplFile->setDescription($description); // update, falls es die datei schon gab
    $this->manager->flush();
    
    return new ServiceResponse(Service::OK, $uplFile, ServiceResponse::JSON_UPLOAD_RESPONSE);
  }
  
  
  /**
   * Returns a list of all uploaded files
   *
   * @return array
   */
  public function getFiles(Array $criteria = array(), Array $orderBy = NULL) {
    $files = new \Psc\Data\ArrayCollection(
      $this->manager->getRepository()->findBy($criteria, $orderBy)
    );
    
    foreach ($files as $file) {
      $this->manager->attach($file);
    }
    
    return $files;
  }
  
  /**
   * @param Psc\CMS\UploadManager $uploadManager
   */
  public function setUploadManager(UploadManager $uploadManager) {
    $this->manager = $uploadManager;
    return $this;
  }
  
  /**
   * @return Psc\CMS\UploadManager
   */
  public function getUploadManager() {
    return $this->manager;
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
}
?>