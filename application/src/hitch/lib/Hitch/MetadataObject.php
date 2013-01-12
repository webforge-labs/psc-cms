<?php

/**
 * This file is part of the Hitch package
 * 
 * (c) Marc Roulias <marc@lampjunkie.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hitch;

interface MetadataObject {
  
  public function setMetadata($meta);
  
  public function getMetaData();
  
}