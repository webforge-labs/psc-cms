<?php

namespace Psc\TPL\ContentStream;

use Doctrine\Common\Collections\Collection;

interface ContentStream {
  
  public function addEntry(Entry $entry);
  
  public function setEntries(Collection $entries);
  
  public function getEntries();
  
  public function findFirst($type);
  
  public function findAfter(Entry $entry);
}
?>