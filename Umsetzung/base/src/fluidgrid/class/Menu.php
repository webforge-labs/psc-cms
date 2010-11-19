<?php

class FluidGrid_Menu extends FluidGrid_Model {

  /**
   * 
   * @var array
   */
  protected $menu;

  public function __construct(Array $menu) {
    $this->menu = $menu;
  }


  public function getContent() {
    
    $ulMenu = FluidGrid_HTMLElement::factory('ul')
        ->addClass('section')
        ->addClass('menu')
        ->setLevel($this->level+1)
        ->setContent(new FluidGrid_Collection());

    foreach ($this->menu as $section) {
      
      $link = $this->getSectionLink($section);

      $subMenu = FluidGrid_HTMLElement::factory('ul')
          ->addClass('submenu')
          ->setContent(new FluidGrid_Collection());
      
      if (isset($section['subnav']) && is_array($section['subnav'])) {
        foreach ($section['subnav'] as $item) {
          $subMenu->content->addItem(FluidGrid_HTMLElement::factory('li',$this->getLink($item)));
        }
      }
      
      
      $ulMenu->content->addItem(FluidGrid_HTMLElement::factory('li',new FluidGrid_Collection($link,
                                                                                             $subMenu
                                                                   )));
    }

    return $ulMenu;
  }
  
  protected function getSectionLink(Array $item) {
    return FluidGrid_HTMLElement::factory('a',new FluidGrid_String($item['name']),array('class'=>'menuitem'));
  }

  protected function getLink(Array $item, $active = FALSE) {
    $a = FluidGrid_HTMLElement::factory('a',new FLuidGrid_String($item['name'],array('url'=>$item['url'])));
    if ($active) $a->addClass('active');
    return $a;
  }

}

?>