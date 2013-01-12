<?php

namespace Psc\CMS\Controller;

use Psc\Form\ValidationPackage;
use Psc\Doctrine\DCPackage;
use Psc\JS\JooseSnippet;

use Psc\UI\SplitPane;
use Psc\UI\Group;
use Psc\UI\Accordion;
use Psc\UI\PanelButtons;
use Psc\UI\Form;
use Psc\UI\HTML;

/**
 * 
 */
class NavigationController extends \Psc\SimpleObject implements LanguageAware {
  
  /**
   * @var Psc\Form\ValidationPackage
   */
  protected $v;
  
  /**
   * Der NavigationsContext um den des geht (default z.B. und in den meisten Fällen)
   * 
   * @var string
   */
  protected $ident;
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  
  protected $repository;
  
  /**
   * @var array
   */
  protected $languages;
  
  /**
   * @var string
   */
  protected $language;
  
  public function __construct($ident, DCPackage $dc, $navigationNodeEntityName = NULL, Array $languages = NULL, ValidationPackage $v = NULL) {
    $this->ident = $ident;
    $this->setValidationPackage($v ?: new ValidationPackage());
    $this->setDoctrinePackage($dc);
    $this->entityName = $navigationNodeEntityName ?: $this->dc->getModule()->getNavigationNodeClass();
    $this->repository = $this->dc->getRepository($this->entityName);
    $this->repository->setContext($this->ident);
    $this->languages = $languages;
  }
  
  public function getFormular() {
    $pane = new SplitPane(70);
    $pane->setLeftContent(
      $container = Form::group('Navigation', NULL)
    );
    $container->getContent()->div->setStyle('min-height','600px');
    $container->addClass('\Psc\navigation');
    
    $pane->setRightContent(
      \Psc\UI\Group::create('',array(
        Form::hint('Die Navigations-Ebenen sind von links nach rechts zu lesen. Die Zuordnung der Unterpunkte zu Hauptpunkten '.
                 'ist von oben nach unten zu lesen.'."\n".
                 'Die Hauptnavigation besteht aus den Navigations-Punkten, die überhaupt nicht eingerückt sind. '.
                 'Jede weitere Einrückung bedeutet ein tiefere Ebene in der Navigation.'
                 ).'<br />',
        '<br />',
        
      ))->setStyle('margin-top','7px')
      //Accordion::create(array('autoHeight'=>true))
      //  ->addSection('Seiten', array(
      //  ))
      //  ->html()
      //    ->addClass('\Psc\right-accordion')
    );
    
    $panelButtons = new PanelButtons(array('save', 'reload'));
    
    $form = new \Psc\CMS\Form(NULL, '/cms/navigation/'.$this->ident, 'post');
    //$this->setHTTPHeader('X-Psc-Cms-Request-Method', 'PUT');
    
    //$main = HTML::tag('div', Array(
    //  $panelButtons,
    //  $pane
    //));
    $form->setContent('buttons', $panelButtons)
         ->setContent('pane', $pane)
    ;
    
    $main = $form->html();
    $main->addClass('\Psc\navigation-container');
    $main->addClass('\Psc\serializable');
    
    $snippet = JooseSnippet::create(
      'Psc.UI.Navigation',
      array(
        'widget'=>JooseSnippet::expr(\Psc\JS\jQuery::getClassSelector($main)),
        'flat'=>$this->repository->getFlatForUI($this->language ?: 'de', $this->languages)
      )
    );
    
    $main->templateAppend($snippet->html());

    
    return $main;
  }
  
  public function saveFormular(Array $flat) {
    \Psc\Doctrine\Helper::enableSQLLogging('stack', $em = $this->dc->getEntityManager());
    $logger = $this->repository->persistFromUI($flat, $this->dc->getModule());
    
    return array(
      'status'=>TRUE,
      'log'=>$logger->toString(),
      'context'=>$this->repository->getContext(),
      'sql'=>\Psc\Doctrine\Helper::printSQLLog('/^(INSERT|UPDATE|DELETE)/', TRUE, $em)
    );
  }
  
  /**
   * @param Psc\Form\ValidationPackage $v
   */
  public function setValidationPackage(ValidationPackage $v) {
    $this->v = $v;
    return $this;
  }
  
  /**
   * @return Psc\Form\ValidationPackage
   */
  public function getValidationPackage() {
    return $this->v;
  }
  
  /**
   * @param Psc\Doctrine\DCPackage $dc
   */
  public function setDoctrinePackage(DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    return $this->dc;
  }
  
  /**
   * @param string $ident
   * @chainable
   */
  public function setIdent($ident) {
    $this->ident = $ident;
    return $this;
  }

  /**
   * @return string
   */
  public function getIdent() {
    return $this->ident;
  }
  
  public function getRepository() {
    return $this->repository;
  }
  
  /**
   * @param array $languages
   * @chainable
   */
  public function setLanguages(Array $languages) {
    $this->languages = $languages;
    return $this;
  }

  /**
   * @return array
   */
  public function getLanguages() {
    return $this->languages;
  }

  /**
   * @param string $language
   * @chainable
   */
  public function setLanguage($language) {
    $this->language = $language;
    return $this;
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }
}
?>