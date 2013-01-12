<?php

namespace Psc\Hitch;

use Psc\Hitch\Manager AS HitchManager;
use Hitch\Mapping\ClassMetadataFactory;
use Hitch\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Psc\Code\Code;
use Psc\PSC;
use Webforge\Common\System\File;
use Psc\Code\Generate\GClass;
use Psc\XML\Loader AS XMLLoader;

/**
 * Liest aus einer XML Quelle die Hitch Objekte aus
 *
 *
 * der Reader arbeitet ohne Caches und liest tatsächlich nur
 * der Loader oder Importer sollte dann intelligenter sein
 * 
 * die Objekte des Projektes können sich in getModule('Hitch')->getObjectsNamespace() befinden.
 * Ansonsten muss man das Hitch Objekt selbst erstellen und die ClassMetadatafactory selbst erstellen.
 * Man kann aber den ObjectsNamespace im Modul auch selbst setzen - nach dem bootstrapping oder so
 */
class Reader extends \Psc\Object {
  
  /**
   * @var Hitch\HitchManager
   */
  protected $hitch;
  
  /**
   * @var Psc\Hitch\Module
   */
  protected $module;

  public function __construct(HitchManager $hitch = NULL) {  
    if (!isset($hitch)) {
      $hitch = new HitchManager();
      $hitch->setClassMetaDataFactory(new ClassMetadataFactory(
                                        new AnnotationLoader(new AnnotationReader()), 
                                        new ArrayCache(),
                                        PSC::getProject()->getModule('Hitch')->getObjectsNamespace()
                                        )
                                    );
    }
    $this->hitch = $hitch;
    $this->module = PSC::getProject()->getModule('Hitch');
  }
  
  /**
   * @param GClass $rootClass die klasse die das erste Element des XMLs modelliert
   */
  public function readFile(File $file, GClass $rootClass) {
    try {
      $xmlString = $file->getContents();
      $root = $this->hitch->unmarshall($xmlString, $rootClass->getFQN());
    } catch (\Exception $e) {
      if ($e->getMessage() === 'String could not be parsed as XML') {
        /* Debug Loading */
        $loader = new XMLLoader();
        $loader->process($xmlString);
      }
      throw $e;
    }
    
    return $root;
  }
  
  public function expandClassName($className) {
    return $this->module->expandClassName($className);
  }
}
?>