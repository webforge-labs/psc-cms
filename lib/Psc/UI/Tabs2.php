<?php

namespace Psc\UI;

use stdClass;
use Psc\TPL\TPL;
use Psc\TPL\Template;
use Psc\CMS\TabsContentItem2 as TabsContentItem;
use Psc\CMS\Item\TabOpenable;
use Psc\CMS\Item\Exporter;
use Psc\JS\Helper as jsHelper;

/**
 * @TODO wir könnten noch mehr markup schon so wie im ui.css machen (mehr klassen) damit das "umspringen" nicht so krass ist (wenn js geladen wurde)
 *
 * ooder:
 * wir bauen eine preloader fürs gesamte cms
 */
class Tabs2 extends \Psc\HTML\WidgetBase {

  protected $tabsIndex = -1;
  protected $tab0Template = NULL;
  
  protected $prefix = 'tab-content';
  protected $closeable = 'Tab schließen';
  
  /**
   * Welcher Index soll beim Laden ausgewählt werden?
   */
  protected $select;
  
  /**
   * @var Psc\CMS\Item\Exporter
   */
  protected $exporter;
  
  public function __construct(Array $options = array(), Template $tab0Template) {
    $this->tab0Template = $tab0Template;

    parent::__construct('tabs', $options);
    $this->exporter = new Exporter();
  }
  
  protected function doInit() {
    $this->html = HTML::Tag('div', new stdClass)->addClass('\Psc\tabs');
    $this->html->content->ul = HTML::Tag('ul', array(), array('class'=>'ui-tabs-nav'));
    $this->html->templateContent = new stdClass();
    $this->html->templateContent->select = NULL;
    $this->html->templateAppend("\n%select%");
    
    if (count($this->widgetOptions) > 0) { // wir initialisieren nicht, wenn PHP eh keine Settings hat
      parent::doInit();
    }
    
    if (isset($this->tab0Template)) {
      $this->add($this->tab0Template->__('title'), $this->tab0Template->get(), NULL, NULL, FALSE); // nicht schließbar
    }
  }
  
  /**
   * Fügt ein TabsContentItem (nur V2) den Tabs hinzu
   *
   * der Content wird per Ajax geladen mit der URL die das TabsContentItem angibt
   */
  public function addItem(TabsContentItem $item) {
    if ($item instanceof \Psc\Doctrine\Entity && \Psc\PSC::getProject()->isDevelopment()) {
      throw new \Psc\DeprecatedException('Entities übergeben ist deprecated. Psc\CMS\Item\Adapter benutzen.');
    }
    return $this->add($item->getTabsLabel(TabsContentItem::LABEL_TAB), NULL, $item->getTabsURL(), HTML::string2id(implode('-',$item->getTabsId())));
  }
  
  /**
   * Fügt ein TabOpenable den Tabs hinzu
   *
   */
  public function addTabOpenable(TabOpenable $item) {
    $rm = $item->getTabRequestMeta();
    return $this->add($item->getTabLabel(), NULL, $rm->getUrl(), $this->exporter->convertToId($item));
  }
  
  /**
   * Fügt einen Tab hinzu
   *
   * Es kann alles angegebenwerden (Low-Level)
   */
  public function add($headline, $content = NULL, $contentLink = NULL, $tabName = NULL, $closeable = NULL) {
    $this->init();
    $this->tabsIndex++;
    
    if (!isset($closeable)) {
      $closeable = $this->closeable;
    }
    
    if (!isset($tabName)) {
      $tabName = $this->prefix.$this->tabsIndex;
    }
    if (!isset($contentLink)) {
      $contentLink = '#'.$tabName;
    }
    
    $this->html->content->ul->content[] =
        HTML::Tag('li',
          array(
                HTML::Tag('a',
                          HTML::esc($headline),
                          array('title'=>$tabName,'href'=>$contentLink)
                          ),
                // das muss in sync mit Psc.UI.Tabs.js sein
                ($closeable ? '<span class="ui-icon ui-icon-gear options"></span>' : NULL), // schon auch closeable weil in den options "close" ist
                ($closeable ? '<span class="ui-icon ui-icon-close">'.HTML::esc($closeable).'</span>' : NULL)
          )
        )->setGlueContent('%s');
        
    if (isset($content))
      $this->html->content->$tabName = HTML::Tag('div', $content, array('id'=>$tabName));
    
    return $this;
  }
  
  /**
   * Gibt den letzten eingefügten Index zurück
   */
  public function getIndex() {
    return $this->tabsIndex;
  }
  
  /**
   * Wählt den Index aus, der direkt nach dem Laden der Seite geladen werden soll
   *
   * dies initialisiert das widget auch auf Javascript-Basis
   */
  public function select($index) {
    $this->init();
    
    $this->select = $index;
    $this->html->getTemplateContent()->select =
      jsHelper::embed(
        jsHelper::bootLoad(
          array('app/main'),
          array('main'),
          sprintf("var tabs = main.getTabs(), tab = tabs.tab({index: %d});\n    tabs.select(tab);", $this->select)
        )
      )
    ;
    return $this;
  }
}
?>