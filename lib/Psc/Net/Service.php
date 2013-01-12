<?php

namespace Psc\Net;

/**
 * Ein Service erhält vom RequestHandler einen ServiceRequest und gibt eine ServiceResponse zurück
 *
 * die Response wird vom RequestHandler in eine HTTP-Response umgewandelt und dann wieder ausgegeben
 */
interface Service {
  
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  const PATCH = 'PATCH';
  
  const OK = 200;
  const CREATED = 201;
  
  // ab 400 siehe \Psc\Net\HTTP\HTTPException
  
  const ERROR = 'status_error'; // die frage ist: ob wir das hier jemals brauchen wenn wir HTTPExcepion haben?
  
  /**
   * Gibt True zurück wenn der Service den ServiceRequest erfolgreich bearbeiten kann
   *
   * @todo erfolgreich definieren
   * @return bool
   */
  public function isResponsibleFor(ServiceRequest $request);

  /**
   * Führt den ServiceRequest aus
   *
   * @return ServiceResponse
   */
  public function route(ServiceRequest $request);
}
?>