<?php

namespace Psc\CMS\Ajax;

interface Response {
  
  const CONTENT_TYPE_JSON = 'application/json';
  const CONTENT_TYPE_HTML = 'text/html';
  const CONTENT_TYPE_PLAIN = 'text/plain';
  const CONTENT_TYPE_IMAGE_PNG = 'image/png';
  
  const STATUS_OK = 'ok';
  const STATUS_FAILURE = 'failure';
  
  /* typen von ok und validation status */
  const TYPE_STANDARD = 'standard';
  const TYPE_VALIDATION = 'validation';
  
  public function setStatus($status);
  
  public function getStatus();
  
  public function getContentType();
  
  public function setContentType($contentType);
  
  public function getType();
  
  public function setType($type);

  public function export();
  
}