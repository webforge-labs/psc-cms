<?php

namespace Psc\JS;

class RequirejsManager extends \Psc\JS\Manager implements \Psc\HTML\HTMLInterface {
  
  protected $configuration;
  protected $main;
  
  public function __construct($name = 'requirejs', $main = '/js/boot.js') {
    parent::__construct($name);
    $this->main = $main;
  }
  
  public function getHTML() {
    $html = array();
    
    $html[] = Helper::load('/psc-cms-js/vendor/require.js')
                ->setAttribute('data-main', $this->main)
                ->setOption('br.closeTag',FALSE);
    
    foreach ($this->enqueued as $alias) {
      $html[] = Helper::load($this->files[$alias]['name'])->setOption('br.closeTag',FALSE);
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