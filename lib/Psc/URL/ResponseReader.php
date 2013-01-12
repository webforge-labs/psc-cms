<?php

namespace Psc\URL;

use Psc\JS\JSONConverter;

class ResponseReader extends \Psc\SimpleObject {
  
  public function read(Response $response, $format = 'html', $code = 200) {
    //$this->logf("Dispatched CMS Request:\n%s", $dispatcher->getRequest()->debug(TRUE, FALSE));
    
    // versuche error meldung zu bekommen
    $msg = NULL;
    if ($response->getCode() >= 400 && $response->getHeaderField('X-Psc-Cms-Error') == 'true' && $response->getHeaderField('X-Psc-Cms-Error-Message') != NULL) {
      $msg = "\n".'Fehler auf der Seite: '.$response->getHeaderField('X-Psc-Cms-Error-Message');
    }
    
    if ($code != $response->getCode()) {
      $e = new ResponseException('Failed asserting that Response Code is '.$code.'. Response code is: '.$response->getCode().'.'.$msg);
      $e->response = $response;
      throw $e;
    }
    
    if ($format == 'json') {
      try {
        return JSONConverter::create()->parse($response->getRaw());
      } catch (\Psc\JS\JSONParsingException $e) {
        throw new ResponseException(
          sprintf("Result cannot be read as JSON. %s",
                  mb_substr($response->getRaw(), 0, 600)),
          0,
          $e
        );
      }
    } elseif ($format == 'html') {
      return $response->getDecodedRaw();
    } elseif ($format == 'text') {
      return $response->getDecodedRaw();
    } elseif ($format == 'raw') {
      return $response->getRaw();
    }
    
    return $response;
  }
}
?>