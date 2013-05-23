<?php

namespace Psc\URL;

use Webforge\Common\System\File;
use stdClass;
use Psc\PSC;
use Psc\Code\Event\Manager as EventManager;

/**
 *
 *
 * Spaß mit Curl und SSL auf Windows:
 * http://richardwarrender.com/2007/05/the-secret-to-curl-in-php-on-windows/
 * 
 * @TODO HTTP\Header rausnehmen (auch bei Response) und mit Net\HTTP\Header ersetzen
 * @TODO andere methods erlauben
 * @TODO setData vereinfachen usw aufräumen und vll in einen RequestConverter auslagern?
 * @TODO Constructor erweitern
 * @TODO init rausnehmen (weil total confusing)
 */
class Request extends \Psc\Object implements \Psc\Code\Event\Dispatcher {
  
  const EVENT_PROCESSED = 'Psc\URL\Request.Processed';
  
  protected $url;
  
  protected $ch;
  
  protected $cookieJar;
  
  protected $type = 'GET';
  
  /**
   * Die Request-Headers die gesendet wurden
   */
  protected $headers;
  
  
  /**
   * Die Request-Headers die gesendet werden sollen
   */
  protected $httpHeaders = array('Content-Type'=>'application/x-www-form-urlencoded');
  
  /**
   * Die Response diesen Requests
   * @var Psc\URL\Response
   */
  protected $response;
  
  /**
   * Ein Cache für die Response Headers
   *
   * Response verwaltet diese!
   * @var string
   */
  protected $responseHeader;
  
  /**
   * @var stdClass
   */
  protected $data;
  
  /**
   * Bundle der Trusted SSL Sources für SSL Verbindungen
   *
   * liegt in psc_cms\resources\
   */
  protected $caBundle;
  
  /**
   * @var Psc\Code\Event\Manager
   */
  protected $manager;
  
  /**
   */
  protected $init = FALSE;
  
  /**
   * @var array
   */
  protected $options;
  
  public function __construct($url, File $cookieJar = NULL) {
    $this->setUrl($url);
    $this->ch = curl_init();
    
    $this->cookieJar = $cookieJar ?: File::createTemporary();
    $this->data = new stdClass();
    
    if (Helper::isHTTPS($this->url)) {
      $this->useSSL();
    }
    
    $this->manager = new EventManager();
  }
  
  /* set allet */
  public function init() {
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1); // gibt die Value zurück
    curl_setopt($this->ch, CURLOPT_URL, $this->url);
    curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects
    curl_setopt($this->ch, CURLOPT_COOKIEJAR, (string) $this->cookieJar);
    curl_setopt($this->ch, CURLOPT_COOKIEFILE, (string) $this->cookieJar);
    curl_setopt($this->ch, CURLINFO_HEADER_OUT, TRUE);
    
    $headers = array();
    $headers[] = 'Expect:'; // prevents expect-100 automatisch zu senden (ka) 
    foreach ($this->httpHeaders as $name => $value) {
      $headers[] = $name.': '.$value;
    }
    $this->setOption(CURLOPT_HTTPHEADER, $headers);
    
    $this->initData();
    $this->init = TRUE;
    
