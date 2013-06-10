<?php

namespace Psc\CMS;

use Psc\Data\Type\Type;
use Psc\CMS\Item\Adapter as ItemAdapter;
use Psc\CMS\Item\MetaAdapter;
use Psc\UI\DataScreener;
use Closure;
use Psc\UI\PanelButtons;
use Psc\CMS\Translation\Container as TranslationContainer;

class EntityGridPanel extends \Psc\CMS\GridPanel {
  
  const TCI_BUTTON     = 0x000001;
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var Psc\CMS\Labeler
   */
  protected $labeler;
  

  public function __construct(EntityMeta $meta, TranslationContainer $translationContainer, $label = NULL, Labeler $labeler = NULL, DataScreener $screener = NULL, $sortable = FALSE) {
    $this->labeler = $labeler ?: new Labeler();
    $this->entityMeta = $meta;
    parent::__construct($label ?: $this->entityMeta->getGridLabel(), $translationContainer, $panelButtons = NULL, $screener, $sortable, $this->entityMeta->getGridRequestMeta(TRUE, $save = TRUE));
  }
  
  protected function doInit() {
    parent::doInit();
    
    $this->getGrid()->addClass('\Psc\entity-grid-panel');
    $this->html->addClass('\Psc\entity-grid-panel-container');
  }
  
  protected function initPanelButtons() {
    parent::initPanelButtons();

    $this->panelButtons->addNewButton(
      $this->entityMeta->getAdapter()->getNewTabButton()
    );

    if ($this->sortable) {
      $this->panelButtons->addSaveButton(PanelButtons::ALIGN_LEFT | PanelButtons::PREPEND)->setLabel('Reihenfolge speichern');
    }
  }
  
  public function addControlColumn() {
    return $this->createColumn('ctrl', Type::create('String'), self::EMPTY_LABEL, array(),
                               function (Entity $entity) {
                                 return \Psc\UI\fHTML::checkbox('egp-ctrl[]', FALSE, $entity->getIdentifier())
                                  ->addClass('\Psc\identifier-checkbox');
                               }
                              );
  }
  
  public function addControlButtons() {
    $this->constructParams['buttons']['open'] = TRUE;
    //$this->constructParams['buttons']['delete'] = TRUE;
  }
  
  /**
   * Fügt ein Property des Entities als Spalte dem Grid hinzu
   *
   * flags:
   *   TCI_BUTTON stellt den Inhalt des Properties in einen Button dar, der das Entity als Button repräsentiert.
   *
   * wenn mit dem Grid Tabs geöffnet werden sollen, muss mindestens ein Property ein TCI_BUTTON flag haben
   * @param bitmap $flags
   * @param Closure $htmlConverter für tci: function ($button, $entity, $property, $entityMeta), normal: function ($propertyValue, $entity, $property)
   * @return Psc\CMS\FormPanelColumn
   */
  public function addProperty($propertyName, $flags = 0x000000, Closure $htmlConverter = NULL) {
    $property = $this->entityMeta->getPropertyMeta($propertyName, $this->labeler);
    
    if ($flags & self::TCI_BUTTON) {
      $entityMeta = $this->entityMeta;
      
      return $this->createColumn($property->getName(), $property->getType(), $property->getLabel(), array($property->getName(),'tci'),
                              function (Entity $entity) use ($property, $entityMeta, $htmlConverter) {
                                $adapter = $entityMeta->getAdapter($entity, EntityMeta::CONTEXT_GRID);
                                $adapter->setButtonMode(\Psc\CMS\Item\Buttonable::CLICK | \Psc\CMS\Item\Buttonable::DRAG);
                                
                                $button = $adapter->getTabButton();
                                
                                if (isset($htmlConverter)) {
                                  return $htmlConverter($button, $entity, $property, $entityMeta);
                                }

                                return $button;
                              }
                             );
    } else {
      $column = $this->createColumn($property->getName(), $property->getType(), $property->getLabel());
      $valueConverter = $this->getDefaultConverter($column);
      $column->setConverter(
        // ein wrapper um den value converter herum
        // somit geben wir den wert des properties des entities an die zelle
        function (Entity $entity) use ($property, $column, $valueConverter, $htmlConverter) {
          if ($htmlConverter) {
            return $htmlConverter($entity->callGetter($property->getName()), $entity, $property);
          } else {
            return $valueConverter($entity->callGetter($property->getName()), $column);
          }
        }
      );
      
      return $column;
    }
  }
  
  /**
   * Fügt mehrere Entities dem Grid hinzu
   */
  public function addEntities($entities) {
    foreach ($entities as $entity) {
      $row = array();
      foreach ($this->columns as $columnName => $column) {
        $converter = $column->getConverter();
        $row[] = $converter($entity, $column);
      }
      $this->addRow($row);
    }
    return $this;
  }
  
  protected function dataToString($value, $columnName) {
    if ($value instanceof \Psc\CMS\Entity) {
      return $value->getContextlabel(EntityMeta::CONTEXT_GRID);
    } else {
      return $this->dataScreener->toString($value, $this->columns->get(array($columnName,'type')));
    }
  }
  
  protected function createTabButton(Entity $entity, $propertyName) {
    //$label = $entity->callGetter($propertyName);
    // wenn wir das nicht machen können wir mit dem context besser das label selbst steuern
    
    //$button->setLabel($label); 
    
    return $button;
  }
  
  /**
   * @param Psc\CMS\EntityMeta $entityMeta
   * @chainable
   */
  public function setEntityMeta(EntityMeta $entityMeta) {
    $this->entityMeta = $entityMeta;
    return $this;
  }

  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }
}
?>