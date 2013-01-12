<?php

namespace Psc\CMS\Ajax;

use \Psc\Code\Code;

class ImageResponse extends \Psc\CMS\Ajax\StandardResponse {
  
  public function __construct($raw, $contentType = Response::CONTENT_TYPE_IMAGE_PNG) {
    Code::value($contentType, Response::CONTENT_TYPE_IMAGE_PNG);
    parent::__construct(Response::STATUS_OK,$contentType);
    $this->data->content = $raw;
  }
  
  public function export() {
    return (string) $this->data->content;
  }
}

?>