    return $this;
  }
  
  public function initData() {
    if ($this->isPost()) {
      if (mb_strpos($this->httpHeaders['Content-Type'],'application/x-www-form-urlencoded') === 0) {
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query((array) $this->data));
      } else {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data);
      }
      
    } elseif ($this->isGet()) {
      curl_setopt($this->ch, CURLOPT_URL, $this->url.'?'.http_build_query ( (array) $this->data ));
    }
  }
  
  /**
   * @return Response des Aufrufes
   */
  public function process() {
    if (!isset($this->url) || $this->url === '') throw new RequestException('Request kann nicht gesendet werden. URL ist leer');
    //if (curl_getopt($this->ch, CURLOPT_URL) == '') throw new RequestException('Request kann nicht gesendet werden. wurde vorher init() aufgerufen? (CURLOPT_URL nicht gesetzt).');
    
    /* speicher die responseHeaders in unserer variable die wir an die response weitergeben */
    $this->setOption(CURLOPT_HEADERFUNCTION,array($this,'callbackHeader'));
    $this->responseHeader = NULL;
    
    /* Ausführen */
    $rawResponse = curl_exec($this->ch);
    
    if (curl_error($this->ch) != "") {
      throw new RequestException(
        sprintf("Fehler beim Aufruf von URL: '%s' CURL-Error: '%s'",
                $this->url, curl_error($this->ch)),
        curl_errno($this->ch)
      );
    }
    
    try {
      $this->response = new Response($rawResponse, HTTP\Header::parse($this->responseHeader));
    } catch (\Psc\Exception $e) {
      throw new RequestException('Aus dem Request konnte keine Response erstellt werden. '.$e->getMessage(),0,$e);
    }
    $this->headers = curl_getinfo($this->ch,CURLINFO_HEADER_OUT);
    
    curl_close($this->ch);
    
    $this->manager->dispatchEvent(self::EVENT_PROCESSED,
                                  array(
                                    'response'=>$this->response,
                                    'headers'=>$this->headers
                                  ),
                                  $this);
    
    return $rawResponse;
  }
  
  /**
   *
   * callback: wird von CURL beim process() aufgerufen
   * ist eigentlich dafür da den header zu modifizieren / zu schreiben
   * muss die länge des headers zurückgeben
   */
  public function callbackHeader($ch,$header) {
    $this->responseHeader .= $header;
    
    return strlen($header); // never touch this
  }
  
  /* http://de.php.net/manual/de/function.curl-setopt.php */
  
  public function option($opt, $value) {
    curl_setopt($this->ch, $opt, $value);
    return $this;
  }
  
  public function setOption($opt, $value) {
    $this->options[$opt] = $value;
    curl_setopt($this->ch, $opt, $value);
    return $this;
  }
  
  public function getOption($opt) {
    return $this->options[$opt];
  }
  
  public function setType($type) {
    $this->type = mb_strtoupper($type) == 'POST' ? 'POST' : 'GET';
  }
  
  public function setPost(Array $data) {
    $this->setType('post');
    $this->setData($data);
    return $this;
  }
  
  public function setData($data) {
    $this->data = $data;
    return $this;
  }
  
  /**
   * @return stdClass
   */
  public function getData() {
    return $this->data;
  }

  public function isPost() {
    return $this->type == 'POST';
  }

  public function isGet() {
    return $this->type == 'GET';
  }
  
  public function getURL() {
    return $this->url;
  }
  
  public function appendURL($relativeURL) {
    $this->url .= $relativeURL;
    return $this;
  }
  
  public function setURL($url) {
    if (mb_strpos($url,'?') !== FALSE) {
      //throw new \InvalidArgumentException('URLS mit ? übergeben ist nicht erlaubt!');
    }
    $this->url = $url;
    return $this;
  }
  
  public function useSSL() {
    if (PSC::getEnvironment()->getOS() === 'windows') {
      $this->setOption(CURLOPT_CAINFO, $this->getCABundle());
    }
    return $this;
  }
  
  protected function getCABundle() {
    if (!isset($this->caBundle)) {
      $this->caBundle = \Psc\PSC::getResources()->getFile('cabundle.crt');
    }
    
    return $this->caBundle;
  }
  
  public function setAuthentication($username, $password, $type = CURLAUTH_ANY) {
    $this->setOption(CURLOPT_HTTPAUTH, $type);
    $this->setOption(CURLOPT_USERPWD,$username.':'.$password);
    return $this;
  }
  
  public function removeAuthentication() {
    $this->setOption(CURLOPT_HTTPAUTH, NULL);
    $this->setOption(CURLOPT_USERPWD, NULL);
  }
  
  public function setHeaderField($name, $value) {
    $this->httpHeaders[$name] = $value;
    return $this;
  }
  
  public function getHeaderField($name) {
    return array_key_exists($name, $this->httpHeaders) ? $this->httpHeaders[$name] : NULL;
  }
  
  public function setReferer($referer) {
    $this->setOption(CURLOPT_REFERER, $referer);
    return $this;
  }
  
  public function getHeaders() {
    return $this->headers;
  }
  
  public function getManager() {
    return $this->manager;
  }
  
  public function getResponseHeader() {
    return $this->response->getHeader();
  }
  
  public function debug($withResponse = TRUE, $withResponseBody = TRUE) {
    $text  = "== Request Headers ===============================\n";
    
    if ($this->isGet() && count((array) $this->data) > 0) {
      $text .= 'URL: '.$this->url."?".http_build_query((array) $this->data)."\n";
    } else {
      $text .= 'URL: '.$this->url."\n";
    }
    
    if (isset($this->response)) { // aka request wurde schon abgeschickt, dann zeige hier die gesendeten headers an
      $text .= $this->getHeaders()."\n";
    } else {
      foreach ($this->httpHeaders as $name=>$value) {
        $text .= $name.': '.$value."\n";
      }
    }
    $text .= "== Body (Type: ".$this->type.") =================\n";
    if (array_key_exists('Content-Type', $this->httpHeaders) && mb_strpos($this->httpHeaders['Content-Type'], 'multipart/form-data') !== FALSE) {
      $text .= '<cannot display multipart body on debug>'."\n";
    } else {
      $text .= \Psc\Doctrine\Helper::getDump($this->data, 8);
    }
    $text .= "=================================================\n";
    
    if ($withResponse) {
      $text .= "\n";
      $text .= "== Response =====================================\n";
      if (isset($this->response))  {
        $text .= $this->getResponseHeader()->debug()."\n";
        if ($withResponseBody) {
          $text .= "== Body =========================================\n";
          $text .= $this->response->getRaw()."\n";
        }
      } else {
        $text .= '[noch keine Response]'."\n";
      }
      
      $text .= "=================================================\n";
    }
    return $text;
  }
  
  public function isInit() {
    return $this->init;
  }
  
  /**
   * @return Psc\URL\Response
   */
  public function getResponse() {
    return $this->response;
  }
}
?>