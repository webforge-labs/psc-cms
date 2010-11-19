<?php

class FluidGrid_Navigation extends FluidGrid_Model {

  /**
   * 
   * @var array
   */
  protected $nav;
  
  /* 
   *
   * <code>
   * FluidGrid_Grid::factory(new FluidGrid_Navigation(
   *                             Array(
   *                                 array('name'=>'Fluid 12-column', 'url'=>'../../../12/',
   *                                       'subnav'=>array(
   *                                           array('name'=>'MooTools', 'url'=>'../../../12/fluid/mootools/'),
   *                                           array('name'=>'jQuery', 'url'=>'../../../12/fluid/jquery/'),
   *                                           array('name'=>'none', 'url'=>'../../../12/fluid/none/'),
   *                                           ),
   *                                     ),
   *                                 array('name'=>'Fluid 16-column', 'url'=>'../../../16/',
   *                                       'subnav'=>array(
   *                                           array('name'=>'MooTools', 'url'=>'../../../16/fluid/mootools/'),
   *                                           array('name'=>'jQuery', 'url'=>'../../../16/fluid/jquery/'),
   *                                           array('name'=>'none', 'url'=>'../../../16/fluid/none/'),
   *                                           ),
   *                                     ),
   *                                 )
   *                             ))->setWidth(16)
   * </code>
   */
  public function __construct(Array $nav) {
    $this->nav = $nav;
  }


  public function getContent() {

    $nav1 = FluidGrid_Collection::factory()->setModelClass('FluidGrid_HTMLElement');
    foreach ($this->nav as $nav1Item) {
      
      $link = $this->getLink($nav1Item);

      $nav2 = FluidGrid_Collection::factory()->setModelClass('FluidGrid_HTMLElement');
      if (isset($nav1Item['subnav']) && is_array($nav1Item['subnav'])) {
        foreach ($nav1Item['subnav'] as $nav2Item) {
          $nav2->addItem(FluidGrid_HTMLElement::factory('li',$this->getLink($nav2Item)));
        }
      }
      
      $nav1->addItem(FluidGrid_HTMLElement::factory('li',new FluidGrid_Collection($link,
                                                                                  FluidGrid_HTMLElement::factory('ul',$nav2)
                                                        )));
    }

    $ulMain = FluidGrid_HTMLElement::factory('ul',
                                             $nav1
        )
        ->setClass('nav')
        ->setClass('main')
        ->setLevel($this->level+1);

    return $ulMain;
  }

  protected function getLink(Array $navItem) {
    return FluidGrid_HTMLElement::factory('a',new FluidGrid_String($navItem['name']),array('href'=>$navItem['url']));
  }

  public function __toString() {
    try {
      return (string) $this->getContent();
    } catch (Exception $e) {
      print $e;
    }
  }
}

?>