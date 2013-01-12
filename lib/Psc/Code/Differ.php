<?php

namespace Psc\Code;

use PHPUnit_Util_Diff;

class Differ extends \Psc\SimpleObject {
  
  const REMOVED = 2;
  const ADDED = 1;
  const OLD = 0;
  
  public function __construct() {
    
  }
  
  public function diff($actual, $expected) {
    /*
     * every array-entry containts two elements:
     *   - [0] => string $token
     *   - [1] => 2|1|0
     *
     * - 2: REMOVED: $token was removed from $from
     * - 1: ADDED: $token was added to $from
     * - 0: OLD: $token is not changed in $to
     */

    $this->diffs = PHPUnit_Util_Diff::diffToArray($actual, $expected);
    return $this;
  }
  
  public function getAdded() {
    $diffs = array();
    foreach ($this->diffs as $diff) {
      if ($diff[1] === self::ADDED) {
        $diffs[] = $diff[0];
      }
    }
  }
  
  /**
   * Gibt alle Diffs ohne old zurück
   *
   */
  public function getDiffs() {
    $diffs = array();
    foreach ($this->diffs as $diff) {
      if ($diff[1] !== self::OLD) {
        $diffs[] = $diff;
      } 
    }
    return $diffs;
  }
  
  public function getPairs() {
    $pairs = array();
    $pair = NULL;
    foreach ($this->diffs as $key =>$diff) {
      if ($diff[1] === self::OLD)
        continue;
      
      if ($diff[1] === self::REMOVED) {
        $pair = array($diff[0]);
      } elseif ($diff[1] === self::ADDED && $pair) {
        $pair[] = $diff[0];
        $pairs[] = $pair;
        $pair = NULL;
      } elseif ($diff[1] === self::ADDED && !$pair) {
        $pairs[] = array(NULL, $diff[0]);
        //print_r($this->getDiffs());
        //throw new \Psc\Exception(sprintf('[%d] not matching to pair: %s', $key, $diff[0]));
      }
      
    }
    return $pairs;
  }
  
  /**
   * Gibt alle Diffs (inklusive old) zurück
   */
  public function getAllDiffs() {
    return $this->diffs;
  }
}
?>