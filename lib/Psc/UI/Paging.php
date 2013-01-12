<?php

namespace Psc\UI;

use Psc\Code\Code;
use Psc\GPC;
use stdClass;
use Psc\Code\Callback;

/**
 * Simple Paging: kann eine Menge von Objekten konkret in Chunks schneiden die man "pro Seite" sehen würde
 *
 * etwas schöner wäre hier noch, wenn diese Paging-Kalkulations-Logik ausgelagert werden könnte.
 * manchmal braucht man einfach nur total pages, current page anhand von total items und einer page nummer, etc (siehe SearchWidget von Comun)
 */
class Paging extends HTMLTag implements \Countable {
  
  const FIRST = 'first';
  const LAST = 'last';
  const CURRENT = 'current';
  const NEXT = 'next';
  const PREVIOUS = 'previous';
  
  const START = 'start';

  /**
   * Die Werte des Pagings
   * @var array
   */
  protected $pages = array(1);
  
  
  /**
   * @var int die aktuelle Seite
   */
  protected $page = NULL;
  
  
  /**
   * @var int >= 1
   */
  protected $per;
  
  /**
   * Die anzahl dieser Objects muss nicht zwingend total gleich sein
   */
  protected $objects = array();
  
  /**
   * @var int Anzahl der Objekte ingesamt die das Paging umfasst (nicht Anzahl der Seiten)
   */
  protected $total = 0;
  
  
  protected $widgetOptions;
  
  protected $init = FALSE;

  /**
   * Erstellt ein neues Paging mit der Range von Seitenzahlen
   * 
   * @param Array die Menge von Elementen
   * @param int $perPage wieviele von den Elementen sollen auf jeder Seite angezeigt werden
   */
  public function __construct($perPage = 10) {
    if ($perPage <= 0) {
      throw new \Psc\Exception('perPage muss >= 1 sein');
    }
    $this->per = (int) $perPage; // nur setter benutzen, damit wir nicht 2 mal calculatePages() machen
    $this->widgetOptions = new stdClass();
    
    parent::__construct('div','', array('id'=>'paging'));
    $this->addClass('\Psc\paging');
  }
  
  
  protected function calculatePages() {
    $n = max(1,ceil($this->total / $this->per));
    
    
    $this->setPagesNum($n);

/*
if($pages <= 5) {
	$pageStart = 0;
	$pageEnd = $pages;
}
else {
	$pageStart = min($pages - 5, max(0, $page - 2));
	$pageEnd = min($pages, $pageStart + 5);	
}
*/
    
  }
  
  public function html() {
    if (!$this->init) {
      $o = $this->widgetOptions;
      $o->pages = count($this->pages);
      $o->currentPage = $this->getCurrent();
      
      $this->init = TRUE;   // once, oder immer?
    }
    
    jQuery::widget($this, 'paging', (array) $this->widgetOptions);
    return parent::html();
  }
  
  public function setTotal($num) {
    $num = (int) $num;
    
    if ($this->total != $num) {
      $this->total = (int) $num;
      $this->calculatePages();
    }
  }
  
  protected function setPagesNum($pagesNum) {
    if ($pagesNum < 1) {
      throw new \InvalidArgumentException('pagesNum muss größer gleich 1 sein');
    }
    
    $this->pages = range(1,(int) $pagesNum);
  }
  
  public function getPagesNum() {
    return count($this->pages);
  }
  
  public function hasPages() { return count($this->pages) > 1; }

  /**
   * Gibt die Seitenzahl vor der aktuellen zurück
   * 
   * Ist die Seitenzahl die erste, wird nur NULL zurückgegeben
   * @return NULL|int
   */  
  public function getPreviousPage() {
    if (isset($this->page) && $this->page > 1) {
      return $this->page-1;
    } elseif ($this->page == 1) {
      return NULL;
    } else {
      return 1;
    }
  }

  /**
   * Gibt die Seitenzahl nach der aktuellen zurück
   * 
   * Ist die Seitenzahl die letzte, wird nur NULL zurückgegeben
   * @return NULL|int
   */  
  public function getNextPage() {
    if (isset($this->page) && $this->page < count($this->pages)) {
      return $this->page+1;
    } elseif($this->page == count($this->pages)) {
      return NULL;
    } else {
      return count($this->pages);
    }
  }
  
