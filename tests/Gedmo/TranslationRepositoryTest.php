<?php

namespace Psc\Gedmo;

/**
 * @group class:Psc\Gedmo\TranslationRepository
 */
class TranslationRepositoryTest extends \Psc\Code\Test\Base {
  
  protected $translationRepository;
  
  public function setUp() {
    $this->chainClass = 'Psc\Gedmo\TranslationRepository';
    parent::setUp();
    
    $this->em = \Psc\PSC::getProject()->getModule('Doctrine')->getEntityManager();
    $this->inputField = (object)  array(
      'de'=>'Der Titel auf Deutsch',
      'en'=>'the title in english',
      'fr'=>'L\'intitulé même de ce rapport est déjà un mensonge.'
    );
    $this->entityClass = 'Psc\CMS\Navigation\NodeTranslation';
    $this->entityMeta = $this->getEntityMeta($this->entityClass);
    $this->repository = new TranslationRepository($this->em, $cm = $this->entityMeta->getClassMetadata());
  }
  
  public function testAcceptance() {
    $entity = new \Psc\CMS\Navigation\Node();
    
    $this->repository->synchronizeTranslationField(
      $entity, $this->getEntityMeta('Psc\CMS\Navigation\Node'),
      'title', $this->inputField,
      $this->entityMeta
    );
    
    $translations = $entity->getTranslationsByField('title');
    $i18n = array();
    foreach ($translations as $translation) {
      $i18n[$translation->getLocale()] = $translation->getContent();
    }
    
    $this->assertEquals(array('de'=>$this->inputField->de,
                              'en'=>$this->inputField->en,
                              'fr'=>$this->inputField->fr
                            ),
                        $i18n
                      );
  }
}
?>