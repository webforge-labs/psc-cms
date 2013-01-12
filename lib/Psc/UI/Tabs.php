<?php

namespace Psc\UI;

use stdClass;
use Psc\JS\Helper AS JSHelper;
use Psc\JS\jQuery AS JSjQuery;

class Tabs extends \Psc\Object {

  /**
   * @var HTMLTag
   */
  protected $html;
  
  protected $tabsIndex = 0;
  protected $prefix;
  
  protected $focusId;
  
  public $options;
  
  protected $init = FALSE;
  
  /**
   * @var FALSE|string wenn string wird dieses als title label für das icon angezeigt
   */
  protected $closeable = FALSE;
  
  public function __construct(Array $options = array()) {
    $this->options = $options;
    
    $this->html = HTML::Tag('div', new stdClass)->addClass('\Psc\tabs');
    $this->html->generateId();
    
    $this->html->content->ul = HTML::Tag('ul', array());
    $this->html->template = "%self%\n%js%";
    
    $this->prefix = 'tab';
  }
  
  public function add($headline, $content = NULL, $contentLink = NULL, $tabName = NULL) {
    if (!isset($tabName)) {
      $tabName = $this->prefix.$this->tabsIndex;
    }
    if (!isset($contentLink)) {
      $contentLink = '#'.$tabName;
    }
    
    $this->html->content->ul->content[] = HTML::Tag('li',
                                                    array(HTML::Tag('a',
                                                              
                                                              HTML::esc($headline),
                                                              
                                                              array('title'=>$tabName,'href'=>$contentLink)
                                                          ),
                                                          ($this->closeable ? '<span class="ui-icon ui-icon-close">'.HTML::esc($this->closeable).'</span>' : NULL)
                                                    )
                                                   );
    if (isset($content))
      $this->html->content->$tabName = HTML::Tag('div', $content, array('id'=>$tabName));
    
    $this->tabsIndex++;
  }
  
  protected function init() {
    if (!$this->init) {
      jQuery::widget($this->html, 'tabs', (array) $this->options);
      
      $this->init = TRUE;
    }
    
    return $this;
  }
  
  public function focus($id) {
    $this->focusId = $id;
    return $this;
  }
  
  /**
   * @return HTMLTag
   */
  public function html() {
    $this->init();
    
    $html = $this->html;
    if (isset($this->focusId)) {
      $html .= JSjQuery::onWindowLoad(sprintf("$('#content #tabs').tabs('select','#%s')", $this->focusId));
    }
    
    return $html;
  }
  
  public function disableJS() {
    $this->html->template = str_replace('%js%','',$this->html->template);
  }
  
  public function __toString() {
    return (string) $this->html();
  }
}
?>