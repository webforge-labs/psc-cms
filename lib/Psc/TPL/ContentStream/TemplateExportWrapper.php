<?php

namespace Psc\TPL\ContentStream;

use Closure;

abstract class TemplateExportWrapper implements TemplateEntry, ContextAware {
  // TODO: add interfaces for contentStream Aware and ConverterAware (if this is needed by exporter)

  /**
   * @var Psc\TPL\ContentStream\Context
   */
  protected $context;

  /**
   * @var Psc\TPL\ContentStream\Converter
   */
  protected $converter;

  /**
   * @var Psc\TPL\ContentStream\ContentStream
   */
  protected $contentStream;

  /**
   * @var Closure
   */
  private $convertHelper;

  /**
   * Returns the Template Variables for the entry, exported for a template engine
   * 
   * it MUST NOT return plain values. Array or Object of stdClass
   * 
   * @param Clousure $convert you can use this to convert another Entry e.g. $convert($this->image)
   * @param Psc\TPL\ContentStream\Converter $converter
   * @param Psc\TPL\ContentStream\ContentStream $contentStream
   * @return Traversable
   */
  public function getTemplateVariables(Closure $convert) {
    return (object) $this->exportTemplateVariables($convert);
  }

  /**
   * Returns the Template variables for the entry
   * 
   * @param Closure $exportEntry use this to export the variables of another entry
   * @return Traversable
   */
  abstract protected function exportTemplateVariables(Closure $exportEntry);

  public function setContext(Context $context) {
    $this->context = $context;
    return $this;
  }
}
