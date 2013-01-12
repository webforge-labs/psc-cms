<?php

namespace Psc\Data;

use Closure;
use Psc\Doctrine\Helper as DoctrineHelper;
use Doctrine\Common\Collections\Collection;

class ArrayCollection extends \Doctrine\Common\Collections\ArrayCollection implements \Psc\JS\JSON, Exportable {
  
  /**
   * Compare-Function: Objects
   *
   * geht davon aus, dass alle Elemente der Collections objekte sind
   * Identität: $o1 === $o2
   * Reihenfolge: strcasecmp(spl_object_hash($o1), spl_object_hash($o2));
   */
  const COMPARE_OBJECTS = 'compare_objects';

  /**
   * Compare-Function: Comparable (nur Objects)
   *
   * geht davon aus, dass alle Elemente der Collections Objekte sind und sie die Funktion equals() haben
   * Identität: $o1->compareTo($o2) === 0
   * Reihenfolge: $o1->compareTo($o2)
   * siehe \Doctrine\Common\Comparable
   */
  const COMPARE_COMPARABLE = 'compare_comparable';
  
  /**
   * Compare-Function: toString
   *
   * geht davon aus, dass alle Elemente der Collections in strings umgewandelt werden können. Und diese Strings eine Reihenfolge bilden
   * Identität: (string) $i1 === (string) $i2
   * Reihenfolge: strcasecmp((string) $i1, (string) $i2); // nicht ganz sicher welches strcmp das hier ist
   */
  const COMPARE_TOSTRING = 'compare_tostring';
  
  public function exportJSON() {
    return array_map(function ($element) {
                      if ($element instanceof \Psc\JS\JSON) {
                        return $element->exportJSON();
                      } elseif($element instanceof \Psc\Data\Exportable) {
                        return $element->export();
                      } elseif(is_array($element)) {
                        return array_map(function ($object) {
                                          return $object->exportJSON();
                                        },
                                         $element
                                        );
                      } elseif (is_string($element) || is_numeric($element) || is_bool($element) || is_float($element) || is_null($element)) {
                        return $element;
                      } else {
                        throw new \Psc\Exception('Kann keine komplexen Objekte in einer Collection exportieren');
                      }
                     },
                     $this->toArray()
                    );
  }
  
  public function export() {
    return $this->exportJSON();
  }
  
  public function JSON() {
    return json_encode($this->exportJSON());
  }
  
  protected function getJSONFields() { // ist eher so zu Interface zwecken da
    return array();
  }
  
  /**
   * Gibt alle Elemente zurück, die in dieser Collection, aber nicht in $collection sind
   *
   * man müsste also die elemente dieser Ausgabe von $this entfernen, um zu $collection gleich zu sein
   *
   * die $compareFunction bekommt 2 Parameter: $item1 und $item2. Diese sind aus $collection und $this
   * Bei Gleichheit muss sie 0 zurückgeben
   * ist $item1 in der Reihenfolge vor $item2 muss sie 1 zurückgeben ansonsten -1
   * Wird die Reihenfolge falsch ermittelt ist das Ergebnis des Diffs falsch!
   *
   * für self::COMPARE_OBJECTS wird nach spl_object_hash() compared und sortiert (das ist dann === für objekte, geht jedoch nicht für basis-werte)
   * für self::COMPARE_TOSTRING ist die Gleichheit (string) $item1 === (string) $item2
   * 
   * @return ArrayCollection eine Liste der Diffs. Schlüssel bleiben nicht erhalten
   */
  public function deleteDiff(Collection $collection, $compareFunction) {
    return new ArrayCollection(array_merge(
      $this->difference($this->toArray(), $collection->toArray(), $compareFunction)
    ));
  }

  /**
   * Gibt alle Elemente zurück, die nicht in dieser Collection, aber in $collection sind
   *
   * man müsste also die elemente dieser Ausgabe zu $this hinzufügen, um zu $collection gleich zu sein
   * @see deleteDiff()
   * @return ArrayCollection
   */
  public function insertDiff(Collection $collection, $compareFunction) {
    return new ArrayCollection(array_merge(
      $this->difference($collection->toArray(), $this->toArray(), $compareFunction)
    ));
  }
  
