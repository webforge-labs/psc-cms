<?php

namespace Psc\TPL\ContentStream;

interface ContextLoadable {

  /**
   *  Should return a (new) instance of the Entity with the serializedValue with the help of $context
   * 
   * for example an image is loaded with the image manager from the db
   * an entity maybe loaded with its controller / doctrine from the database
   */
  public static function loadWithContentStreamContext($serializedValue, Context $context);
}
