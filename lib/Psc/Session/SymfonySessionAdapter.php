<?php

namespace Psc\Session;

use Psc\Form\DataInput;
use Psc\Exception;

/**
 *
 * We needed to hack here: the Auth component uses getOption somewhere, but should depend on the Session interface
 * so we need to implement getOption as well (very bad)
 */
class SymfonySessionAdapter extends \Psc\OptionsObject implements \Webforge\Common\Session {
  
  /**
   * @var \FormDataInput
   */
  protected $data;

  protected $session;
  
  public function __construct(\Symfony\Component\HttpFoundation\Session\SessionInterface $session) {
    $this->session = $session;
    $this->setDefaultOptions(Array(
      'cookieDomain'=>isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL,
      'name'=>'SID',
      'cookies'=>TRUE
    ));
  }
  
  public function get() {
    $keys = func_get_args();
    $key = implode('/', $keys);

    return $this->session->get($key);
  }
  
  public function set() {
    $keys = func_get_args();
    $value = array_pop($keys);
    
    $key = implode('/', $keys);

    return $this->session->set($key, $value);
  }
  
  public function init() {
    // we assume symfony has started the session for us
    return $this;
  }

  public function destroy() {
    $this->session->invalidate();
    return $this;
  }
}
