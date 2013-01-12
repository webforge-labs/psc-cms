<?php

namespace Psc\CMS;

use Psc\Code\Code;

class UploadedFileNotFoundException extends \Psc\Exception {
  
  public static function fromEntityNotFound($e, $input) {
    return new static('Die UploadedFile konnte nicht aus der Datenbank geladen werden: '.$e->getMessage());
  }
}
?>