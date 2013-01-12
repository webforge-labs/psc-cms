<?php

namespace Psc\CMS\Controller;

use Psc\Code\Code;
use Psc\TPL\TPL;
use Psc\TPL\Template;
use Psc\Config;
use Psc\Net\HTTP\HTTPException;

class TPLController extends \Psc\SimpleObject {
  
  /**
   * @controller-api
   */
  public function get(array $tplName) {
    
    $__variablesDefinitions = array();
    
    try {
      $tpl = new Template($tplName);
      $tpl->setLanguage(Config::req('i18n.language'));
      $tpl->setVars($__variablesDefinitions);
      
      if (!$tpl->validate()) {
        throw new \Psc\TPL\MissingFileException('Template ist nicht valid!');
      }
      
      if (!$tpl->getFile()->exists()) {
        throw new \Psc\TPL\MissingFileException('Template-File: '.$tpl->getFile().' existiert nicht');
      }
      
      return $tpl->get();
      
    } catch (\Psc\TPL\MissingFileException $e) {
      throw HTTPException::NotFound('Das Template: '.Code::varInfo($tplName).' ist nicht vorhanden', $e);
    }
  }
}
?>