<?php

namespace Psc\Image;

use Psc\PSC,
    Psc\Image\Manager,
    Entities\Image,
    Psc\System\File
;

/**
 * @group Imagine
 * @group class:Psc\Image\Manager
 */
class ManagerTest extends \Psc\Doctrine\DatabaseTest {

  public function configure() {
    $this->con = 'tests';
    parent::configure();
  }
  
  public function setUp() {
    parent::setUp();
    
    PSC::getProject()->getModule('Hitch')->bootstrap();
    PSC::getProject()->getModule('Imagine')->bootstrap();
    
    if (!$this->tableExists('images')) {
      $this->createEntitySchema('Image');
    }
    $this->loadFixtures(array('images'));
  }
  
  public function assertPreConditions() {
    $this->assertRowsNum('Image',0);
    
    $manager = new Manager('\Entities\Image', $this->em);
    
    $dir = PSC::get(PSC::PATH_FILES)->append('images');
    $this->assertEquals($dir, $manager->getDirectory());
    
    $dir->wipe();
    $this->assertEquals(0, count($dir->getContents()), 'Verzeichnis ist nicht leer, obwohl gerade geleert');
  }
  
  public function testConstruct() {
    $manager = new Manager('\Entities\Image', $this->em);
    
    // da wir den Manager mit keinem Parameter aufrufen, ist base/files/images unser originalDir
    $originals = PSC::get(PSC::PATH_FILES)->sub('images/');
    
    // und base/cache/images unser Cache-Dir
    $this->assertInstanceOf('Psc\Data\FileCache',$manager->getCache(),'Test nur für Filecache gemacht');
    $cache = PSC::get(PSC::PATH_CACHE)->sub('images/');
    
    $img1 = new File('image1.jpg', PSC::get(PSC::PATH_TESTDATA)->append('images'));
    $img4 = new File('image4.jpg', PSC::get(PSC::PATH_TESTDATA)->append('images'));
    $img10 = new File('image10.jpg', PSC::get(PSC::PATH_TESTDATA)->append('images'));
    
    $imagine = new \Imagine\GD\Imagine();
    $imagineImage1 = $imagine->open((string) $img1);
    $imagineImage4 = $imagine->open((string) $img4);
    $imagineImage10 = $imagine->open((string) $img10);

    $image1 = $manager->store($imagineImage1);
    $image4 = $manager->store($imagineImage4);
    $image10 = $manager->store($imagineImage10);
    
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
    
    /* löschen test */
    $image1->remove();
    $this->em->flush();
    
    $this->assertFileNotExists((string) $image1->getSourceFile());
    
    /* thumbnailing */
    $iiThumb4 = $image4->getThumbnail(104, 97);
    $iiThumb4 = $image4->getThumbnail(104, 97);
  }
  
  public function testOtherOSPathinDB() {
    $manager = new Manager('\Entities\Image', $this->em);
    
    $imagine = new \Imagine\GD\Imagine();
    $image = $manager->store($imagine->load(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAAbCAIAAAAyOnIjAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEBJREFUeNo8w9sNwCAIBdC7/3T82g0Q5GEXMJDWkxwQEZi5zzlvEfkqVP8La1WDWXW41+gRNXvmvvd+MZ5xBBgAnedKkCs1gtkAAAAASUVORK5CYII=')));
    $manager->flush();
    
    $iname = 'ssljdlfj.png';
    $image->setSourcePath('./q/'.$iname);
    
    $originals = PSC::get(PSC::PATH_FILES)->sub('images/');
    $this->assertEquals((string) new File($originals->sub('q'),$iname), (string) $manager->getSourceFile($image));
  }
}
?>