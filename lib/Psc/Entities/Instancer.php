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
    $navNode = $this->newObject(
      'NavigationNode', Array(
        array(
          'de'=>'node mit nummer '.$num,
          'en'=>'node with number '.$num, 
          'es'=>'nodas numeras '.$num
        )
      )
    );
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
      NULL,
      'c1: **Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.**',
      'c2: In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula. [[http://www.google.de|Textlink »]]. Porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus ». Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt.',
      'c3: Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt.',
      'c4: Sed consequat, leo eget bibendum sodales, ** Fette Schrift kann durch das umschließen von ** entstehen velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla.',
      'c5: [quote] Zur besonderen Auszeichnung kann die kursive Schrift auch vom normalen Textfluß eingerückt sein. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris.[/quote]',
      'c6: Das Bild neben diesem Text steht links. In ac felis quis tortor malesuada pretium. Pellentesque auctor neque nec urna. Proin sapien ipsum, porta a, auctor quis, euismod ut, mi. Aenean viverra rhoncus pede. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut non enim eleifend felis pretium feugiat. Vivamus quis mi. Phasellus a est. Phasellus magna. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis. Curabitur a felis in nunc fringilla tristique.',
      'c7: Morbi mattis ullamcorper velit. Phasellus gravida semper nisi. Nullam vel sem. Pellentesque libero tortor, tincidunt et, tincidunt eget, semper nec, quam. Sed hendrerit. Morbi ac felis. Nunc egestas, augue at pellentesque laoreet, felis eros vehicula leo, at malesuada velit leo quis pede. Donec interdum, metus et hendrerit aliquet, dolor diam sagittis ligula, eget egestas libero turpis vel mi. Nunc nulla. Fusce risus nisl, viverra et, tempor et, pretium in, sapien. Donec venenatis vulputate lorem. Morbi nec metus. Phasellus blandit leo ut odio. Maecenas ullamcorper, dui et placerat feugiat, eros pede varius nisi, condimentum viverra felis nunc et lorem.',
      'c8: Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus.'
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
