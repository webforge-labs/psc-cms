<?php

namespace Psc\Entities\ContentStream;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\ContentStream\CompiledSimpleTeaser")
 * @ORM\Table(name="cs_simpleteasers")
 */
class SimpleTeaser extends CompiledSimpleTeaser {

  public function html() {
    return '';
  }

}
