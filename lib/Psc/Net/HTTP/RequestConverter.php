<?php

namespace Psc\Net\HTTP;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\JS\JSONConverter;

class RequestConverter extends \Psc\System\LoggerObject {

  public function __construct(\Psc\System\Logger $logger = NULL) {
    $this->setLogger($logger ?: new \Psc\System\BufferLogger());
    $this->jsonConverter = new JSONConverter();
  }

  public function fromHTTPRequest(Request $request) {
    $body = $request->getBody();
    $meta = array();
    
    // override method
    $rqMethodHeader = 'X-Psc-Cms-Request-Method';
    if (in_array($method = $request->getHeaderField($rqMethodHeader), array(Service::PUT, Service::DELETE))) {
      $this->log('Request-Method durch HTTP-Header '.$rqMethodHeader.' überschrieben zu: '.$method);
      unset($body->$rqMethodHeader);
    } else {
      $method = constant('Psc\Net\Service::'.$request->getMethod()); // Request::GET => ServiceRequest::GET
    }
    
    // revision
    $revisionHeader = 'X-Psc-Cms-Revision';
    if (($revision = $request->getHeaderField($revisionHeader)) != '') {
      $meta['revision'] = $revision;
      unset($body->$revisionHeader);
    }
    
    // convert bodyAsJSON to native
    if (is_object($body) && count($body) === 1 && isset($body->bodyAsJSON)) {
      $body = $this->jsonConverter->parse($body->bodyAsJSON);
    }

    // convert json request to native
    if (is_string($body) && $request->isContentType('application/json')) {
      $body = $this->jsonConverter->parse($body);
    }
    
    return new ServiceRequest(
      $method,
      $request->getParts(),
      $body,
      $request->getQuery(),
      $request->getFiles(),
      $meta
    );
  }
}
?>