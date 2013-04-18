<?php

namespace Psc\CMS\Controller;

abstract class ContentStreamContainerController extends ContainerController {

  protected $contentStreamDefaultType = 'page-content';

  public function getEntityInRevision($parentIdentifier, $revision, $subResource = NULL, $query = NULL) {
    if (is_array($subResource) && $subResource[0] === 'contentstream') {
      $contentStreamController = $this->getController('ContentStream');

      $contentStream = $contentStreamController->prepareFor(
        $entity = parent::getEntityInRevision($parentIdentifier, $revision, NULL, $query),
        $type = isset($subResource[2]) ? $subResource[2] : $this->contentStreamDefaultType,
        $locale = $subResource[1]
      );

      return $contentStreamController->getEntityFormular($contentStream);
    }

    return parent::getEntityInRevision($parentIdentifier, $revision, $subResource, $query);
  }
}
