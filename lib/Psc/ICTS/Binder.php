<?php

namespace Psc\ICTS;

use \Psc\DataInput;

/**
 * Ein Binder verbindet ein Template mit einer Datenquelle
 *
 * er wird vom Template angesprochen, wenn es Variablen haben möchte
 * er wird von der Datenquelle gefüttert
 */
interface Binder {

  const THROW_EXCEPTION = \Psc\ICTS\Data::THROW_EXCEPTION;
  const RETURN_NULL = \Psc\ICTS\Data::RETURN_NULL;

  const PREFIX_KEYS = 'prefix';
  const ABSOLUTE_KEYS = 'absolute_no_profix';
  
  /**
   * Gibt einen einzelnen Schlüssel mit gesetztm Prefix zurück
   * 
   * @param array|string $keys entweder ein array oder string der string darf nicht die . notation eines arrays sein!
   * @return mixed
   */
  public function g($keys, $default = NULL);
  
  
  /**
   * wie g() nur  OHNE Prefix
   */
  public function b($keys, $default = NULL);

  /**
   * Setzt einen einzelnen Schlüssel ohne Prefix
   * 
   * zu $keys wird kein prefix hinzugefügt
   */
  public function bind($keys, $value);
  
  /**
   * Setzt einen einzelnen Schlüssel mit dem gesetzten Prefix
   * zu $keys wird der prefix hinzugefügt (wenn gesetzt)
   */
  public function s($keys, $value);
  
  /**
   * Ersetzt alle Daten im Binder mit denen vom Array
   * @chainable
   */
  public function setBindsFromArray(Array &$data);
  
  /**
   * Gibt alle Daten im Binder zurück
   * OHNE prefix
   * @return \Psc\ICTS\Data
   */
  public function getBinds();
  
  
  /**
   * @return Array
   */
  public function getPrefix();
  
  /**
   * Fügt einen Namespace für die Template-Variablen hinzu
   */
  public function addPrefix($prefix);
  
  public function removePrefix();
}

?>