<?php

namespace Psc\Entities;

use Psc\Data\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Psc\Entities\FileRepository")
 * @ORM\Table(name="files")
 */
class File extends CompiledFile {
  
  public function getEntityName() {
    return 'Psc\Entities\File';
  }
}
