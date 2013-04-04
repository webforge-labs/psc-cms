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
    $image = new ContentStream\Image('/url/in/cms/to/original.png');
    $image->setImageEntity($this->getImage($num));
    return $image;
  }

  protected function instanceImage($num) {
    $manager = $this->container->getImageManager();

    $imageFile = $this->commonFiles->getFile('images/image'.$num.'.jpg');


    $image = $manager->store(
      $manager->createImagineImage($imageFile),
      NULL,
      Manager::IF_NOT_EXISTS
    );
    $manager->flush();

    return $image;
  }

  /**
   * all protected instanceXXX Functions are avaible as getXXX
   * @return Entity
   */
  public function __call($method, Array $params = array()) {
    $num = $params[0];

    if ($entity = $this->hit($method, $num)) {
      return $entity;
    }

    $entity = call_user_func(array($this, str_replace('get', 'instance', $method)), $num);
    $this->store($method, $num, $entity);

    return $entity;
  }

  protected function store($function, $num, $entity) {
    $this->cache[$function][$num] = $entity;
  }

  protected function hit($function, $num) {
    return isset($this->cache[$function]) && isset($this->cache[$function][$num])
      ? $this->cache[$function][$num] : NULL;
  }
}
