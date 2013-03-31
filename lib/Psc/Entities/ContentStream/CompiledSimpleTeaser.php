<?php

namespace Psc\Entities\ContentStream;

use Psc\Entities\NavigationNode;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledSimpleTeaser extends Entry {
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $headline;
  
  /**
   * @var string
   * @ORM\Column(type="text")
   */
  protected $text;
  
  /**
   * @var Psc\Entities\ContentStream\Image
   * @ORM\OneToOne(targetEntity="Psc\Entities\ContentStream\Image")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $image;
  
  /**
   * @var Psc\Entities\NavigationNode
   * @ORM\OneToOne(targetEntity="Psc\Entities\NavigationNode")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $link;
  
  public function __construct($headline, $text = NULL) {
    $this->setHeadline($headline);
    if (isset($text)) {
      $this->setText($text);
    }
  }
  
  /**
   * @return string
   */
  public function getHeadline() {
    return $this->headline;
  }
  
  /**
   * @param string $headline
   */
  public function setHeadline($headline) {
    $this->headline = $headline;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getText() {
    return $this->text;
  }
  
  /**
   * @param string $text
   */
  public function setText($text) {
    $this->text = $text;
    return $this;
  }
  
  /**
   * @return Psc\Entities\ContentStream\Image
   */
  public function getImage() {
    return $this->image;
  }
  
  /**
   * @param Psc\Entities\ContentStream\Image $image
   */
  public function setImage(Image $image = NULL) {
    $this->image = $image;
    return $this;
  }
  
  /**
   * @return Psc\Entities\NavigationNode
   */
  public function getLink() {
    return $this->link;
  }
  
  /**
   * @param Psc\Entities\NavigationNode $link
   */
  public function setLink(NavigationNode $link = NULL) {
    $this->link = $link;
    return $this;
  }
  
  public function serialize($context) {
    return $this->doSerialize(array('headline','text','link','image'));
  }
  
  public function getLabel() {
    return 'Normaler Teaser';
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledSimpleTeaser';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'headline' => new \Psc\Data\Type\StringType(),
      'text' => new \Psc\Data\Type\MarkupTextType(),
      'image' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\Image')),
      'link' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\NavigationNode')),
    ));
  }
}
?>