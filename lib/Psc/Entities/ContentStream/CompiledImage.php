<?php

namespace Psc\Entities\ContentStream;

use Psc\Entities\Image;
use Psc\Image\Manager;
use Psc\TPL\ContentStream\ImageManaging;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledImage extends Entry implements \Psc\TPL\ContentStream\ImageManaging {
  
  /**
   * @var integer
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $url;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $caption;
  
  /**
   * @var string
   * @ORM\Column(nullable=true)
   */
  protected $align;
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $thumbnailFormat = 'content-page';
  
  /**
   * @var Psc\Entities\Image
   * @ORM\ManyToOne(targetEntity="Psc\Entities\Image")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $imageEntity;
  
  /**
   * @var Psc\Image\Manager
   */
  protected $imageManager;
  
  public function __construct($url, $caption = NULL, $align = NULL) {
    $this->setUrl($url);
    if (isset($caption)) {
      $this->setCaption($caption);
    }
    if (isset($align)) {
      $this->setAlign($align);
    }
  }
  
  /**
   * Gibt den Primärschlüssel des Entities zurück
   * 
   * @return mixed meistens jedoch einen int > 0 der eine fortlaufende id ist
   */
  public function getIdentifier() {
    return $this->id;
  }
  
  /**
   * @param mixed $identifier
   * @chainable
   */
  public function setIdentifier($id) {
    $this->id = $id;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }
  
  /**
   * @param string $url
   */
  public function setUrl($url) {
    $this->url = $url;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getCaption() {
    return $this->caption;
  }
  
  /**
   * @param string $caption
   */
  public function setCaption($caption) {
    $this->caption = $caption;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getAlign() {
    return $this->align;
  }
  
  /**
   * @param string $align
   */
  public function setAlign($align) {
    $this->align = $align;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getThumbnailFormat() {
    return $this->thumbnailFormat;
  }
  
  /**
   * @param string $thumbnailFormat
   */
  public function setThumbnailFormat($thumbnailFormat) {
    $this->thumbnailFormat = $thumbnailFormat;
    return $this;
  }
  
  /**
   * @return Psc\Entities\Image
   */
  public function getImageEntity() {
    if (!isset($this->imageEntity)) {
      if (!isset($this->imageManager)) {
        throw new \RuntimeException('ImageManager muss gesetzt sein, bevor html erzeugt wird (getImageEntity)');
      }
      $this->imageEntity = $this->imageManager->load($this->url);
    }
    return $this->imageEntity;
  }
  
  /**
   * @param Psc\Entities\Image $imageEntity
   */
  public function setImageEntity(Image $imageEntity = NULL) {
    $this->imageEntity = $imageEntity;
    if (isset($this->imageManager)) {
      $this->imageManager->load($this->imageEntity);
    }
    return $this;
  }
  
  public function serialize($context) {
    return $this->doSerialize(array('url','caption','align','imageEntity'), array(), $context);
  }
  
  public function getLabel() {
    return 'Bild';
  }
  
  public function html() {
    $img = \Psc\HTML\HTML::tag('img', NULL, array('src'=>$this->getHTMLUrl(), 'alt'=>$this->getLabel()));
    
    if (isset($this->align)) {
      $img->addClass('align'.$this->align);
    }
    
    return $img;
  }
  
  public function getHTMLUrl() {
    return $this->getImageEntity()->getThumbnailUrl($this->getThumbnailFormat());
  }
  
  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager() {
    return $this->imageManager;
  }
  
  /**
   * @param Psc\Image\Manager $imageManager
   */
  public function setImageManager(Manager $imageManager) {
    $this->imageManager = $imageManager;
    if (isset($this->imageEntity)) {
      $this->imageManager->load($this->imageEntity);
    }
    return $this;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledImage';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'id' => new \Psc\Data\Type\IdType(),
      'url' => new \Psc\Data\Type\StringType(),
      'caption' => new \Psc\Data\Type\StringType(),
      'align' => new \Psc\Data\Type\StringType(),
      'thumbnailFormat' => new \Psc\Data\Type\StringType(),
      'imageEntity' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\Image')),
      'imageManager' => new \Psc\Data\Type\ObjectType(new \Psc\Code\Generate\GClass('Psc\\Image\\Manager')),
    ));
  }
}
?>