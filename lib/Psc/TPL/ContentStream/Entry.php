<?php

namespace Psc\TPL\ContentStream;

interface Entry extends TemplateEntry {

  /**
   * @param Closure $serializeEntry a helper to serialize sub items
   * @return array
   */
  public function serialize($context, \Closure $serializeEntry);

  /**
   * @return string the name of the JS Class without Psc.UI.LayoutManagerComponent.
   */
  public function getType();
}
