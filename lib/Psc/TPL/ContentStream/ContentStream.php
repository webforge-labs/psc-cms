<?php

namespace Psc\TPL\ContentStream;

use Doctrine\Common\Collections\Collection;

interface ContentStream extends Entry {

  const PAGE_CONTENT = 'page-content';
  const SIDEBAR_CONTENT = 'sidebar-content';
  
  public function addEntry(Entry $entry);
  
  public function setEntries(Collection $entries);
  
  public function getEntries();
  
  public function findFirst($type);
  
  public function findAfter(Entry $entry);

  /**
   * Converts the ClassName to a Typename
   * 
   * called from Entry
   * @return string the name of the JS Class without Psc.UI.LayoutManagerComponent.
   */
  public static function convertClassName($classFQN);
}
?>