<?php

/**
 * 
 */
class PHPMethod extends PHPElement {
  
  /**
   * Die Modifier der Methode
   * 
   * @var string[] Schlüsselworte: 'public','protected','static','private'
   */  
  protected $memberModifiers;

  /**
   * Der Name des Properties
   *
   * @var string
   */
  protected $name;

  /**
   * 
   * @var PHPZParameterList
   */
  protected $parameters;

  /**
   * 
   * @var bool
   */
  protected $returnReference;

  public function __construct(Array $token) {
    parent::__construct($token);

    $this->value = NULL;
  }

}

?>