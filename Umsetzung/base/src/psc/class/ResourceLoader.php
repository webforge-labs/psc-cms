<?php
/**
 * Die Klasse lädt alle Resource Files die im Psc-Framework benutzt werden
 * 
 * Diese Klasse ersetzt require_once, include usw.
 * dadurch darf sie leider auch nur in purem PHP geschrieben sein, da sie auch vom AutoLoader benutzt wird
 */
class ResourceLoader extends Object {
  
  
  /**
   * Gibt den Pfad zu einer Datei zurück
   * 
   * Ist kein ModulName angegeben wird im src path gesucht.
   * ist $file ein array wird der letzte bestandteil als dateiname interpretiert
   * $file hat nie die endung php
   * <code>
   * getPath('ctrl', 'home/article');
   * getPath('view', 'home/welcome');
   * </code>
   * @param string $fileNamespace
   * @param string|array $file wenn ein array, dann wird dies als pfad zur datei interpretiert. $file kann aber ebenso ein string mit / (oder mehreren) sein (niemals mit \ !)
   * @param string der Name des Modules
   * @return string|FALSE der Pfad zur Datei mit \ oder / je nach Betriebssystem
   * @exception wenn die Datei nicht gefunden werden kann
   */
  public function getPath($fileNamespace, $file, $module = NULL) {
    /* für das Format der Pfade in konventionen.txt nachschauen */

    $path = SRC_PATH;
 
    if (isset($module)) {
      /* 
         wir lassen hier den check, ob ein modul existiert, erst einmal weg
         und untersuchen das lieber bei der Exception außerhalb dieser klasse
      */
      $path .= $module.DIRECTORY_SEPARATOR;
    } /* ansonsten gibt es kein modul und wir suchen die datei im src path */
    
    if (!is_array($file)) {
      $file = explode('/',$file);
    }

    $filename = array_pop($file);
    $directory = implode(DIRECTORY_SEPARATOR,$file);

    if ($directory != '')
      $directory .= DIRECTORY_SEPARATOR;


    /* namespace in eigenem Verzeichnis:
       src/ctrl/home/article.php 
    */
    $path1 = $path.$fileNamespace.DIRECTORY_SEPARATOR.$directory.$filename.'.php';

    /* namespace vor der Datei:
       src/home/ctrl.article.php 
    */
    $path2 = $path.$directory.$fileNamespace.'.'.$filename.'.php';

    var_dump(
      $path1,
      $path2
    );

    //if (is_file($path.$fileNamespace.DIRECTORY_SEPARATOR.$filePath.'.php'))
    
  }

}

?>