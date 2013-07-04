<?php

namespace Psc\CMS\Controller;

class UserEntityController extends \Psc\CMS\Controller\AbstractEntityController {
  
  public function setUp() {
    $this->optionalProperties[] = 'password';
    $this->addBlackListProperty('password', 'grid');
    parent::setUp();

    $this->initPropertyTranslations('cms');
  }
  
  protected function initProcessor(\Psc\Doctrine\Processor $processor) {
    $processor->onProcessSetField('password', function ($entity, $field, $value, $type) {
      $entity->hashPassword($value);
    });
    parent::initProcessor($processor);
  }

  protected function initGridPanel(\Psc\CMS\EntityGridPanel $panel) {
    $this->blackListProperties[] = 'password';
    parent::initGridPanel($panel);
  }
  
  public function getEntityName() {
    return $this->dc->getEntityMeta('User')->getGClass()->getFQN();
  }
}
