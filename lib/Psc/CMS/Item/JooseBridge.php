<?php

namespace Psc\CMS\Item;

use Psc\HTML\Tag;
use Psc\Code\Code;
use Psc\Code\Generate\GClass;

/**
 * 
 * eine Bridge zu JS könnte dann so aussehen
 * 
 * <button class="psc-cms-ui-button guid-2739xb3"></button>
 * 
 * jQuery('button.guid-2739xb3').data('joose', new Psc.CMS.Item({
 *     traits: [Alle Interfaces die das Item implementiert],
 *       tab: {
 *         id: 'entities-person-17-form',
 *         url: '/entities/person/17/form',
 *         label: 'Person Nummer 17'
 *       },
 *       ac: {
 *         label: '[#17] Person mit vollen Namen. (Groß, blondhaarig)'
 *       },
 *       button: {
 *         label: 'Button Person #17',
 *         fullLabel: 'Der volle Name der Person Nummer 17, die auch sonstige Informationen hat, die einfach zu lang zum anzeigen sind'
 *       },
 *       identifier: 17,
 *       entityName: 'article'
 *   })
 *   );
 * 
 * Die Roles/Traits in Joose lesen dann die Daten aus dem initializer aus und bridgen zu Psc.UI.* JS-Jooseklassen oder Sonstiges
 */
class JooseBridge extends \Psc\HTML\JooseBase implements \Psc\JS\JooseSnippetWidget {
  
  public function __construct($item) {
    $this->item = $item;
    parent::__construct('Psc.CMS.FastItem');
  }
  
  /**
   * @var Psc\CMS\Item\xxxxxxxxxxxable
   */
  protected $item;
  
  /**
   * @var Psc\CMS\Item\Exporter
   */
  protected $exporter;
  
  
  public function link(Tag $tag) {
    $this->html = $tag;
    return $this;
  }
  
  /**
   * Attached alle Traits/Roles zum Item
   */
  protected function doInit() {
    $this->autoLoadJoose($this->getJooseSnippet());
  }

  public function getJooseSnippet() {
    $exportedTraits = array();
    $params = array();
    
    foreach ($this->getItemTraits() as $traitName => $subTraits) {
      $trait = 'Psc\CMS\Item\\'.$traitName;

      if (!array_key_exists($trait, $exportedTraits)) {
        $params = array_merge($params, $this->getExporter()->$traitName($this->item));
        
        // alle interfaces die dieser trait hatte, müssen wir nicht mehr exportieren
        foreach ($subTraits as $exportedTrait) {
          $exportedTraits[$exportedTrait] = TRUE;
        }
      }
    }
    $params['widget'] = $this->widgetSelector();
    
    return $this->createJooseSnippet(
      $this->jooseClass, $params
    );
  }
  
  protected function getItemTraits() {
    // das ist lahm wie hulle:
    
    //$itemClass = GClass::factory(Code::getClass($this->item));
    //$itemTraits = $itemClass->getInterfaces();
    //if (count($itemTraits) === 0) {
    //  throw new \InvalidArgumentException($itemClass.' hat keine Traits für die JooseBridge!');
    //}

    // kann mit dem Test erzeugt werden
    $avaibleInterfaces = Array(
      'ComboDropBoxable' => array('Psc\\CMS\\Item\\SelectComboBoxable','Psc\\CMS\\Item\\Identifyable','Psc\\CMS\\Item\\AutoCompletable','Psc\\CMS\\Item\\DropBoxButtonable','Psc\\CMS\\I
    tem\\Buttonable','Psc\\CMS\\Item\\TabOpenable'),
      'DropBoxButtonable' => array('Psc\\CMS\\Item\\TabOpenable','Psc\\CMS\\Item\\Buttonable','Psc\\CMS\\Item\\Identifyable'),
      'SelectComboBoxable' => array('Psc\\CMS\\Item\\AutoCompletable','Psc\\CMS\\Item\\Identifyable'),
      'GridRowable' => array('Psc\\CMS\\Item\\TabOpenable','Psc\\CMS\\Item\\EditButtonable','Psc\\CMS\\Item\\Buttonable','Psc\\CMS\\Item\\DeleteButtonable','Psc\\CMS\\Item\\Identifyabl
    e','Psc\\CMS\\Item\\Deleteable','Psc\\CMS\\Item\\Patchable'),
      'RightContentLinkable' => array('Psc\\CMS\\Item\\TabLinkable','Psc\\CMS\\Item\\TabOpenable','Psc\\CMS\\Item\\Identifyable'),
      'Searchable' => array('Psc\\CMS\\Item\\AutoCompletable','Psc\\CMS\\Item\\Identifyable','Psc\\CMS\\Item\\TabOpenable'),
      'AutoCompletable' => array('Psc\\CMS\\Item\\Identifyable'),
      'Patchable' => array(),
      'NewButtonable' => array(),
      'NewTabButtonable' => array('Psc\\CMS\\Item\\TabButtonable','Psc\\CMS\\Item\\TabOpenable','Psc\\CMS\\Item\\Buttonable'),
      'DeleteButtonable' => array('Psc\\CMS\\Item\\Deleteable','Psc\\CMS\\Item\\Identifyable','Psc\\CMS\\Item\\Buttonable'),
      'EditButtonable' => array('Psc\\CMS\\Item\\TabOpenable','Psc\\CMS\\Item\\Buttonable'),
      'TabButtonable' => array('Psc\\CMS\\Item\\Buttonable','Psc\\CMS\\Item\\TabOpenable'),
      'TabLinkable' => array('Psc\\CMS\\Item\\TabOpenable'),
      'TabOpenable' => array(),
      'Buttonable' => array(),
      'Deleteable' => array('Psc\\CMS\\Item\\Identifyable'),
      'Identifyable' => array(),
    );

    $itemTraits = array();
    foreach ($avaibleInterfaces as $traitName => $subTraits) {
      $trait = 'Psc\CMS\Item\\'.$traitName;
      if ($this->item instanceof $trait) {
        $itemTraits[$traitName] = $subTraits;
      }
    }
    
    return $itemTraits;
  }
  
  /**
   * @param Psc\CMS\Item\Exporter $exporter
   */
  public function setExporter(Exporter $exporter) {
    $this->exporter = $exporter;
    return $this;
  }
  
  /**
   * @return Psc\CMS\Item\Exporter
   */
  public function getExporter() {
    if (!isset($this->exporter)) {
      $this->exporter = new Exporter();
    }
    return $this->exporter;
  }
  
  /**
   * @param mixed $item
   * @chainable
   */
  public function setItem($item) {
    $this->item = $item;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getItem() {
    return $this->item;
  }
}
?>