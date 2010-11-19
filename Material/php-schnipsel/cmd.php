<?php
      $out = array();
      $cmd = "$convert -geometry 246x500 $picpath $smallpicpath 2>&1";
      $ret = exec($cmd,$out); //kleines bild machen
      if (!file_exists($smallpicpath)) {
        $errors[] = 'Thumbnail konnte nicht erzeugt werden (Server Fehler): '.implode('&nbsp;.',$out).' Befehl: '.htmlentities($cmd); 
      }

?>