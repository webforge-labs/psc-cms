<?php

namespace Psc\Net;

use Webforge\Common\ArrayUtil AS A;
use Psc\Code\Code;
use Psc\Preg;
use Webforge\Common\System\File;

/**
 * Der RequestMatcher ist ein kleines Helferlein, um einen ServiceRequest zu "parsen"
 *
 * matchId: Integer größer 0 (gibt die Id als (int) zurück)
 * matchRx: jeder ReguläreAusdruck (gibt das Matching zurück)
 * matchValue: === $value
 * matchIValue: mb_strtolower() === $value
 *
 * @TODO matchValues(array())  wäre toll
 */
class RequestMatcher extends \Psc\SimpleObject {
  
  public function __construct(ServiceRequest $request) {
    $this->request = $request;
    $this->parts = (array) $request->getParts();
  }
  
  public function matchValue($value) {
    if ($this->part() === $value) {
      return $this->success();
    }
    
    $this->fail('%s matched nicht mit '.Code::varInfo($value));
  }

  /**
   * @return bool
   */
  public function matchesValue($value) {
    if ($this->part() === $value) {
      $this->matchValue($value);
      return TRUE;
    }

    return FALSE;
  }
  
  public function matchNES() {
    if (mb_strlen($s = $this->part()) > 0) {
      return $this->success($s);
    }
    
    $this->fail('ist ein leerer String');
  }
  
  public function matchIValue($value) {
    if (($p = mb_strtolower($this->part())) === $value) {
      return $this->success($p);
    }
    
    $this->fail($p.' matched '.Code::varInfo($value).' nicht');
  }
  
  /**
   * @return array $match
   */
  public function matchRX($rx) {
    $match = array();
    if (Preg::match($this->part(), $rx, $match) > 0) {
      return $this->success($match);
    }
    
    $this->fail("part '%s' matched nicht: ".$rx);
  }
  
  /**
   * @return string $match[$set]
   */
  public function qmatchRX($rx, $set = 1) {
    if (($match = Preg::qmatch($this->part(), $rx, $set, FALSE)) !== FALSE) {
      return $this->success($match);
    }
    
    $this->fail('%s matched nicht: '.$rx);
  }

  /**
   * @return string $match[$set]
   */
  public function qmatchiRX($rx, $set = 1) {
    if (($match = Preg::qmatch($this->part(), Preg::setModifier($rx,'i'), $set, FALSE)) !== FALSE) {
      $ret = $this->success($match);
      if (is_array($ret)) {
        return array_map($ret, 'mb_strtolower');
      } elseif (is_string($ret)) {
        return mb_strtolower($ret);
      } else {
        return $ret;
      }
    }
    
    $this->fail('%s matched nicht: '.$rx);
  }

  public function matchId() {
    list($id) = $this->matchRX('/^\d+$/'); // das zieht hier schon von parts() ab!
    $id = (int) $id;
    
    if ($id > 0) {
      return $id;
    }
    
    $this->fail('%s ist keine id');
  }
  
  public function matchMethod($method) {
    if ($this->request->getType() === $method) {
      return $this->success($method);
    }
    
    $this->fail('methode '.$method.' matched nicht');
  }
  
  public function success($ret = NULL) {
    $value = array_shift($this->parts);
    if (func_num_args() === 0) {
      return $value;
    } else {
      return $ret;
    }
  }
  
  public function fail($msg) {
    throw new RequestMatchingException(sprintf($msg, $this->part()));
  }
  
  /**
   * Gibt den aktuellen Part zurück (part + 1, part +2 ...)
   *
   * gibt immer NULL zurück wenn keine Parts mehr vorhanden sind
   * @return mixed|NULL
   */
  public function part($offset = 0) {
    return A::index($this->parts, $offset);
  }
  
  /**
   * Gibt die QueryVariable oder NULL zurück
   *
   */
  public function qvar($name, $default = NULL) {
    $query = (array) $this->request->getQuery();
    return array_key_exists($name, $query) ? $query[$name] : $default;
  }
  
  /**
   * Body Var
   */
  public function bvar($name, $default = NULL) {
    $body = (object) $this->request->getBody();
    return isset($body->$name) ? $body->$name : $default;
  }

  public function getFile($name) {
    $file = $this->request->getFile($name);

    if (!($file instanceof File)) {
      $this->fail(sprintf("File '%s' is not given in request", $name));
    }

    return $file;
  }
  
  /**
   * @return mixed|NULL es wird immer NULL zurückgegeben wenn keine parts mehr voranden sind
   */
  public function shift() {
    if (count($this->parts) === 0) return NULL;
    return array_shift($this->parts);
  }
  
  public function pop() {
    if (count($this->parts) >= 1) {
      return array_pop($this->parts);
    }
  }
  
  public function getLeftParts() {
    return $this->parts;
  }
  
  public function getLastPart() {
    if (count($this->parts) >= 1) {
      return $this->part(count($this->parts)-1);
    }
  }
  
  public function isEmpty() {
    return count($this->parts) == 0;
  }
  
  public function getRequest() {
    return $this->request;
  }
}
