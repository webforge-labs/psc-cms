<?php

namespace Psc\CMS;

use Psc\Code\Code;
use Psc\Code\Event\Event;
use Psc\Data\Set;
use Psc\Data\SetMeta;
use Psc\URL\Helper AS URLHelper;

use Psc\Data\Type\EntityType;
use Psc\Data\Type\PersistentCollectionType;
use Psc\Data\ObjectExporter;

/**
 * Entity-Base-Class für alle CMS High-Level Funktionen
 *
 */
abstract class AbstractEntity extends \Psc\Doctrine\AbstractEntity implements Entity {
  
  public function getContextLabel($context = EntityMeta::CONTEXT_DEFAULT) {
    return $this->getEntityLabel(); // Blöder, technischer, default
  }

  /**
   * @see Psc\CMS\Item\Buttonable
   */
  public function getButtonRightIcon($context = self::CONTEXT_DEFAULT) {
    return NULL;
  }
  
  /**
   * @see Psc\CMS\Item\Buttonable
   */
  public function getButtonLeftIcon($context = self::CONTEXT_DEFAULT) {
    return NULL;
  }

  public function export() {
    $meta = new SetMeta();
    foreach ($this->getSetMeta()->getTypes() as $property => $type) {
      // @TODO schön wäre eigentlich auf ebene 2 immer nur "entity:id" zu exportieren
      // das müsste dann alle relevanten daten für die response haben
      if ($type instanceof EntityType || $type instanceof PersistentCollectionType) {
        continue;
      }
      $meta->setFieldType($property, $type);
    }

    $set = new Set(array(), $meta);
    foreach ($set->getKeys() as $property) {
      $set->set($property, $this->callGetter($property));
    }
    
    $exporter = new ObjectExporter();
    return $exporter->walkWalkable($set);
  }  
}
