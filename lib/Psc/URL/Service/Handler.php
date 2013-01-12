<?php

namespace Psc\URL\Service;

/**
 *
 *
 * GET /episodes              => Episodes->index()
 * GET /episodes/8            => Episodes->get(8)
 * GET /episodes/8/status     => Episodes->get(8,'status')  // oder halt subrequest Episodes->get(8)->get('status') (oder sowas)
 * PUT /episodes/8            => Episodes->put(8, $data) // dunno
 * POST /episodes/8/status    => Episodes->post(8, $data)
 *
 * @TODO cool wäre, wenn der handler diese .htaccess rules selbst schreiben könnte:
 * @TODO Responses hier 404 / 505 wäre auch cool
 *
 *
Options FollowSymLinks

RewriteEngine on
RewriteBase /

RewriteCond  %{REQUEST_URI}    ^/episodes/.*
RewriteRule  ^(.*)   /api.php?mod_rewrite_request=$1 [L]

 */
class Handler extends \Psc\Object {
  
  protected $services = array();
  
  protected $call;
  protected $service;
  
  public function __construct() {
  }
  
  /**
   * Führt den Request aus
   * 
   * Gibt die Ausgabe des Calls vom Controller zurück
   * wenn man den Request nicht selbst erstellen will, kann man
   *
   * \Psc\URL\Service\Request::infer();
   *
   * benutzen.
   */
  public function process(Request $request) {
    // required
    $service = $this->getRegisteredService($request->getPart(1));
    
    $call = new Call(mb_strtolower($request->getMethod()));
    
    // identifier ist optional
    if (($identifier = $request->getPart(2)) !== NULL) {
      $call->addParameter($identifier);

      /* alle Subs weitergeben als weitere Parameter */
      foreach ($request->getPartsFrom(3) as $p) {
        $call->addParameter($p);
      }
    } else {
      $call->setName('index');
    }
    
    if ($request->getMethod() === Request::POST || $request->getMethod() === Request::PUT) {
      $call->addParameter($request->getBody());
    }
    
    $this->call = $call;
    $this->service = $service;
    return $this->call();
  }
  
  /**
   * Gibt die Rückgabe des process() von Controller zurück
   */
  protected function call() {
    if (isset($this->service) && isset($this->call)) {
      return $this->service->process($this->call);
    }
  }
  
  public function getRegisteredService($name) {
    if (!array_key_exists($name, $this->services)) {
      throw new Exception("Kein Service mit dem Namen: '".$name."' registriert.");
    }
    return $this->services[$name];
  }

  public function register(Service $service) {
    $this->services[$service->getName()] = $service;
    return $this;
  }
}

?>