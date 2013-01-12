<?php

namespace Psc\HTML;

use Psc\JS\Helper as js;
use Psc\CSS\Helper as css;

class Page5Framework extends Page5 {
  
  /**
   * @var string ohne - dahinter
   */
  protected $titlePrefix;

  public function addTwitterBootstrapCSS() {
    $this->loadCSS('/psc-cms-js/vendor/twitter-bootstrap/css/bootstrap.css');
    $this->loadCSS('/psc-cms-js/vendor/twitter-bootstrap/css/bootstrap-responsive.css');
  }

  
  public function loadCSS($url, $media = 'all') {
    $this->head->content[$url] = css::load($url, $media);
    return $this;
  }

  public function loadJS($url) {
    $this->head->content[$url] = js::load($url);
    return $this;
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
}
?>