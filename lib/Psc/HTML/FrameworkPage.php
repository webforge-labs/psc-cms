<?php

namespace Psc\HTML;

use Psc\JS\Helper as js;
use Psc\CSS\Helper as css;
use Webforge\Framework\Project;

class FrameworkPage extends Page {

  protected $cmsCSSPrefix = '/css';

  /**
   * @var string ohne - dahinter
   */
  protected $titlePrefix;

  public function addCMSDefaultCSS($uiTheme = 'smoothness', $jqUIVersion = '1.8.22') {
    /* Frontend CSS Files */
    $this->loadCSS($this->cmsCSSPrefix.'/reset.css');
    $this->loadCSS($this->cmsCSSPrefix.'/colors.css');
    $this->loadCSS($this->cmsCSSPrefix.'/default.css');
    $this->loadCSS('/psc-cms-js/vendor/jqwidgets/styles/jqx.base.css');
    $this->loadCSS('/psc-cms-js/vendor/jqwidgets/styles/jqx.ui-'.$uiTheme.'.css');
    $this->loadCSS('/psc-cms-js/vendor/jquery-ui/css/'.$uiTheme.'/jquery-ui-'.$jqUIVersion.'.custom.css');
    $this->loadCSS($this->cmsCSSPrefix.'/cms/form.css');
    $this->loadCSS('/psc-cms-js/css/ui.css');
  }

  public function addTwitterBootstrapCSS() {
    $this->loadCSS('/psc-cms-js/vendor/twitter-bootstrap/css/bootstrap.css');
    $this->loadCSS('/psc-cms-js/vendor/twitter-bootstrap/css/bootstrap-responsive.css');
    $this->loadCSS('/psc-cms-js/vendor/twitter/typeahead/css/typeahead.js-bootstrap.css');
  }

/**
   * Use development as $assetMode to use non-compiled assets
   * 
   * use other values or 'built' to the compiled files and minified js
   */
  public function addCMSRequireJS($assetModus = 'development') {
    if ($assetModus === 'development') {
      $this->loadJs('/psc-cms-js/lib/config.js');
      $requirejs = '/psc-cms-js/vendor/require.js';
      $main = '/js/boot.js';
    } else {
      $requirejs = '/js-built/require.js';
      $main = '/js-built/lib/boot.js';
    }

    return $this->addRequireJS($requirejs, $main);
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
  
  public function addRequireJS($requirejsUrl = NULL, $mainJS = NULL) {
    $this->loadJS($requirejsUrl ?: '/js/require.js')
      ->setAttribute('data-main', $mainJS ?: '/js/boot.js')
      ->setOption('br.closeTag',FALSE);

    return $this;
  }

  public function setTitleString($title) {
    $project = \Psc\PSC::getProject();
    $staging = $project instanceof Project && $project->isStaging() ? 'staging - ' : NULL;
    
    return parent::setTitleString(($this->titlePrefix ? $this->titlePrefix.' - ' : '').$staging.$title);
  }
}
