<?php

namespace Psc\CMS\Controller;

use Psc\Image\Manager;

/**
 * @group class:Psc\CMS\Controller\ImageController
 */
class ImageControllerTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $imageCtrl;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\ImageController';
    $this->con = 'tests';
    parent::setUp();
    $manager = new Manager('Psc\Entities\Image', $this->em);
    $this->imageCtrl = new ImageController($manager);      
    
    $this->bud = $manager->store($manager->createImagineImage($this->getFile('img1.jpg')), 'bud', Manager::IF_NOT_EXISTS);
    $this->terence = $manager->store($manager->createImagineImage($this->getFile('img2.jpg')), 'terence', Manager::IF_NOT_EXISTS);
    $manager->flush();
  }
  
  public function testGetImageReturnsTheImageEntityQueriedById() {
    $this->assertSame($this->bud, $this->imageCtrl->getImage($this->bud->getIdentifier()));
  }
  
  public function testGetImageReturnsTheImageEntityQueriedByHash() {
    $hashImage = $this->imageCtrl->getImage($this->terence->getHash());
    $this->assertSame($this->terence, $hashImage);
  }
  
  public function testInsertImageFileReturnsImageEntity() {
    $file = \Psc\System\File::createTemporary();
    $file->writeContents($this->getFile('img1.jpg')->getContents());
    
    $image = $this->imageCtrl->insertImageFile(
      $file,
      (object) array('specification','not','yet','specified') // yagni
    );
    
    $this->assertSame($this->bud, $image);
  }
  
  public function testImageConversionToResponseHasUsableURLInIt() {
    $export = $this->bud->export();
    $this->assertObjectHasAttribute('url', $export);
    $this->assertNotEmpty($export->url);
  }
}
?>