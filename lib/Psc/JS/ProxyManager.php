<?php

namespace Psc\JS;

class ProxyManager extends \Psc\JS\Manager implements \Psc\HTML\HTMLInterface {
  
  protected $configuration;
  
  public function __construct($name = 'proxy', \Psc\CMS\Configuration $configuration = NULL) {
    parent::__construct($name);
    
    $this->configuration = $configuration ?: \Psc\PSC::getProject()->getConfiguration();
    $this->url = $this->configuration->get('js.url','/js/');
  }
  
  public function getHTML() {
    $html = array();
    foreach ($this->enqueued as $alias) {
      $html[] = Helper::load($this->url.$this->files[$alias]['name'])->setOption('br.closeTag',FALSE);
    }
    
    return $html;
  }
  
  /**
   * @return string
   */
  public function html() {
    return \Webforge\Common\ArrayUtil::join($this->getHTML(),"%s \n");
  }
}
?>