<?php

namespace Psc\Net;

/**
 * Marks an object which is able to output its contents just like a normal response would do
 *
 * it prints its outputs, and can flush the buffer to create chunked output responses
 */
interface ResponseOutputting {
  
  /**
   * @param const $format one of ServiceResponse::* format constants
   */
  public function output(ResponseOutput $output, $format = NULL);
}
?>