<?php

namespace Psc\Hitch;

use Psc\XML\Loader AS XMLLoader;

class Manager extends \Hitch\HitchManager {
  
  protected $loader;
  
  public function __construct(XMLLoader $loader = NULL) {
    $this->loader = $loader ?: new XMLLoader();
  }
  
  /**
   * Unmarshall an XML string into an object graph
   * 
   * @param string $xmlString
   * @param string $rootClass
   */
  public function unmarshall($xmlString, $rootClass) {
    $metadata = $this->classMetadataFactory->getClassMetadata($rootClass);
    
    $xml = $this->loader->process($xmlString);

    return $this->parseObject($xml, $metadata);
  }
}
?>