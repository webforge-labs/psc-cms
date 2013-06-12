<?php

namespace Psc\UI;

use Psc\JS\JooseSnippetWidget;

/**
 *
 * use NavigationNodeRepository to create the flat array
 */
class PagesMenu extends \Psc\HTML\JooseBase implements JooseSnippetWidget {
  
  protected $flatNavigationNodes;

  protected $contentStreamTypes = array('page-content');

  protected $locale;
  
  public function __construct(Array $flatNavigationNodes, $locale) {
    $this->flatNavigationNodes = $flatNavigationNodes;
    $this->locale = $locale;
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
        'locale'=>$this->locale,
        'widget'=>$this->widgetSelector(),
        'uiController'=>$this->jsExpr('main.getUIController()'),
        'flat'=>$this->flatNavigationNodes,
        'contentStreamTypes'=>$this->contentStreamTypes
      )
    );
  }

  public function addContentStreamType($name) {
    $this->contentStreamTypes[] = $name;
    return $this;
  }
}
