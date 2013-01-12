<?php

namespace Psc\CMS;

use Psc\Code\Code;

class UserManager extends \Psc\SimpleObject {
  
  /**
   * @var Psc\Doctrine\EntityRepository
   */
  protected $repository;
  
  public function __construct(\Psc\Doctrine\EntityRepository $userRepository) {
    $this->repository = $userRepository;
  }
  
  public function get($identifier) {
    try {
      return $this->repository->hydrate($identifier);
      
    } catch (\Psc\Doctrine\EntityNotFoundException $e) {
      throw new NoUserException('User mit dem Identifier: '.Code::varInfo($identifier).' nicht gefunden.', 0, $e);
    }
  }
}
?>