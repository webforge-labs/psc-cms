<?php

namespace Psc\CMS;

use \Psc\Config,
    \Psc\PSC,
    \Psc\HTML\HTML,
    \Psc\TPL\TPL
;

/**
 * @deprecated use Psc\HTML\FrameworkPage
 */
class HTMLPage extends \Psc\HTML\Page {
  
  public function __construct(\Psc\JS\Manager $jsManager = NULL, \Psc\CSS\Manager $cssManager = NULL, $projectAbbrev = NULL) {
    parent::__construct($jsManager, $cssManager);
    
    $projectAbbrev = $projectAbbrev ?: PSC::getProject()->getLowerName();
    
    
    /* Projekt CSS Files */
    $this->cssManager->register($projectAbbrev.'.css',$projectAbbrev,array('jquery-ui'));
    
    /* Enqueue defaults */
    $this->cssManager->enqueue('default');
    $this->cssManager->enqueue('jquery-ui');
    $this->cssManager->enqueue($projectAbbrev);
    $this->cssManager->enqueue('cms.form');
    $this->cssManager->enqueue('cms.ui');
    
    $this->setUp();
  }
  
  protected function setUp() {
  }

  /**
   * @deprecated denn irgendwie ist das hier fehl am platz (siehe \Psc\CMS\CMS )
   */
  public function addMarkup(CMS $cms, $vars = array()) {
    // deprecated
    $this->body->content = TPL::get(array('CMS','main'),
                                          array_merge(
                                            array('page'=>$this,
                                            'authController'=>$cms->getAuthController(),
                                            'user'=>$cms->getUser(),
                                            'tabs'=>$cms->getContentTabs(),
                                            'cms'=>$cms,
                                            'dropContents'=>$cms->getDropContents()
                                          ), $vars)
                                        );

  }
}
