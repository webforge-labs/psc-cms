<?php

namespace Psc\Doctrine;

use \Psc\Code\Code;

class Exception extends \Psc\Exception {
  
  public static function convertPDOException(\PDOException $e) {
    $exceptions = array('\Psc\Doctrine\ForeignKeyConstraintException',
                        '\Psc\Doctrine\UniqueConstraintException',
                        '\Psc\Doctrine\TooManyConnectionsException',
                        '\Psc\Doctrine\UnknownColumnException'
                        );
    
    /* grml. fix pdo */
    if ($e->errorInfo === NULL && mb_strlen($msg = $e->getMessage()) > 0) {
      //SQLSTATE[08004] [1040] Too many connections
      if (\Psc\Preg::match($msg, '/SQLSTATE\[([0-9]+)\]\s*\[([0-9]+)\]\s*(.*)?/s', $m)) {
        $e->errorInfo[0] = $m[1];
        $e->errorInfo[1] = $m[2];
        $e->errorInfo[2] = $m[3];
      }
    }
    
    foreach ($exceptions as $cname) {
      if ($cname::check($e)) {
        return new $cname($e);
      }
    }
    
    //throw new \Psc\Exception('unconverted PDO Exception: '.Code::varInfo($e->errorInfo),0,$e);
    print 'unconverted PDOException: '.Code::varInfo($e->errorInfo);
    
    return $e;
  }
}
?>