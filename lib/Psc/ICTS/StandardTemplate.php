<?php

namespace Psc\ICTS;

use \Psc\Exception;

/**
 * Setzt den Namen standardmäßig auf den KlassenNamen, der mit $this->getClassName() geholt wird
 */
abstract class StandardTemplate extends Template {

  public function __construct(Template $parentTemplate) {
    if (!isset($this->name))
      $this->name = $this->getClassName(); // das ist der name ohne ns
    
    parent::__construct($parentTemplate);
  }

  /**
   * Setzt mehrere Values vom Binder in Properties um
   *
   * BSP: $this->extractToProperties(array('/product','/page','/game','headline','text'), self::THROW_EXCEPTION);
   * 
   * Nimmt jedes Propertiy mit dem Namen $value in $propNames ruft get beim Binder für diese Value auf und setzt das Property im Objekt
   * mit Prefix als default (benutzt g())
   * aber OHNE Prefix wenn $value in $propNames ein String mit / am Anfang ist (benutzt b())
   * @param Array $propNames
   * @param $string $standardDefault wird für alle gesetzt, die keinen individuellen default haben (zweiter parameter von this->g())
   */
  public function extractToProperties(Array $propNames, $standardDefault = NULL) {
      foreach ($propNames as $info) {
      if (is_string($info)) {
        if (mb_strpos($info,'/') === 0) {
          $prop = mb_substr($info,1);
          $this->$prop = $this->b($prop,$standardDefault); // nimmt $prop vom root an
        } else {
          $this->$info = $this->g($info, $standardDefault); // nimmt $info mit prefix
        }
        
      } else {
        throw new Exception('implement');
      }
    }
  }
}