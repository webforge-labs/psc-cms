<?php

namespace Psc\Entities\ContentStream;

class ImageTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\Entities\\ContentStream\\Image';
    parent::setUp();

    $this->image = new Image('/sourcepath/in/db');

    $this->imageManager = $this->getMock('Psc\Image\Manager', array(), array(), '', FALSE);
    $this->imageEntity = $this->getMock('Psc\Entities\Image', array(), array(), '', FALSE);
  }

  public function testImageIsInstanceOfImageManaging() {
    $this->assertInstanceOf('Psc\TPL\ContentStream\ImageManaging', $this->image);
  }

  public function testInterfaceImplForsetImageManager() {
    $this->image->setImageManager($this->imageManager);
    // this is not the interface, but we want it
    $this->assertSame($this->imageManager, $this->image->getImageManager());
  }

  public function testImageEntityIsAttachedToManagerSomehowWhileGettingOrSetting() {
    $this->imageManager->expects($this->once())->method('load')->with($this->equalTo($this->imageEntity));


    $this->image->setImageEntity($this->imageEntity);
    $this->image->setImageManager($this->imageManager);

    $this->assertSame($this->imageEntity, $this->image->getImageEntity());
  }

  public function testImageEntityIsAttachedToManagerSomehowWhileSetOrGetImageManager() {
    $this->imageManager->expects($this->once())->method('load')->with($this->equalTo($this->imageEntity));

    $this->image->setImageManager($this->imageManager);
    $this->image->setImageEntity($this->imageEntity);

    $this->assertSame($this->imageEntity, $this->image->getImageEntity());
  }

  public function testDBDataTraversion_ImageEntityCanBeLoadedWithJustURL() {
    $this->image = new Image('/sourcepath/in/db');
    $this->image->setImageManager($this->imageManager);

    $this->imageManager->expects($this->once())->method('load')
      ->with($this->equalTo($this->image->getUrl()))
      ->will($this->returnValue($this->imageEntity));

    $this->assertSame($this->imageEntity, $this->image->getImageEntity());
  }

  public function testHTMLUrlReturnsThumbNailUrl() {
    $this->imageEntity->expects($this->once())->method('getThumbnailUrl');
    $this->image->setImageEntity($this->imageEntity);

    $this->image->getHTMLUrl();
  }
}
