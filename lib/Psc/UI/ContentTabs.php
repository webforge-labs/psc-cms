<?php

namespace Psc\UI;

use Psc\CMS\TabsContentItem;
use Psc\CMS\TabsContentItem2;
use Psc\TPL\TPL;

class ContentTabs extends Tabs {
  
  public function __construct(Array $options = array(), \Psc\TPL\Template $welcome = NULL) {
    parent::__construct($options);
    
    $this->getHTML()->setAttribute('id','tabs');
    $this->setPrefix('content');
    $this->init();
    $this->disableJS();

    if (\Psc\PSC::inProduction()) {
      TPL::install('welcome');
    }
    $this->add('Willkommen', $welcome ? $welcome->get() : TPL::get('welcome')); // nicht schließbar

    $this->setCloseable('Tab schließen');
  }

  public function addItem($item) {
    \Psc\Doctrine\Helper::assertCTI($item);
    $this->add($item->getTabsLabel(), NULL, $item->getTabsURL(), implode('-',$item->getTabsId()));
  }

}
