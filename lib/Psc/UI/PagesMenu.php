<?php

namespace Psc\UI;

use Psc\JS\JooseSnippetWidget;

/**
 *
 * use NavigationNodeRepository to create the flat array
 */
class PagesMenu extends \Psc\HTML\JooseBase implements JooseSnippetWidget {
  
  protected $flatNavigationNodes;
  
  public function __construct(Array $flatNavigationNodes) {
    $this->flatNavigationNodes = $flatNavigationNodes;
  }
  
  protected function doInit() {
    $this->html = HTML::tag('div', NULL, array('class'=>'pages-menu'));
    
    $this->autoLoadJoose(
      $this->getJooseSnippet()
    );
  }
  
  public function getJooseSnippet() {
    return $this->createJooseSnippet(
      'Psc.UI.PagesMenu', Array(
        'widget'=>$this->widgetSelector(),
        'uiController'=>$this->jsExpr('main.getUIController()'),
        'flat'=>$this->flatNavigationNodes
      )
    );
  }
}
?>