  protected function difference(Array $c1, Array $c2, $compareFunction) {
    if ($compareFunction === self::COMPARE_TOSTRING) {
      return array_diff($c1, $c2);
    } else {
      $compareFunction = $this->compileCompareFunction($compareFunction);
      
      return array_udiff($c1, $c2, $compareFunction);
    }
  }
  
  protected function compileCompareFunction($compareFunction) {
    if ($compareFunction === self::COMPARE_TOSTRING) {
      $compareFunction = function ($item1, $item2) {
        $s1 = (string) $item1;
        $s2 = (string) $item2;
        if ($s1 === $s2) return 0;
        return strcasecmp($s1, $s2) > 0 ? 1 : -1;
      };
    
    } elseif ($compareFunction === self::COMPARE_COMPARABLE) {
      $compareFunction = function ($object1, $object2) {
        return $object1->compareTo($object2);
      };
    
    } elseif ($compareFunction === self::COMPARE_OBJECTS) {
      $compareFunction = function ($item1, $item2) {
        if ($item1 === $item2) return 0;

        $s1 = spl_object_hash($item1);
        $s2 = spl_object_hash($item2);
        return strcasecmp($s1, $s2) > 0 ? 1 : -1;
      };
    }
    
    return $compareFunction;
  }

  /**
   * Vergleicht die Collections auf identische Inhalte
   *
   * Die Inhalte 2er Collections sind identisch, für alle Elemente an der Position $x der collections gilt:
   *   $compareFunction($this[$x], $collection[$x]) === 0
   *   
   * die angegebene $compareFunction kann unabhängig von einer Reihenfolge der Elemente sein. (nur Ausgabe === 0 ist wichtig)
   * @return bool
   */
  public function isSame(Collection $collection, $compareFunction) {
    // ungleiche Anzahl ist nie gleich => fastcheck
    if (count($this) !== count($collection)) return FALSE;
    
    // Anzahl ist gleich, d. h. es sollte keine Elemente geben, die an einem Index nicht dem anderen Array identisch sind
    $compareFunction = $this->compileCompareFunction($compareFunction);
    
    foreach ($this as $key => $element) {
      if (($otherElement = $collection->get($key)) === NULL) {
        return FALSE;
      }
      
      if ($compareFunction($element, $otherElement) !== 0) {
        return FALSE;
      }
    }
    
    return TRUE;
  }
  
  /**
   * Vergleicht die Inhalte zweier Collections
   *
   * die Schlüssel der Elemente sind nicht relevant (anders als bei isSame)
   * @return bool
   */
  public function isEqual(Collection $collection, $compareFunction) {
    // ungleiche Anzahl ist nie gleich => fastcheck
    if (count($this) !== count($collection)) return FALSE;

    if (count($this->insertDiff($collection, $compareFunction)) > 0) return FALSE;
    if (count($this->deleteDiff($collection, $compareFunction)) > 0) return FALSE;
    
    return TRUE;
  }
  

  /**
   * Berechnet updates / inserts / deletes (komplettes diff) 2er Collections
   *
   * Natürlich ist es auch möglich diese mit insertDiff/deleteDiff/Intersect zu berechnen. Diese Funktion ist aber schneller und benötigt dafür nur ein uniqueHash-Kriterium (welches standardmäßig der spl_object_hash ist).
   * D. h. im DefaultFall funkioniert diese Funktion nur mit Objekten
   * die $uniqueHashFunction bekommt ein argument und sollte ein int / string (irgendwas für einen array-key-geeignetes) zurückgeben
   *
   * diese Variante ist ca 10-25% schneller als 2 diffs und intersect
   * @return list(ArrayCollection $inserts, ArrayCollection $updates, ArrayCollection $deletes) 
   */
  public function computeCUDSets(Collection $collection, $compareFunction, $uniqueHashFunction = NULL) {
    $compareFunction = $this->compileCompareFunction($compareFunction);
    if (!isset($uniqueHashFunction)) {
      $uniqueHashFunction = function ($o) {
        return spl_object_hash($o);
      };
    }
    
    $updates = array();
    $deletes = array();
    $intersect = array();
    $collection = $collection->toArray();
    usort($collection, $compareFunction); // aufsteigend sortieren
    foreach ($this->toArray() as $key => $item) {
      
      $found = FALSE;
      foreach ($collection as $other) {
        if (($r = $compareFunction($item, $other)) === 0) {
          $updates[] = $item;
          $intersect[$uniqueHashFunction($item)] = TRUE;
          $found = TRUE;
          break;
        } elseif ($r === -1) { // wir müssen nicht weiter prüfen, da ja $collection aufsteigend sortiert ist
          break;
        }
      }
      
      if (!$found) { // jedes element welches nicht in $collection ist, wurde gelöscht
        $deletes[] = $item;
      }
    }
    
    $inserts = array();
    foreach ($collection as $other) {
      // jedes elemente welches in collection ist, aber nicht in $this, wurde eingefügt
      if (!array_key_exists($uniqueHashFunction($other), $intersect)) {
        $inserts[] = $other;
      }
    }
    
    return array(new ArrayCollection($inserts), new ArrayCollection($updates), new ArrayCollection($deletes));
  }
  
