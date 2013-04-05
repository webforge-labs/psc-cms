<?php

namespace Psc\Entities\ContentStream;

use Psc\Entities\NavigationNode;
use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CompiledTeaserHeadlineImageTextLink extends Entry {
  
  /**
   * @var string
   * @ORM\Column
   */
  protected $headline;
  
  /**
   * @var Psc\Entities\ContentStream\Image
   * @ORM\ManyToOne(targetEntity="Psc\Entities\ContentStream\Image")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $image;
  
  /**
   * @var string
   * @ORM\Column(type="text")
   */
  protected $text;
  
  /**
   * @var Psc\Entities\NavigationNode
   * @ORM\ManyToOne(targetEntity="Psc\Entities\NavigationNode")
   * @ORM\JoinColumn(onDelete="SET NULL")
   */
  protected $link;
  
  public function __construct($headline, Image $image = NULL, $text, NavigationNode $link = NULL) {
    $this->setHeadline($headline);
    if (isset($image)) {
      $this->setImage($image);
    }
    $this->setText($text);
    if (isset($link)) {
      $this->setLink($link);
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
    return $this->doSerialize(array('headline','image','text','link'), array(), $context);
  }
  
  public function getLabel() {
    return 'TeaserHeadlineImageTextLink';
  }
  
  public function html() {
    return TeaserHeadlineImageTextLink;
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\CompiledTeaserHeadlineImageTextLink';
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'headline' => new \Psc\Data\Type\StringType(),
      'image' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\ContentStream\\Image')),
      'text' => new \Psc\Data\Type\MarkupTextType(),
      'link' => new \Psc\Data\Type\EntityType(new \Psc\Code\Generate\GClass('Psc\\Entities\\NavigationNode')),
    ));
  }
}
?>