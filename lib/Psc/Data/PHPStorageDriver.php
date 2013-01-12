<?php

namespace Psc\Data;

use \Webforge\Common\System\File;

class PHPStorageDriver implements StorageDriver {
  
  protected $file;
  
  protected $template = '<?php

%dataPHP%

?>';
  
  public function __construct(\Webforge\Common\System\File $file) {
    $this->file = $file;
  }
  
  public function persist(Storage $storage) {
    $this->file->writeContents(
      \Psc\TPL\TPL::miniTemplate($this->template, array('dataPHP'=>'$data = '.var_export($storage->getData()->getData(),TRUE).';')),
      File::EXCLUSIVE
    );
    return $this;
  }
  
  public function load(Storage $storage) {
    if ($this->file->exists()) {
      require $this->file; // require nicht require_once
      if (!isset($data))  {
        throw new \Psc\Exception('Invalid state of storageFile: '.$this->file.' This is critical due to hacking!');
      }
      
    } else {
      $data = array();
    }
    
    $storage->setData($data);
    return $this;
  }
}
?>