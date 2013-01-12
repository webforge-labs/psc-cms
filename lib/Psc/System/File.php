<?php

namespace Psc\System;

class File extends \Webforge\Common\System\File {

  /**
   * Replaces the contents of the file
   * @deprecated
   */
  public function replaceContents(Array $replacements) {
    $this->writeContents(
      str_replace(
                  array_keys($replacements),
                  array_values($replacements),
                  $this->getContents()
                 )
    );
    return $this;
  }

}
?>