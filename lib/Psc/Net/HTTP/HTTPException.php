<?php

namespace Psc\Net\HTTP;

/**
 * Eine HTTP-Error-Exception
 *
 * dies ist sozusagen ein gewollter Fehler.
 * - Der Code der HTTPException entspricht dem HTTP-Error-Code
 * - die Reason wird automatisch gesetzt
 * - Responsebody ist die RAW-Response die vom FrontController ausgegeben wird
 *
 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 * http://tools.ietf.org/html/rfc4918#section-11.2
 * 
 * HTTPExceptions werden vom RequestHandler in HTTP-Responses umgewandelt. D. h. sie können in jedem Service / Controller geworfen werden und verursachen dann die gewünschte Response
 */
class HTTPException extends \Psc\Net\HTTP\Exception implements \Psc\Code\ExceptionExportable {
  
  /**
   * Die Raw Response, die ausgegeben wird
   *
   * @var string
   */
  protected $responseBody;

  /**
   * Zusätzliche Headers für die Exception
   * 
   * @var Array
   */
  protected $headers = array();
  
  /**
   * NotFound (404)
   *
   * The server has not found anything matching the Request-URI. No indication is given of whether the condition is temporary or permanent. The 410 (Gone) status code SHOULD be used if the server knows, through some internally configurable mechanism, that an old resource is permanently unavailable and has no forwarding address. This status code is commonly used when the server does not wish to reveal exactly why the request has been refused, or when no other response is applicable.
   *  
   */
  public static function NotFound($body = NULL, \Exception $previous = NULL) {
    return self::code(404, $body, $previous);
  }

  /**
   * Unauthorized (401)
   * 
   * The request requires user authentication.
   * If the request already included Authorization credentials, then the 401 response indicates that authorization has been refused for those credentials.
   */
  public static function Unauthorized($body = NULL, \Exception $previous = NULL) {
    return self::code(401, $body, $previous);
  }
  
  /**
   * BadRequest (400)
   * 
   * The request could not be understood by the server due to malformed syntax. The client SHOULD NOT repeat the request without modifications.
   * => used in Validation
   */
  public static function BadRequest($body = NULL, \Exception $previous = NULL, Array $headers = array()) {
    return self::code(400, $body, $previous, $headers);
  }
  
  /**
   * Forbidden (403)
   * 
   * The server understood the request, but is refusing to fulfill it. Authorization will not help and the request SHOULD NOT be repeated. If the request method was not HEAD and the server wishes to make public why the request has not been fulfilled, it SHOULD describe the reason for the refusal in the entity. If the server does not wish to make this information available to the client, the status code 404 (Not Found) can be used instead.
   */
  public static function Forbidden($body = NULL, \Exception $previous = NULL) {
    return self::code(403, $body, $previous);
  }
  
  /**
   * MethodNotAllowed (405)
   *
   * The method specified in the Request-Line is not allowed for the resource identified by the Request-URI. The response MUST include an Allow header containing a list of valid methods for the requested resource.
   */
  public static function MethodNotAllowed($body = NULL, \Exception $previous = NULL) {
    return self::code(405, $body, $previous);
  }

  /**
   * Not Acceptable (406)
   *
   * The resource identified by the request is only capable of generating response entities which have content characteristics not acceptable according to the accept headers sent in the request. 
   */
  public static function NotAcceptable($body = NULL, \Exception $previous = NULL) {
    return self::code(406, $body, $previous);
  }

  /**
   * Conflict (409)
   *
   * The request could not be completed due to a conflict with the current state of the resource. This code is only allowed in situations where it is expected that the user might be able to resolve the conflict and resubmit the request. The response body SHOULD include enough information for the user to recognize the source of the conflict. Ideally, the response entity would include enough information for the user or user agent to fix the problem; however, that might not be possible and is not required.
   *
   * Conflicts are most likely to occur in response to a PUT request. For example, if versioning were being used and the entity being PUT included changes to a resource which conflict with those made by an earlier (third-party) request, the server might use the 409 response to indicate that it can't complete the request. In this case, the response entity would likely contain a list of the differences between the two versions in a format defined by the response Content-Type. 
   */
  public static function Conflict($body = NULL, \Exception $previous = NULL) {
    return self::code(409, $body, $previous);
  }
  
  /**
   * Unsupported Media Type (415)
   *
   * The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
   */
  public static function UnsupportedMediaType($body = NULL, \Exception $previous = NULL) {
    return self::code(415, $body, $previous);
  }

  /**
   * Service Unavailable (503)
   *
   * The server is currently unable to handle the request due to a temporary overloading or maintenance of the server. The implication is that this is a temporary condition which will be alleviated after some delay. If known, the length of the delay MAY be indicated in a Retry-After header. If no Retry-After is given, the client SHOULD handle the response as it would for a 500 response.
   *
   * Note: The existence of the 503 status code does not imply that a server must use it when becoming overloaded. Some servers may wish to simply refuse the connection.
   */
  public static function ServiceUnavaible($body = NULL, \Exception $previous = NULL) {
    return self::code(503, $body, $previous);
  }

  
  //public static function reason // anhand des strings erstellen
  
  /**
   * @return HTTPException
   */
  public static function code($code, $body = NULL, \Exception $previous = NULL, Array $headers = array()) {
    $reason = ResponseHeader::getReasonFromCode($code);
    $e = new static($reason, (int) $code, $previous);
    $e->setResponseBody($body);
    $e->setHeaders($headers);
    return $e;
  }
  
  public function getResponseBody() {
    if (!isset($this->responseBody)) {
      return $this->getMessage();
    }
    
    return $this->responseBody;
  }
  
  public function setResponseBody($body) {
    $this->responseBody = $body;
  }
  
  public function getHeader() {
    return sprintf('HTTP-Error: %d %s', $this->getCode(), $this->getMessage());
  }
  
  public function debug() {
    return $this->getHeader().' ::: '.$this->getResponseBody();
  }
  
  public function exportExceptionText() {
    return $this->debug();
  }
  
  /**
   * @param array $headers
   * @chainable
   */
  public function setHeaders(array $headers) {
    $this->headers = $headers;
    return $this;
  }

  /**
   * @return array
   */
  public function getHeaders() {
    return $this->headers;
  }
}
?>