<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\EntryRepository")
 * @ORM\Table(name="cs_entries")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *   "headline" = "Headline", 
 *   "paragraph" = "Paragraph", 
 *   "image" = "Image", 
 *   "simpleteaser" = "SimpleTeaser", 
 *   "teaser-headline-image-text-link" = "TeaserHeadlineImageTextLink", 
 *   "wrapper" = "ContentStreamWrapper",
 *   "li" = "Li"
 * })
 */
abstract class Entry extends CompiledEntry {
  
  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel();
    }
    
    return parent::getContextLabel();
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\Entry';
  }
}
?>