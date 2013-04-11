<?php

namespace Psc\TPL\ContentStream;

use Closure;

interface TemplateEntry {

  /**
   * Returns the Template Variables for the entry, exportet for a template engine
   * 
   * it MUST NOT return plain values. Array or Object of stdClass
   * 
   * @param Clousure $convert you can use this to convert another Entry e.g. $exportTemplateEntry($this->image)
   * @return Traversable
   */
  public function getTemplateVariables(Closure $exportTemplateEntry);

}
