<?php

namespace Psc\UI\LayoutManager;

use stdClass;

/**
 * 
 * var teaser = new Psc.UI.LayoutManager.TemplateWidget({
 *   specification: teaserSpec,
 *   uploadService: dm.getUploadService()
 * });
 * 
 * spec (something like that): this may be outdated!
 * {
 *   "name": "Teaser",
 * 
 *   "fields": {
 *     "headline": { "type": "string", "label": "Überschrift", "defaultValue": "die Überschrift" },
 *     "image": { "type": "image", "label": "Bild" },
 *     "text": { "type": "text", "label": "Inhalt", "defaultValue": "Hier ist ein langer Text, der dann in der Teaserbox angezeigt wird..." },
 *     "link": {"type": "link", "label": "Link-Ziel"}
 *   }
 * } * 
 */
class TemplateWidgetControl extends Control {

  protected $specification;

  /**
   * @param json|object $specification (better: object)
   */
  public function __construct( $specification) {
    $this->specification = $specification;

    parent::__construct(
      'TemplateWidget', 
       array(
         'specification'=>$this->specification
      ), 
      isset($specification->label) ? $specification->label : $specification->name
    );
  }
}
