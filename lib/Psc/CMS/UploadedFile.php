<?php

namespace Psc\CMS;

interface UploadedFile {
  
  //public function __construct(\Webforge\Common\System\File $file, $description = NULL)
  
  /**
   * Gibt die Kurzbeschreibung der Datei zurück
   *
   * die Kurzbeschreibung kann z.B. als Linkbeschriftung der File benutzt werden und ist eine etwas schönere Darstellung des Datei-Namens
   * @return string
   */
  public function getDescription();
  
  /**
   * Setzt die Kurzbeschreibung der Datei
   * 
   * @param string $description
   */
  public function setDescription($description);
  
  /**
   * Gibt die Binär-Datei zum Upload zurück
   * 
   * @return Webforge\Common\System\File
   */
  public function getFile();
  
  /**
   * Gibt den Hash zurück der den Inhalt der Datei eindeutig hashed
   *
   * die Standardimplementierung sollte sha1 sein ist aber nicht relevant
   * @return string
   */
  public function getHash();
  
  /**
   * @return string
   */
  public function setHash($hash);
  
  
  /**
   * Nur der Dateiname der gesendet wird, wenn die Datei zum Download angeboten wird
   *
   * @return string ohne pfad! mit extension
   */
  public function getDownloadFilename();


  /**
   * @return string
   */
  public function getOriginalName();
  
  /**
   * @param string $originalName
   */
  public function setOriginalName($originalName);
  
  
  public function getDisplayExtension();
}
?>