<?php

namespace Psc\TPL\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Psc\TPL\ContentStream\Converter AS ContentStreamConverter;
// use Psc\TPL\ContentStream\Context AS ContentStreamContext
use Psc\Code\Code;
use Psc\CMS\AbstractEntity;

/**
 * (at)ORM\Entity(repositoryClass="ACME\Entities\ContentStream\EntryRepository")
 * (at)ORM\Table(name="cs_entries")
 * (at)ORM\InheritanceType("JOINED")
 * (at)ORM\DiscriminatorColumn(name="discr", type="string")
 * (at)ORM\DiscriminatorMap({"headline" = "Headline", "image" = "Image", "paragraph" = "Paragraph", "li" = "Li", "download" = "Download", "downloadslist" = "DownloadsList", "websitewidget" = "WebsiteWidget"})
 *
 * warum hatte ich mich hier entschieden CTI zu benutzen?
 * warum bauche ich den cs_entries table?: wegen Sort
 */
abstract class EntryEntity extends AbstractEntity implements \Psc\HTML\HTMLInterface, \Psc\TPL\ContentStream\Entry {
  
  /**
   * The Context while creating HTML
   * 
   * if you want to access this you have to implement the ContextAware Interface
   * @var ContentStreamContext
   */
  protected $context;
  
  public static function unserialize(\stdClass $data, $entityFactory, ContentStreamConverter $converter) {
    foreach ($data as $property => $value) {
      $entityFactory->set($property, $value);
    }
    
    return $entityFactory->getEntity();
  }

  
  /**
   * @return array
   */
  abstract public function serialize($context);
  

  /**
   * @return Entries[]
   */
  protected function doSerialize(Array $properties, Array $data = array()) {
    $serialized = array('type'=>$this->getType(),'label'=>$this->getLabel());
    
    foreach ($properties as $property) {
      $value = $this->$property;
      
      if ($value instanceof \Psc\Doctrine\Entity) {
        $value = $value->getIdentifier();
      }
      
      $serialized[$property] = $value;
    }
    
    return (object) array_merge($serialized, $data);
  }
  

  /**
   * @return string the name of the JS Class without Psc.UI.LayoutManagerComponent.
   */
  public function getType($classFQN) {
    return Code::getClassName($this->getEntityName());
  }

  
  /**
   * @return string
   */
  abstract public function getLabel();
  
  
  /**
   * @param ContentStreamContext $context
   * @chainable
   */
  public function setContext($context) {
    $this->context = $context;
    return $this;
  }

  /**
   * @return ContentStreamContext
   */
  public function getContext() {
    return $this->context;
  }
}