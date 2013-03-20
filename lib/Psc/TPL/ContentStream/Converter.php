<?php

namespace Psc\TPL\ContentStream;

use Psc\Doctrine\EntityFactory;

abstract class Converter extends \Psc\SimpleObject {

  public function convertHTML(ContentStream $cs, Array $entries = NULL) {
    $html = NULL;
    
    $entries = $entries !== NULL ? $entries : $cs->getEntries();
    foreach ($entries as $entry) {
      
      if ($entry instanceof ImageManaging) {
        $entry->setImageManager($this->context->getImageManager());
      }
      
      if ($entry instanceof ContextAware) {
        $entry->setContext($this->context);
      }
      
      $html .= $entry->html($this->context); // alternativ: contextAware wäre besser weil html() ist ja vom HTMLInterface
    }
    
    return $html;
  }
  
  public function convertHTMLExcerpt(ContentStream $cs, $maxLength = NULL) {
    if (($p = $cs->findFirst('paragraph')) != NULL) { // cooler halt: find cuttable/excerptable/textable irgendwie sowas
      if (!isset($maxLength)) {
        // so irgendwie, wie machen wir maxLength? für mehrere p's?
        return $this->convertHTML($cs, array($p));
      } else {
        return $p->excerpt($maxLength);
      }
    }
    return '';
  }

  public function convertHTMLHeadline(ContentStream $cs) {
    $entries = array();
    if (($h1 = $cs->findFirst('headline')) != NULL) {
      $entries[] = $h1;
    }
    // wenn nichts gefunden vll den ersten satz des ersten paragraphs nehmen?
    return $this->convertHTML($cs, $entries);
  }

  public function convertTextHeadline(ContentStream $cs) {
    // so machen wirs erstmal
    if (($h1 = $cs->findFirst('headline')) != NULL) {
      return $h1->getContent();
    }
    return '';
    
    // so wäre es schön:
    //$entries = array();
    //if (($h1 = $cs->findFirst('headline')) != NULL) {
    //  $entries[] = $h1;
    //}
    //// wenn nichts gefunden vll den ersten satz des ersten paragraphs nehmen?
    //return $this->convertText($cs, $entries);
  }
  
  /**
   * Konvertiert den ContentStream in eine Repräsenation die nur aus einem Array besteht
   *
   * der Javascript LayoutManager kann dies mit unserialize() lesen und daraus widgets erstellen
   * @return array
   */
  public function convertSerialized(ContentStream $cs) {
    $serialized = array();
    
    foreach ($cs->getEntries() as $entry) {
      if ($entry instanceof ContextAware) {
        $entry->setContext($this->context);
      }
      
      $serialized[] = $entry->serialize($this->context);
    }
    
    return $serialized;
  }
  
  
  /**
   * @param Closure $factoriesCreater bekommt als einzigen Paramter den Type des Entities (der Klassename ohne Namespace alles in klein)
   */
  public function convertUnserialized(Array $serialized, ContentStream $contentStream = NULL) {
    if (isset($contentStream)) {
      foreach ($serialized as $serializedEntry) {
        $entry = $this->unserializeEntry((object) $serializedEntry);
        $entry->setContentStream($contentStream);
      }
      return $contentStream;
    } else {
      $unserialized = array();
      foreach ($serialized as $serializedEntry) {
        $unserialized[] = $this->unserializeEntry($serializedEntry);
      }
    }
    
    return $unserialized;
  }
  
  public function unserializeEntry(\stdClass $serializedEntry) {
    $factoryCreater = $this->getFactoryCreater();
    // back:
    $factory = $factoryCreater($serializedEntry->type);
    $c = $factory->getEntityMeta()->getClass();
      
    unset($serializedEntry->type);
    
    if (!$factory->getEntityMeta()->hasProperty('label')) {
      unset($serializedEntry->label);
    }
    
    return $c::unserialize($serializedEntry, $factory, $this);
  }
  
  /**
   * @return Closure
   */
  public function getFactoryCreater() {
    if (!isset($this->factoryCreater)) {
      $factories = array();
      $dc = $this->context->getDoctrinePackage();
    
      $that = $this;
      $this->factoryCreater = function($type) use (&$factories, $dc, $that) {
        if (!array_key_exists($type, $factories)) {
          $c = $that->getTypeClass($type);
        
          $factories[$type] = new EntityFactory($dc->getEntityMeta($c));
        } else {
          $factories[$type]->reset();
        }
      
        return $factories[$type];
      };
    }
    
    return $this->factoryCreater;
  }
  
  /**
   * Returns a full FQN for the $typeName
   * 
   * @param string $typeName the JavaScript name without any namespace
   */
  abstract public function getTypeClass($typeName);
  
  public function getContext() {
    return $this->context;
  }
}
?>