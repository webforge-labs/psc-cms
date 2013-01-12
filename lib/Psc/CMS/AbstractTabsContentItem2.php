<?php

namespace Psc\CMS;

use Psc\Net\HTTP\URL;
use Psc\URL\Helper AS URLHelper;

/**
 * Diese Klasse ist "abstract" benannt ohne abstrakt zu sein, damit diese nicht mit new (per Konvention) instanziiert wird
 *
 * in CMS gibt es eine Convenience-API die beliebige TabsContentItems erstellt. Diese benutzen!
 */
class AbstractTabsContentItem2 extends \Psc\SimpleObject implements \Psc\CMS\TabsContentItem2 {
  
  /**
   * Die Labels des Items
   * 
   * @var array[$context] = $label
   */
  protected $labels = array();
  
  /**
   * Zusätzliche Meta-Daten des Objektes
   *
   * (nicht näher spezifiziert)
   * können Kontext-Daten sein die zum URL - Erzeugen gebraucht werden, oder ähnliches
   * @var array
   */
  protected $data = array();
  
  /**
   * @var string
   */
  protected $action = 'form';
  
  /**
   * @var string
   */
  protected $serviceName;
  
  /**
   * @var mixed
   */
  protected $identifier;
  
  /**
   * @var string
   */
  protected $resourceName;
  
  /**
   * @var string
   */
  protected $tabsUrl;
  
  public function __construct($serviceName, $resourceName, $identifier, $action, $label, Array $data = array()) {
    $this->serviceName = $serviceName;
    $this->resourceName = $resourceName;
    $this->identifier = $identifier;
    $this->action = $action;
    $this->data = $data;
    
    if (is_string($label)) {
      $label = array('default'=>$label);
    } 
    
    if (!is_array($label)) {
      throw new \InvalidArgumentException('Label muss entweder ein String sein oder ein array');
    }
    
    $this->labels = $label;
  }
  
  /**
   * @return string
   *
   * @TODO relativeURL wie simpleURL wäre geil
   */
  public function getTabsURL(Array $qvars = array()) {
    if (isset($this->tabsUrl)) {
      return $this->tabsUrl;
    }
    return URLHelper::getURL('/'.implode('/',$this->getTabsId()), URLHelper::RELATIVE, $qvars);
  }
  
  /* Getters + Setters */
  /**
   * @inheritdoc
   * @return list($service, $resource, $identifier, $action)
   */
  public function getTabsId() {
    return array($this->getTabsServiceName(), $this->getTabsResourceName(), $this->getTabsIdentifier(), $this->getTabsAction());
  }
  
  /**
   * @param mixed $tabsIdentifier
   * @chainable
   */
  public function setTabsIdentifier($tabsIdentifier) {
    $this->identifier = $tabsIdentifier;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getTabsIdentifier() {
    return $this->identifier;
  }

  /**
   * @inheritdoc
   * @return string
   */
  public function getTabsAction() {
    return $this->action;
  }
  
  /**
   * Setzt die TabsAction des Entities
   *
   * @param string
   */
  public function setTabsAction($action) {
    $this->action = $action;
    return $this;
  }

  /**
   * @inheritdoc
   * @return string
   */
  public function getTabsLabel($context = 'default') {
    return array_key_exists($context, $this->labels) ? $this->labels[$context] : $this->labels['default'];
  }
  
  /**
   * @inheritdoc
   * @return array
   */
  public function getTabsData() {
    return $this->data;
  }
  
  /**
   * @inheritdoc
   * @param array $data
   */
  public function setTabsData(Array $data) {
    $this->data = $data;
    return $this;
  }

  /**
   * Gibt den ResoureName Teil der TabsId zurück
   */
  public function getTabsResourceName() {
    return $this->resourceName;
  }
  
  /**
   * @chainable
   */
  public function setTabsResourceName($resource) {
    $this->resourceName = $resource;
    return $this;
  }

  /**
   * Gibt den ServiceName Teil der TabsId zurück
   */
  public function getTabsServiceName() {
    return $this->serviceName;
  }

  /**
   * @chainable
   */
  public function setTabsServiceName($service) {
    $this->serviceName = $service;
    return $this;
  }
  
  public function setTabsLabel($label, $context = 'default') {
    $this->labels[$context] = $label;
    return $this;
  }
  
  /**
   * @param string $url
   * @chainable
   */
  public function setTabsUrl($url) {
    $this->tabsUrl = $url;
    return $this;
  }
}
?>