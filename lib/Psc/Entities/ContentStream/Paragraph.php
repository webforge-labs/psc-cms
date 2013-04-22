<?php

namespace Psc\Entities\ContentStream;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Webforge\Common\String as S;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\ParagraphRepository")
 * @ORM\Table(name="cs_paragraphs")
 */
class Paragraph extends CompiledParagraph {

  public function html() {
    return HTML::tag('p', TPL::miniMarkup($this->getContent()));
  }
  
  public function excerpt($length, $ender = '…') {
    return HTML::tag('p', TPL::miniMarkup(String::cutAtLast($this->getContent(), $length, ' ', $ender)));
  }

  public function serialize($context, \Closure $serializeEntry) {
    return $this->doSerialize(array('content'), $serializeEntry);
  }

  public function getLabel() {
    return 'Absatz';
  }
  
  public function getContextLabel($context = 'default') {
    if ($context === self::CONTEXT_DEFAULT) {
      return parent::getContextLabel();
    }
    
    return parent::getContextLabel();
  }
  
  public function getEntityName() {
    return 'Psc\Entities\ContentStream\Paragraph';
  }
}
?>