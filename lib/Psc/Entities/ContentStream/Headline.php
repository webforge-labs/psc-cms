<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\HeadlineRepository")
 * @ORM\Table(name="cs_headlines")
 */
class Headline extends CompiledHeadline {

  public function html() {
    return HTML::tag(sprintf('h%d', $this->getLevel()), $this->content);
  }
    
  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel($context);
    }
    
    return parent::getContextLabel($context);
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\Headline';
  }
}
