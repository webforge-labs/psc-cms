<?php

namespace Psc\URL;

use Psc\Code\Event\Event;
use Psc\Data\ObjectCache;
use Psc\Data\Cache;

/**
 * Ein RequestBundle ist ein Mini HTTP-Client der aber nur doofe Requests abfeuert.
 *
 * Diese Klasse hilft ungemein das Caching von URLs zu verwalten und beim Testen die DependencyInjection aufzulösen
 *
 * Achtung: diese Klasse hilft nur bei RequestBundles die ausschließlich URLs crawlen. Das Cache-Kriterium ist die URL (nicht die Methode, nicht der Body)
 *
 * für DPI Auflösung: \Psc\URL\RequestDispatcher
 */
class RequestBundle extends \Psc\Object implements \Psc\Code\Event\Subscriber {
  
  /**
   * @var Psc\Data\ObjectCache
   */
  protected $requestsCache;
  
  /**
   *
   * @param int $ttl die Zeit in Sekunden die jeder Request in diesem Bundle bestehen bleiben soll, bevor er neu ausgeführt wird
   * @param Cache $cache der Cache für die URL Requests
   */
  public function __construct($ttl, Cache $cache = NULL) {
    
    $this->requestsCache = $cache ?:
      new \Psc\Data\ObjectCache(function ($request) {
                                  return array($request->getURL());
                                },
                                (int) $ttl
                               );
  }
  
  /**
   * Gibt entweder einen gecachten oder einen echten Request zurück
   * 
   */
  public function createRequest($url, File $cookieJar = NULL) {
    $loaded = FALSE;
    $request = $this->requestsCache->load(array($url), $loaded);
    
    if (!$loaded) {
      $request = new Request($url, $cookieJar);
      $request->getManager()->bind($this, Request::EVENT_PROCESSED); // stored dann wenn process() aufgerufen wurde
    }
    
    return $request;
  }
  
  
  /**
   * Bei processed fügt das bundle den Request alse Cache-Request hinzu
   */
  public function trigger(Event $event) {
    $request = $event->getTarget();
    
    $cachedRequest = new CachedRequest($request->getURL());
    $cachedRequest->setCachedResponse($event->getData()->response);
    $cachedRequest->setHeaders($event->getData()->headers);
    
    $this->addCachedRequest($cachedRequest);
  }
  
  public function addCachedRequest(CachedRequest $cachedRequest) {
    $this->requestsCache->store(NULL,$cachedRequest);
    return $this;
  }
}
?>