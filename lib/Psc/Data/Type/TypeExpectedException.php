<?php

namespace Psc\Data\Type;

class TypeExpectedException extends \Webforge\Types\TypeExpectedException {

  /**
   *
   * nach $formattedMessage können beliebig viele sprintf-Parameter für $formattedMessage angegeben werden
   * @param mixed $messageParam, ...
   * @return Psc\Code\ExceptionBuilder
   */
  public static function build($formattedMessage, $messageParam) {
    return new \Psc\Code\ExceptionBuilder(get_called_class(), $formattedMessage, array_slice(func_get_args(),1));
  }

}
