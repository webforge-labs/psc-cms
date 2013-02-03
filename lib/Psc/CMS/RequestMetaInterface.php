<?php

namespace Psc\CMS;

use Psc\Data\Exportable;

interface RequestMetaInterface extends Exportable {

  const GET = 'GET';
  const POST = 'POST';
  const DELETE = 'DELETE';
  const PUT = 'PUT';
  
  public function getUrl();
  
  public function getMethod();
  
  //public function getBody();
}
?>