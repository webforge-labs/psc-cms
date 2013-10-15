<?php

namespace Psc\JS;

use Webforge\Common\DeprecatedException;

/**
 * @deprecated
 */
class RequirejsManager extends \Psc\JS\Manager implements \Psc\HTML\HTMLInterface {
  
  protected $configuration;
  protected $main;
  protected $requirejsSource;
  
  public function __construct($name = 'requirejs', $main = '/js/boot.js', $requirejsSource = '/psc-cms-js/vendor/require.js') {
    if (\Psc\PSC::getProject()->isDevelopment()) {
      throw new DeprecatedException('Dont use this, use HTML\Frameworkpage');
    }

    parent::__construct($name);
    $this->requirejsSource = $requirejsSource;
    $this->main = $main;
  }
  
  public function getHTML() {
    $html = array();
    
    $html[] = Helper::load($this->requirejsSource)
                ->setAttribute('data-main', $this->main)
                ->setOption('br.closeTag',FALSE);
    
    foreach ($this->enqueued as $alias) {
      $html[] = Helper::load($this->files[$alias]['name'])->setOption('br.closeTag',FALSE);
    }
    
    return $html;
  }

  /**
   * Sets the path to require.js
   * 
   * otherwise /psc-cms-js/vendor/require.js is used
   */
  public function setRequirejsSource($url) {
    $this->requirejsSource = $url;
    return $this;
  }
  
  /**
   * @return string
   */
  public function html() {
    return \Webforge\Common\ArrayUtil::join($this->getHTML(),"%s \n");
  }
}
