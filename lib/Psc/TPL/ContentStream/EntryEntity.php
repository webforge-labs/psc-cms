<?php

namespace Psc\TPL\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Psc\TPL\ContentStream\Converter AS ContentStreamConverter;
// use Psc\TPL\ContentStream\Context AS ContentStreamContext
use Psc\Code\Code;
use Psc\CMS\AbstractEntity;
use Psc\Doctrine\Entity;
use Closure;

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
    $entityMeta = $entityFactory->getEntityMeta();
    foreach ($data as $property => $value) {
      $propertyMeta = $entityMeta->getPropertyMeta($property);
      $propertyType = $propertyMeta->getType();

      if ($propertyType instanceof \Psc\Data\Type\EntityType) {
        
        if ($propertyType->implementsInterface('Psc\TPL\ContentStream\ContextLoadable')) {
          $objectClass = $propertyType->getGClass()->getFQN();
          $value = $objectClass::loadWithContentStreamContext($value, $converter->getContext());

        } elseif ($propertyType->isCSEntry()) {
          if (is_string($value) && trim($value) === '') {
            $value = NULL;
          } else {
            $serialized = (object) $value;
            $serialized->type = $propertyType->getGClass()->getClassName();
            $value = $converter->unserializeEntry($serialized);
          }
        
        } else {
          throw new \RuntimeException(
            sprintf(
              "Cannot convert EntityType: %s in property '%s' from Entity %s",
              $propertyType->getClassFQN(),
              $property,
              $entityMeta->getClass()
            )
          );
        }
      }

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
  protected function doSerialize(Array $properties, Array $data = array(), $context = NULL) {
    $serialized = array('type'=>$this->getType(),'label'=>$this->getLabel());
    
    foreach ($properties as $property) {
      $value = $this->$property;
      
      if ($value instanceof Entity) {
        if ($value instanceof Entry) {
          $value = $value->serialize($context);
        } else {
          $value = $value->getIdentifier();
        }
      }
      
      $serialized[$property] = $value;
    }
    
    return (object) array_merge($serialized, $data);
  }

  public static function getDiscriminatorMap() {
    return array();
  }
  

  /**
   * @return string the name of the JS Class without Psc.UI.LayoutManagerComponent.
   */
  public function getType() {
    return Code::getClassName($this->getEntityName());
  }

  
  /**
   * @return string
   */
  abstract public function getLabel();

  /**
   * @return stdClass
   */
  public function getTemplateVariables(Closure $exportTemplateEntry) {
    throw new \Psc\Exception('This is not implemented for this class: '.get_class($this));
  }

  
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
