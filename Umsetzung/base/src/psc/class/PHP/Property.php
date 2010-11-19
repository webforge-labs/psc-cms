<?php

/**
 * 
 */
class PHPProperty extends PHPElement {
  
  /**
   * Die Modifier des Properties
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

  public function __construct(Array $token) {
    parent::__construct($token);

    $this->value = NULL;
  }

}

?>