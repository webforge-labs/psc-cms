<?php

namespace Psc\Entities;

use Psc\CMS\Roles\Container;
use Webforge\Common\System\Dir;
use Psc\Image\Manager;

class Instancer {

  protected $cache;
  protected $container;
  protected $commonFiles;

  public function __construct(Container $container, Dir $commonFiles) {
    $this->container = $container;
    $this->commonFiles = $commonFiles;
    $this->cache = array();
  }

  protected function instanceCSImage($num) {
    $image = $this->newObject('CS\Image', array('/url/in/cms/to/original.png'));
    $image->setImageEntity($this->getImage($num));
    return $image;
  }

  protected function instanceImage($num) {
    $manager = $this->container->getImageManager();

    $imageName = is_numeric($num) ? 'images/image'.$num.'.jpg' : 'images/'.$num;
    $imageFile = $this->commonFiles->getFile($imageName);

    $image = $manager->store(
      $manager->createImagineImage($imageFile),
      NULL,
      Manager::IF_NOT_EXISTS
    );
    $manager->flush();

    return $image;
  }

  protected function instanceNavigationNode($num, \Closure $inject = NULL) {
    $navNode = $this->newObject('NavigationNode', Array(array('de'=>'node mit nummer '.$num,'en'=>'node with number '.$num)));
    $navNode
      ->setLft(0)
      ->setRgt(1)
      ->setDepth(0)
    ;
    $navNode->setIdentifier($num);

    if (isset($inject)) $inject($navNode);

    $this->save($navNode);

    return $navNode;
  }

  protected function instancePage($num, \Closure $inject = NULL) {
    $page = $this->newObject('Page', array('page-from-instancer-'.$num));

    if (isset($inject)) $inject($page);

    $this->save($page);

    return $page;
  }

  protected function instanceParagraph($num, \Closure $inject = NULL) {
    $contents = array(
      1=>'c1: Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam',
      2=>'c2: psum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt',
      3=>'c3: ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea'
    );

    $p = $this->newObject('CS\Paragraph', array($contents[$num]));

    if (isset($inject)) $inject($p);

    $this->save($p);

    return $p;
  }

  protected function instanceContentStream($num, \Closure $inject = NULL) {
    $contentStream = $this->newObject('ContentStream', 
      array(
        $this->container->getLanguage(), 
        'cs-instance-'.$num.'-for-'.$this->container->getLanguage()
      )
    );

    if (isset($inject)) $inject($contentStream);

    return $contentStream;
  }

  /**
   * all protected instanceXXX Functions are avaible as getXXX
   * @return Entity
   */
  public function __call($method, Array $params = array()) {
    if (!isset($params[0])) {
      throw new \Psc\Exception('Method required parameter #1 to be integer: '.get_class($this).'::'.$method);
    }
    $num = $params[0];

    if ($entity = $this->hit($method, $num)) {
      return $entity;
    }

    $callable = array($this, str_replace('get', 'instance', $method));

    if (!method_exists($this, $callable[1])) {
      throw new \Psc\Exception('Method does not exist: '.get_class($this).'::'.$callable[1]);
    }

    $entity = call_user_func_array($callable, $params);
    $this->store($method, $num, $entity);

    return $entity;
  }

  protected function save($entity) {
    $em = $this->container->getDoctrinePackage()->getEntityManager();
    $em->persist($entity);
    $em->flush();
    return $this;
  }

  protected function newObject($role, array $params) {
    return \Psc\Code\Generate\GClass::newClassInstance($this->container->getRoleFQN($role), $params);
  }

  protected function store($function, $num, $entity) {
    $this->cache[$function][$num] = $entity;
  }

  protected function hit($function, $num) {
    return isset($this->cache[$function]) && isset($this->cache[$function][$num])
      ? $this->cache[$function][$num] : NULL;
  }
}
