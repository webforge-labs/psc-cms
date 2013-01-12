<?php

namespace Psc\Form;

interface Object {
  
  /**
   * Dieses Label wir z. B. angezeigt bei einem Select bei FormItemRelation
   */
  public function getFormLabel();
  
  /**
   * Gibt den Primärschlüssel des Objektes als String zurück
   * 
   * Wird an den gleichen Stellen wie getFormLabel() verwendet
   * @return string
   */
  public function getFormId();

}