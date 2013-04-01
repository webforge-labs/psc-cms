<?php

namespace Psc\Doctrine;

use Psc\TPL\ContentStream\ContentStream;
use Psc\Doctrine\EntityNotFoundException;
use Doctrine\ORM\NoResultException;

class PageRepository extends \Psc\Doctrine\EntityRepository {
  
  public function hydrate($identifierOrSlug) {
    if (is_integer($identifierOrSlug) || ((int) $identifierOrSlug) > 0) {
      return parent::hydrate($identifierOrSlug);
    } else {
      return parent::hydrateBy(array('slug'=>$identifierOrSlug));
    }
  }
  
  /**
   * @return Psc\CMS\Roles\Page
   */
  public function hydrateByContentStream(ContentStream $cs) {
    $dql = "SELECT page FROM ".$this->_entityName." AS page ";
    $dql .= "JOIN page.contentStreams as contentStream ";
    $dql .= "WHERE contentStream.id = :csid ";
    
    $q = $this->_em->createQuery($dql);
    $q->setParameter('csid', $cs->getId());
    
    try {    
      return $q->getSingleResult();
    } catch(NoResultException $e) {
      throw new EntityNotFoundException(
        sprintf("Page für ContentStream nicht gefunden: identifier: %s", $cs->getId())
      );
    }
  }
}
