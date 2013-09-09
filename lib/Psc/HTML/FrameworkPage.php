<?php

namespace Psc\HTML;

use Psc\JS\Helper as js;
use Psc\CSS\Helper as css;
use Psc\CMS\Project;

class FrameworkPage extends Page {
  
  /**
   * @var string ohne - dahinter
   */
  protected $titlePrefix;

  public function __construct(\Psc\JS\Manager $jsManager = NULL, \Psc\CSS\Manager $cssManager = NULL) {
    $jsManager = $jsManager ?: new \Psc\JS\ProxyManager();
		
		parent::__construct($jsManager, $cssManager);
	}
	
	public function addCMSDefaultCSS() {
    $this->cssManager->enqueue('default');
    $this->cssManager->enqueue('jquery-ui');
    $this->cssManager->enqueue('cms.form');
    $this->cssManager->enqueue('cms.ui');
	}

	public function addTwitterBootstrapCSS() {
    $this->loadCSS('/psc-cms-js/vendor/twitter-bootstrap/css/bootstrap.css');
    $this->loadCSS('/psc-cms-js/vendor/twitter-bootstrap/css/bootstrap-responsive.css');
    $this->loadCSS('/psc-cms-js/vendor/twitter/typeahead/css/typeahead.js-bootstrap.css');
  }

	public function setTitleForProject(Project $project) {
		$config = $project->getConfiguration();
		
	  $title = $config->get('project.title', 'Psc - CMS');
    $title .= ' '.$config->get('version');
    
    $this->setTitle(HTML::tag('title',HTML::esc($title)));
	}

  public function addGoogleAnalyticsJS($account, $domainName) {
		$this->head->content['google-analytics'] = sprintf(<<<'JAVASCRIPT'
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '%s']);
  _gaq.push(['_trackPageview']);
  _gaq.push(['_setDomainName', '%s']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
JAVASCRIPT
    , $account, $domainName);
  }
  
  public function addRequireJS() {
    $this->head->content['require.js'] = js::load('/psc-cms-js/vendor/require.js')
                                          ->setAttribute('data-main', '/js/config.js')
                                          ->setOption('br.closeTag',FALSE);
    return $this;
  }

	public function setTitleString($title) {
    $project = \Psc\PSC::getProject();
		$staging = $project instanceof \Psc\CMS\Project && $project->isStaging() ? 'staging - ' : NULL;
		
		return parent::setTitleString(($this->titlePrefix ? $this->titlePrefix.' - ' : '').$staging.$title);
	}

  /**
   * @return Psc\HTML\Tag
   */
  public function loadCSS($url, $media = 'all') {
    $this->head->content[$url] = $link = css::load($url, $media);
    return $link;
  }

  /**
   * @return Psc\HTML\Tag
   */
  public function loadJS($url) {
    $this->head->content[$url] = $script = js::load($url);
    return $script;
  }  

  /**
   * @return Psc\HTML\Tag
   */
  public function loadConditionalJS($url, $condition) {
    $this->head->content[$url] =
      '<!--[if '.$condition.']>'.
      ($script = js::load($url)).
      '<![endif]-->';
    
    return $script;
  } 

  /**
   * @return Psc\HTML\Tag
   */
  public function loadConditionalCSS($url, $condition, $media = 'all') {
    $this->head->content[$url] =
      '<!--[if '.$condition.']>'.
      ($css = css::load($url, $media)).
      '<![endif]-->';
    
    return $css;
  } 
}
