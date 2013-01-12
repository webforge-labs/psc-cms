<?php

namespace Psc\UI;

use Psc\HTML\Tag;
use Psc\JS\jQueryTemplate;
use Psc\CMS\RequestMeta;

/**
 * 
 */
class CommentsWidget extends \Psc\HTML\JooseBase {
  
  protected $commentTemplate;
  
  /**
   * @var Psc\CMS\RequestMeta
   */
  protected $pullRequestMeta;
  
  /**
   * @var Psc\JS\jQueryTemplate
   */
  protected $countTemplate;
  
  /**
   * @param $commentsHTML irgendein HTML an welches das JooseWidget gehängt werden soll
   * @param $commentTemplate sollte irgendwo auf der Seite erreichbar sein (kann z.b. in $commentsHTML drin sein, egal)
   */
  public function __construct(Tag $commentsHTML, jQueryTemplate $commentTemplate, RequestMeta $pullRequestMeta) {
    $this->html = $commentsHTML;
    $this->commentTemplate = $commentTemplate;
    $this->setPullRequestMeta($pullRequestMeta);
    
    parent::__construct('Psc.UI.Comments', array(), array('Psc.CommentsService','Psc.UI.Template','Psc.Request'));
  }
  
  protected function doInit() {
    $countTemplate = array();
    if (isset($this->countTemplate))
      $countTemplate['countTemplate'] = $this->createJooseSnippet('Psc.UI.Template', array('jQueryTemplate'=>$this->widgetSelector($this->countTemplate)));
    
    $this->autoloadJoose(
      $this->createJooseSnippet(
        'Psc.UI.Comments', array_merge(Array(
          'widget' => $this->widgetSelector(),
          'commentTemplate' => $this->createJooseSnippet(
            'Psc.UI.Template', array(
              'jQueryTemplate'=>$this->widgetSelector($this->commentTemplate)
            )
          ),
          'service' => $this->createJooseSnippet(
            'Psc.CommentsService',
            array(
              'ajaxService'=>$this->jsExpr('main'),
              'pullRequest'=>$this->createJooseSnippet(
                'Psc.Request',
                (array) $this->pullRequestMeta->export()
              )
            )
          )
        ), $countTemplate)
      )
        ->loadOnPscReady('main')
        ->addRequirement('app/main')
        ->addRequirementAlias('nothing') // das ist Psc.UI.Comments
        ->addRequirementAlias('main')
    );
  }
  
  /**
   * @param Psc\CMS\RequestMeta $pullRequestMeta
   */
  public function setPullRequestMeta(RequestMeta $pullRequestMeta) {
    $this->pullRequestMeta = $pullRequestMeta;
    return $this;
  }
  
  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getPullRequestMeta() {
    return $this->pullRequestMeta;
  }
  
  /**
   * @param Psc\JS\jQueryTemplate $countTemplate
   */
  public function setCountTemplate(jQueryTemplate $countTemplate) {
    $this->countTemplate = $countTemplate;
    return $this;
  }
  
  /**
   * @return Psc\JS\jQueryTemplate
   */
  public function getCountTemplate() {
    return $this->countTemplate;
  }
}
?>