  /**
   * Gibt die URL für eine Seite zurück
   * $page kann FIRST|LAST|NEXT|PREVIOUS|CURRENT oder NULL (aktuelle) oder eine Pagenummer sein
   */
  public function getURL($page = NULL) {
    $page = $this->getPage($page);
    
    if ($page != NULL) {
      if (isset($_SERVER['QUERY_STRING']))
        return '/?'.$_SERVER['QUERY_STRING'].'&p='.$page;
      else
        return '/?p='.$page;
    }
  }
  
  public function getPageLabel($page = NULL) {
    $page = $this->getPage($page);
    
    if ($page != NULL) {
      return $page; // das ist einfach die Simple Zahl
    }
  }
  
  /**
   * Gibt die Seite für eine Seite zurück
   * 
   * $page kann FIRST|LAST|NEXT|PREVIOUS|CURRENT oder NULL (aktuelle) oder eine Pagenummer sein
   * @return XXX?
   */
  public function getPage($page = NULL ) {
    if ($page == self::FIRST) $page = 1;
    if ($page == self::LAST) $page = $this->count($this->pages);
    if ($page == self::NEXT) $page = $this->getNextPage();
    if ($page == self::PREVIOUS) $page = $this->getPreviousPage();
    if ($page == self::CURRENT) $page = $this->page;
    
    if (!$this->hasPage($page))
      return NULL;
    else
      return $page;
  }
  
  
  public function hasPage($page) {
    return in_array($page,$this->pages);
  }
    
  
  /**
   * Setzt die aktuelle Seite
   * 
   * Wenn ohne Parameter aufgerufen, holt es sich den $_GET parameter aus der Umgebung
   */
  public function setPage($p = NULL) {
    if (!isset($p)) {
      $p = $this->getActivePage();
    } elseif($p >= 1) {
      $p = (int) $p;
    } else {
      $p = 1;
    }

    $this->page = $p;
    return $this;
  }
  
  /**
   * Holt die aktive Seite von irgendwo her (standardmäßig von Get)
   *
   * kann von ableitenten Klassen leicht überschrieben werden, ohne dass setPage() modifiziet werden muss
   * diese Funktion wird nie im Frontend benutzt sondern dort immer getPage()
   * @return int >= 1
   */
  protected function getActivePage() {
    if (($p = GPC::GET('p')) != NULL && $p >=1) {
      return (int) $p;
    } else {
      return 1;
    }
  }


  /**
   * 
   * @return int
   */
  public function getCurrent() {
    return $this->page;
  }
  
  /**
   * Gibt das Limit z.b. für ein SQL statement zurück
   * @return list(start,length)
   */
  public function getLimit($page = NULL) {
    if ($page == NULL) $page = $this->page;
    return array(max(0,$page-1) * $this->per,$this->per);
  }
  
  public function getPerPage() {
    return $this->per;
  }
  
  public function count() {
    return count($this->pages);
  }
  
  public function setPerPage($per) {
    if ($per <= 0) {
      throw new Exception('Per Page muss >= 1 sein');
    }
    
    if ($this->per != $per) {
      $this->per = $per;
      
      $this->calculatePages();
    }
  }
  
  public function __toString() {
    return $this->html();
  }
  
  
  
  /* Funktionen die mit den Internen Objekten zu tun haben */
  
  /**
   * Gibt die Objekte zurück die sich auf der angegebenen Page befinden
   */
  public function getObjectsOn($page = NULL) {
    $page = $this->getPage($page);
    
    list($start,$limit) = $this->getLimit($page);
    
    $objects = array();
    // array slice geht leider nicht, da es nicht die wirklichen schlüssel des arrays nimmt, sondern diese vorher neu sortiert
    for ($x = 0; $x < $limit; $x++) {
      if (array_key_exists(($start+$x),$this->objects))
        $objects[] = $this->objects[$start+$x];
    }
    return $objects;
  }
  