  /**
   * @param Closure $criteria
   * default: values
   * @TODO andere Parameter: string, VALUES, KEYS, array()?
   */
  public function sortBy($criteria) {
    if ($criteria instanceof Closure) {
      $elements = $this->getElements();
      usort($elements, $criteria);
      $this->setElements($elements);
    }
    return $this;
  }
  
  public function setElements(Array $elements) {
    // so ein .... weil doctrine die variable private gemacht hat
    $this->clear();
    foreach ($elements as $key=>$value) {
      $this->set($key, $value);
    }
    return $this;
  }
  
  public function getElements() {
    return $this->toArray();
  }

  /* Benchmark-Varianten computeCUDSets:
  */
  //public function computeCUDSets1(Collection $collection, $compareFunction) {
  //  $compareFunction = $this->compileCompareFunction($compareFunction);
  //  
  //  $inserts = $this->insertDiff($collection, $compareFunction);
  //  $deletes = $this->deleteDiff($collection, $compareFunction);
  //  $updates = new ArrayCollection(array_merge(array_uintersect($this->toArray(), $collection->toArray(), $compareFunction)));
  //  
  //  return array($inserts, $updates, $deletes);
  //}
  //
  //public function computeCUDSets2(Collection $collection, $compareFunction) {
  //  $compareFunction = $this->compileCompareFunction($compareFunction);
  //  
  //  $updates = array();
  //  $deletes = array();
  //  foreach ($this->toArray() as $key => $item) {
  //    
  //    $found = FALSE;
  //    foreach ($collection as $other) {
  //      if ($compareFunction($item, $other) === 0) {
  //        $updates[] = $item;
  //        $found = TRUE;
  //        break;
  //      }
  //    }
  //    
  //    if (!$found) {
  //      $deletes[] = $item;
  //    }
  //  }
  //  
  //  $inserts = $this->insertDiff($collection, $compareFunction);
  //  
  //  return array($inserts, new ArrayCollection($updates), new ArrayCollection($deletes));
  //}
  //
  //public function computeCUDSets3(Collection $collection, $compareFunction) {
  //  $compareFunction = $this->compileCompareFunction($compareFunction);
  //  
  //  $updates = array();
  //  $deletes = array();
  //  $intersect = array();
  //  foreach ($this->toArray() as $key => $item) {
  //    
  //    $found = FALSE;
  //    foreach ($collection as $other) {
  //      if ($compareFunction($item, $other) === 0) {
  //        $updates[] = $item;
  //        $intersect[spl_object_hash($item)] = TRUE;
  //        $found = TRUE;
  //        break;
  //      }
  //    }
  //    
  //    if (!$found) {
  //      $deletes[] = $item;
  //    }
  //  }
  //  
  //  $inserts = array();
  //  foreach ($collection as $item) {
  //    if (!array_key_exists(spl_object_hash($item), $intersect)) {
  //      $inserts[] = $item;
  //    }
  //  }
  //  
  //  return array(new ArrayCollection($inserts), new ArrayCollection($updates), new ArrayCollection($deletes));
  //}
}
?>