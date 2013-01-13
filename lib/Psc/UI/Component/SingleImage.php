<?php

namespace Psc\UI\Component;

use Psc\UI\HTML;
use Psc\UI\Group;

use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityMeta;
use Psc\Form\SelectComboBoxValidatorRule;
use Psc\Image\Image;

class SingleImage extends JavaScriptBase implements JavaScriptComponent {

  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var Psc\Image\Manager
   */
  protected $imageManager;

  /**
   * EntityMeta der Bild-Klasse
   */
  public function dpi(EntityMeta $entityMeta, DCPackage $dc) {
    $this->entityMeta = $entityMeta;
    $this->dc = $dc;
    return $this;
  }
  
  public function getInnerHTML() {
    return Group::create($this->getFormLabel() ?: 'Bild',
                         HTML::tag('input',NULL,array('type'=>'hidden','name'=>$this->getFormName(),'value'=>$this->getId()))
                        );
  }
  
  protected function initValidatorRule() {
    $this->validatorRule = new SelectComboBoxValidatorRule($this->entityMeta->getClass(), $this->dc);
  }
  
  /**
   * @return Psc\JS\Snippet
   */
  public function getJavascript() {
    return $this->createJooseSnippet(
      'Psc.UI.SingleImage',
      array(
        'widget'=>$this->findInJSComponent('> fieldset > div.content'),
        'id'=>$this->getId(),
        'url'=>$this->getUrl(),
        'formName'=>$this->getFormName(),
        'uploadService'=>$this->createJooseSnippet(
          'Psc.UploadService',
          array(
            'apiUrl'=>'/',
            'uiUrl'=>'/',
            'ajaxService'=>$this->jsExpr('main'),
            'exceptionProcessor'=>$this->jsExpr('main')
          )
        )
      )
    );
  }
  
  protected function getUrl() {
    if (($image = $this->getFormValue()) instanceof Image) {
      $this->imageManager->attach($image);
      return $image->getUrl();
    }
    return NULL;
  }

  protected function getId() {
    return ($image = $this->getFormValue()) instanceof Image ? $image->getIdentifier() : NULL;
  }
  
  /**
   * @param Psc\Image\Manager $imageManager
   * @chainable
   */
  public function setImageManager(\Psc\Image\Manager $imageManager) {
    $this->imageManager = $imageManager;
    return $this;
  }

  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager() {
    return $this->imageManager;
  }
}
?>