    /**
   * Setzt die Objekte des Pagings
   *
   * Wird nur ein Teil der Objekte des Pagings geladen (normalfall), kann mit $offset
   * der erste Indes für das erste Objekt angegeben werden.
   *
   * Dies ist meist der erste Parameter von getLimit()
   *
   * $paging = new Paging();
   * $paging->setPerPage(2);
   * $paging->setPage(2);
   * $paging->setTotal(8);
   * list($start, $length) = $paging->getLimit(); // list(2,2)
   *
   * $paging->setObjects(array('eins','zwei'));
   * $paging->getObjectsOn(Paging::CURRENT); // array('eins','zwei')
   *
   * im Gegensatz zu
   * $paging->setObjects(array('eins','zwei'),0);
   * $paging->getObjectsOn(Paging::CURRENT); // array()
   */
  public function setObjects(Array $objects, $offset = self::START) {
    if ($offset == self::START) list($offset,$NULL) = $this->getLimit();
    
    $i = 0;
    foreach ($objects as $object) {
      $this->objects[$offset+$i] = $object;
      $i++;
    }
    
    return $this;
  }
  
  
  /**
   * Gibt alle Pages des Paging als Label zurück
   *
   * Die Werte sind die Labels mit getPageLabel()
   * Die Schlüssel sind die Nummern der Seiten
   * @return array
   */
  public function getPages() {
    $pages = array();
    foreach ($this->pages as $page) {
      $pages[$page] = $this->getPageLabel($page);
    }
    return $pages;
  }

  
  /**
   * Gibt die Seite zurück auf der sich das angegebene Objekt befindet
   */
  public function getPageFor($object) {
    $key = array_search($object, $this->objects);
    
    if ($key !== FALSE) {
      /* $key ist jetzt der Index eines Elements in Object (0 basierend)
         die Seite auf der dieses Objekt ist z. B. 
         
         per = 5
         
         1 2 3 4 5 => 1
         6 7 8 9 T => 2
      */
      $page = (int) ceil(($key+1) / $this->per);
      
      if (!$this->hasPage($page)) {
        throw new \Psc\Exception('Internal Calculation Error: '.$page.' not in Collection');
      }
      return $page;
    }
    
    return NULL;
  }


  /**
   * Gibt das HTML des Paging Mithilfe von kleinen Snippets zurück
   *
   * Für mehr sophisticated tasks, sollte man getPagesWithCallback() benutzen
   * 
   * $htmlPage, $htmlActivePage und $htmlGlue bekommen jeweils mit sprintf folgende Parameter:
   * %1$s   die URL der Page
   * %2$s   das Label der Page
   * %3$s   die Nummer der Page
   *
   * @param $htmlPage das HTML welches für eine normale (nicht aktive) Seite benutzt wird
   * @param $htmlActivePage das HTML welches für die aktive Page benutzt wird
   * @param $htmlGlue das HTML welches zwischen den Pages stehen soll
   * @return string
   */
  public function getPagesWithHTMLTemplate($htmlPage, $htmlActivePage, $htmlGlue) {
    $html = NULL;
    $activePage = $this->getPage(self::CURRENT);
    foreach ($this->pages as $page) {
      if ($page === $activePage)
        $html .= sprintf($htmlActivePage,$this->getUrl($page),$this->getPageLabel($page),$page);
      else
        $html .= sprintf($htmlPage,$this->getUrl($page),$this->getPageLabel($page),$page);
      
      $html .= sprintf($htmlGlue,$this->getUrl($page),$this->getPageLabel($page),$page);
    }
    $html = mb_substr($html,0,-mb_strlen($htmlGlue));
    return $html;
  }
  
  /**
   * Gibt das Paging HTML mit einer Callbackfunktion zurück
   *
   * der Callback hat folgende Parameter:
   *
   * function callback_paging(UIPaging $paging, $page, $pageLabel, $pageURL, $isActivePage, $isFirstPage, $isLastPage) {
   * 
   * };
   *
   * @return array eine Liste von Rückgabewerten des callbacks mit der page als Schlüssel
   */
  public function getPagesWithCallback(Callback $cb) {
    $html = array();
    $activePage = $this->getPage(self::CURRENT);
    $firstPage = $this->getPage(self::FIRST);
    $lastPage = $this->getPage(self::LAST);
    
    foreach ($this->pages as $page) {
      $html[$page] = $cb->call(array(
        $this,
        $page,
        $this->getPageLabel($page),
        $this->getUrl($page),
        $page === $activePage,
        $page === $firstPage,
        $page === $lastPage
      ));
    }
    return $html;
  }
}
?>