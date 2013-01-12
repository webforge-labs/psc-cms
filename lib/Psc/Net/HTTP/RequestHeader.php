<?php

namespace Psc\Net\HTTP;

class RequestHeader extends \Psc\Net\HTTP\Header {
  
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  
  protected $type;
  
  protected $resource;
  
  public function __construct($type, $resource) {
    $this->type = $type;
    $this->resource = $resource;
  }

  public function setDefaultValues() {
    /* Eigentlich brauchen wir gar keine DefaultHeader Values, weil der Server schon echt viel für uns macht
      nicht ganz sicher hier */
    $this->values = array_replace($this->values, Array(
      'Content-Type' => 'text/html; charset=UTF-8',
    ));
  
    return $this;
  }
  
  protected function sendStatusLine() {
    $this->sendPHPHeader(NULL, $this->getStatusLine(), TRUE);
  }
  
  public function getStatusLine() {
    return sprintf('%s %s HTTP/%s',
                   $this->getType(),
                   $this->getResource(),
                   $this->getVersion());
  }
}
?>