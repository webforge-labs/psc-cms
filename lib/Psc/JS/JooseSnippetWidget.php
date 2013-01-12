<?php

namespace Psc\JS;

/**
 * Ein Widget (normalerweise Psc\HTML\JooseBase) welches mit einem JooseSnippet geladen wird (nicht mit dem alten autoloading)
 *
 * dies ist das migrations-ziel-widget für alle widgets die JooseBase ableiten
 * denn somit können klassen die das widget benutzen mit getJooseSnippet() die ladereihenfolge bestimmen, oder besser verschachteln
 */
interface JooseSnippetWidget {
  
  /**
   * @return Psc\JS\JooseSnippet */
  public function getJooseSnippet();
  
  /**
   * Prevents the Widget to attach javascript to initialize to its html
   */
  public function disableAutoLoad();
}
?>