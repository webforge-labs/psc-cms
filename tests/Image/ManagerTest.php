<?php

namespace Psc\Image;

use Psc\PSC,
    Psc\Image\Manager,
    Psc\Entities\Image,
    Psc\System\File
;

/**
 * @group Imagine
 * @group class:Psc\Image\Manager
 */
class ManagerTest extends \Psc\Doctrine\DatabaseTestCase {

  protected $manager;

  public function setUp() {
    $this->con = 'tests';
    // leere fixture für images
    parent::setUp();
    
    $this->manager = new Manager('Psc\Entities\Image', $this->em);
    $this->imagine = new \Imagine\GD\Imagine;
  }
  
  protected function resetDirectory() {
    // reset physical files
    $dir = PSC::get(PSC::PATH_FILES)->append('images');
    $this->assertEquals($dir, $this->manager->getDirectory());
    $dir->wipe();    
  }
  
  public function testConstruct() {
    $this->resetDirectory();
    // da wir den Manager mit keinem Parameter aufrufen, ist base/files/images unser originalDir
    $originals = PSC::get(PSC::PATH_FILES)->sub('images/');
    
    // und base/cache/images unser Cache-Dir
    $this->assertInstanceOf('Psc\Data\FileCache', $this->manager->getCache(),' Test nur für Filecache gemacht');
    $cache = PSC::get(PSC::PATH_CACHE)->sub('images/');
    
    $img1 = new File('image1.jpg', PSC::get(PSC::PATH_TESTDATA)->append('images'));
    $img4 = new File('image4.jpg', PSC::get(PSC::PATH_TESTDATA)->append('images'));
    $img10 = new File('image10.jpg', PSC::get(PSC::PATH_TESTDATA)->append('images'));
    
    $imagine = new \Imagine\GD\Imagine();
    $imagineImage1 = $imagine->open((string) $img1);
    $imagineImage4 = $imagine->open((string) $img4);
    $imagineImage10 = $imagine->open((string) $img10);

    $image1 = $this->manager->store($imagineImage1);
    $image4 = $this->manager->store($imagineImage4);
    $image10 = $this->manager->store($imagineImage10);
    
    $this->em->flush();
    $this->assertRowsNum('Image',3);
    
    $this->assertNotEmpty($image1->getSourcePath());
    $this->assertNotEmpty($image4->getSourcePath());
    $this->assertNotEmpty($image10->getSourcePath());

    $this->assertFileExists((string) $image1->getSourceFile());
    $this->assertTrue($image1->getSourceFile()->getDirectory()->isSubdirectoryOf($originals));
    $this->assertFileExists((string) $image4->getSourceFile());
    $this->assertTrue($image4->getSourceFile()->getDirectory()->isSubdirectoryOf($originals));
    $this->assertFileExists((string) $image10->getSourceFile());
    $this->assertTrue($image10->getSourceFile()->getDirectory()->isSubdirectoryOf($originals));
    
  }
  
  public function testRemoveImageDeletesThePhysicalFileAsWell() {
    $image1 = $this->manager->load(1);
    
    $this->manager->remove($image1);
    $this->em->flush();
    
    $this->assertFileNotExists((string) $image1->getSourceFile());
  }
  
  public function testThumbnailAcceptance() {
    $image2 = $this->manager->load(2);
    
    /* thumbnailing */
    $iiThumb4 = $image2->getThumbnail(104, 97);
    $iiThumb4 = $image2->getThumbnail(104, 97);
  }
  
  public function testOtherOSPathinDB() {
    $imagine = new \Imagine\GD\Imagine();
    $image = $this->manager->store($imagine->load(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAAbCAIAAAAyOnIjAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEBJREFUeNo8w9sNwCAIBdC7/3T82g0Q5GEXMJDWkxwQEZi5zzlvEfkqVP8La1WDWXW41+gRNXvmvvd+MZ5xBBgAnedKkCs1gtkAAAAASUVORK5CYII=')));
    $this->manager->flush();
    
    $iname = 'ssljdlfj.png';
    $image->setSourcePath('./q/'.$iname);
    
    $originals = PSC::get(PSC::PATH_FILES)->sub('images/');
    $this->assertEquals(
      (string) new File($originals->sub('q'),$iname),
      (string) $this->manager->getSourceFile($image)->resolvePath()
    );
  }

  public function testManagerCanStoreAnImage() {
    $image = $this->manager->store($this->im('img1.jpg'), $title = 'my nice title');
    
    $this->assertInstanceOf('Psc\Image\Image', $image);
    $this->resetDatabaseOnNextTest();
  }
  
  public function testManagerCanLoadStoredImage() {
    $image = $this->manager->store($this->im('img1.jpg'), $label = 'my nice title');
    $this->manager->flush();
    
    $id = $image->getIdentifier();
    
    $this->manager->clear(); // macht auch em clear
    
    $imageDB = $this->manager->load($id);
    
    $this->assertEquals($image->getSourcePath(), $imageDB->getSourcePath());
    $this->assertEquals($image->getHash(), $imageDB->getHash());
    $this->assertEquals($label, $imageDB->getLabel());
    $this->resetDatabaseOnNextTest();
  }
  
  public function testManagerStorAnAlreadyStoredImage() {
    $image1 = $this->manager->store($this->im('img1.jpg'), 'label');
    $this->manager->flush();
    $this->manager->clear();
    
    $image2 = $this->manager->store($this->im('img1.jpg'), 'label', Manager::IF_NOT_EXISTS);
    $this->manager->flush();
    
    $this->assertEquals($image1->getHash(), $image2->getHash());
    $this->assertEquals($image1->getSourcePath(), $image2->getSourcePath());
  }
    
  /**
   * @expectedException PDOException
   */
  public function testManagerStoresAnAlreadyStoredImageWithUniqueConstraint() {
    $this->resetDatabaseOnNextTest();
    
    // unique constraint muss verletzt werden:
    $image2 = $this->manager->store($this->im('img1.jpg'), 'label');
    $this->manager->flush();
  }
  
  protected function im($name) {
    return $this->imagine->open($this->getFile('img1.jpg'));
  }
}
?>