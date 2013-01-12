<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Ajax\Response;

interface ResponseController extends Controller {
  
  /**
   * @return Response
   */
  public function getResponse();
  
  /**
   * @param Response $response
   */
  public function setResponse(Response $response);
}

?>