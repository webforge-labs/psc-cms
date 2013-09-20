<?php

namespace Psc\System\Deploy;

use Psc\CMS\Project;
use Psc\TPL\TPL;

/**
 * @TODO diese Klasse würde viel schicker werden, wenn wir es andersrum machen:
 *
 * wir schreiben die .conf datei als template und ersetzen die Variablen die Dynamisch gemacht werden müssen
 * somit könnten wir auch für lokale hosts die config datei einfacher erzeugen
 */
class ConfigureApacheTask extends \Psc\SimpleObject implements Task {
  
  // siehe setTemplate()
  protected $template = NULL;

  protected $phpValues;
  
  protected $serverName;
  protected $serverNameCms;
  protected $serverAliasCms;
  protected $serverAlias;
  
  protected $documentRoot;
  protected $documentRootCms;
  protected $customLog;
  
  protected $vars = array();
  
  protected $targetProject;
  protected $host;

  /**
   * Der Inhalt der .htaccess datei die deployed werden soll
   * 
   * @var string
   */
  protected $htaccess;

  /**
   * Wird in htdocs-cms deployed
   * @var string
   */
  protected $cmsHtaccess;
  
  /**
   * @param string $host der Name des Hosts auf den deployed wird. es wird dann conf/$host.conf erstellt
   */
  public function __construct(Project $targetProject, $hostName) {
    $this->host = $hostName;
    $this->setTemplate('default');
    
    $vhost = $targetProject->getVhostName();
    
    $this->customLog = '/var/log/apache2/access.'.$vhost.'.log combined';

    $this->targetProject = $targetProject;
    if ($this->targetProject->loadedFromPackage) {
      $this->documentRoot = '/var/local/www/'.$vhost.'/www';
      $this->documentRootCms = '/var/local/www/'.$vhost.'/www/cms';
    } else {
      $this->documentRoot = '/var/local/www/'.$vhost.'/base/htdocs';
      $this->documentRootCms = '/var/local/www/'.$vhost.'/base/htdocs-cms';
    }
    

    $this->phpValues = array(
        'log_errors'=>array('admin','On'),
        'error_log'=>array('admin', '/var/local/www/'.$vhost.'/logs/php_error_log'),
        'upload_max_filesize'=>array('admin', '30M'),
        'post_max_size'=>array('admin','30M'),
        'include_path'=>array(NULL, '/var/local/www/'.$vhost.'/base/src/'),
        'auto_prepend_file'=>array(NULL, 'auto.prepend.php'),
        //'memory_limit'=>array('admin', '1024M'), // geht in apache nicht höher als 1024 MB oder sowas
        'error_reporting'=>array(NULL, '32767'),
        'display_errors'=>array('admin', 'On')
    );
    
    $this->vars = array('appendix'=>'  ','auth'=>'', 'mainAppendix'=>'');

    if ($this->targetProject->loadedFromPackage) {
      $this->phpValues['auto_prepend_file'] = array(NULL, $this->replaceHelpers('%vhost%bootstrap.php'));

      $this->setVar('aliases', 
        'Alias /dimg /var/local/www/'.$vhost.'/files/cache/images'."\n  ".
        'Alias /images /var/local/www/'.$vhost.'/files/images'
      );
    } else {
      $this->setVar('aliases', 
        'Alias /dimg /var/local/www/'.$vhost.'/base/cache/images'."\n  ".
        'Alias /images /var/local/www/'.$vhost.'/base/files/images'
      );

    }
  }
  
  public function run() {
    if (isset($this->htaccess)) {
      $this->targetProject->getHtdocs()
        ->getFile('.htaccess')
          ->writeContents($this->htaccess);
    }
    
    if (isset($this->cmsHtaccess)) {

      if ($this->targetProject->loadedFromPackage) {
        $cmsTargetDir = $this->targetProject->getBase()->sub('www/cms/')->create();
      } else {
        $cmsTargetDir = $this->targetProject->getBase()->sub('htdocs-cms/')->create();
      }

      $cmsTargetDir
        ->getFile('.htaccess')
          ->writeContents($this->cmsHtaccess);
    }
    
    $vars = array();
    foreach (array('serverName','serverNameCms','serverAlias','serverAliasCms','documentRoot','documentRootCms','customLog') as $var) {
      $vars[$var] = $this->$var;
    }
    
    $vars['phpValues'] = NULL;
    foreach ($this->phpValues as $name => $list) {
      list($type, $value) = $list;
      $vars['phpValues'] .= sprintf("  php%s_value %s %s\n",
                                    $type === 'admin' ? '_admin' : NULL, $name, $value);
    }
    
    $vars = array_merge($vars, $this->vars); // volle power für setVar
    
    // new style
    $etc = $this->targetProject->getRoot()->sub('etc/');
    if ($etc->exists()) {
      $conf = $etc->sub('apache2/')->create();
    } else {
      // old style
      $conf = $this->targetProject->getRoot()->sub('conf/')->create();
    }
    
    $conf
      ->getFile($this->host.'.conf')
        ->writeContents(\Psc\TPL\TPL::miniTemplate($this->template, $vars))
    ;
  }
  
  public function setPHPValue($name, $value) {
    $this->phpValues[$name] = array(NULL, $value);
    return $this;
  }
  
  public function setPHPAdminValue($name, $value) {
    $this->phpValues[$name] = array('admin', $value);
    return $this;
  }
  
  public function setVar($name, $value) {
    $this->vars[$name] = $value;
    return $this;
  }

  public function getVar($name) {
    return $this->vars[$name];
  }

  public function append($string) {
    $this->vars['appendix'] .= $string;
    return $this;
  }

  /**
   * Adds configuration after the virtualhosts
   */
  public function after($string) {
    $this->vars['mainAppendix'] = $this->replaceHelpers($string);
    return $this;
  }

  /**
   * Adds an Location alias 
   * 
   * $this->addAlias('/images /var/local/banane');
   */
  public function addAlias($location, $path) {
    $this->setVar('aliases', 
      $this->getVar('aliases').
      sprintf("\n  Alias %s %s", $location , $this->replaceHelpers($path))
    );
    return $this;
  }
  
  /**
   * @param string $filename relativ zu inc
   */
  public function setAutoPrependFile($filename) {
    $this->setPHPValue('auto_prepend_file', $this->replaceHelpers($filename));
    return $this;
  }
  
  public function setIncludePath($filename) {
    $this->setPHPValue('include_path', $this->replaceHelpers($filename));
    return $this;
  }
  
  /**
   * @param string $documentRoot kann %vhost% als variable haben (wird mit absolutem pfad mit / am ende ersetzt)
   * @chainable
   */
  public function setDocumentRoot($documentRoot) {
    $this->documentRoot = $this->replaceHelpers($documentRoot);
    return $this;
  }
  
  protected function replaceHelpers($string) {
    $vhost = $this->targetProject->getVhostName();
    return TPL::miniTemplate(
      $string, 
      array(
        'vhost'=>'/var/local/www/'.$vhost.'/',
        'documentRoot'=>$this->documentRoot,
        'documentRootCms'=>$this->documentRootCms,
        'serverName'=>$this->serverName
      )
    );
  }

  /**
   * @return string
   */
  public function getDocumentRoot() {
    return $this->documentRoot;
  }

  /**
   * @param string $customLog
   * @chainable
   */
  public function setCustomLog($customLog) {
    $this->customLog = $customLog;
    return $this;
  }

  /**
   * @return string
   */
  public function getCustomLog() {
    return $this->customLog;
  }

  /**
   * @param string $serverName
   * @chainable
   */
  public function setServerName($serverName) {
    $this->serverName = $serverName;
    if (!isset($this->serverNameCms)) {
      $this->serverNameCms = 'cms.'.$this->serverName;
    }
    return $this;
  }

  /**
   * @param string $serverName
   * @chainable
   */
  public function setServerNameCms($serverName) {
    $this->serverNameCms = $serverName;
    return $this;
  }

  /**
   * @param string $serverName
   * @chainable
   */
  public function setServerAliasCms($serverName) {
    $this->serverAliasCms = $serverName;
    return $this;
  }

  /**
   * @return string
   */
  public function getServerName() {
    return $this->serverName;
  }

  /**
   * @param string $serverAlias
   * @chainable
   */
  public function setServerAlias($serverAlias) {
    if (is_array($serverAlias))
      $serverAlias = implode(' ', $serverAlias);
      
    $this->serverAlias = $serverAlias;
    return $this;
  }

  /**
   * @return string
   */
  public function getServerAlias() {
    return $this->serverAlias;
  }

  /**
   * @return array
   */
  public function getPHPValues() {
    return $this->phpValues;
  }
  
  /**
   * @param string $htaccess
   * @chainable
   */
  public function setHtaccess($htaccess) {
    $this->htaccess = $htaccess;
    return $this;
  }

  /**
   * @return string
   */
  public function getHtaccess() {
    return $this->htaccess;
  }

  /**
   * @param string $htaccess
   * @chainable
   */
  public function setCmsHtaccess($htaccess) {
    $this->cmsHtaccess = $htaccess;
    return $this;
  }

  /**
   * @return string
   */
  public function getCmsHtaccess() {
    return $this->cmsHtaccess;
  }
  
  public function setPublicAuth($authFile = NULL, $authName = NULL) {
    return $this->setAuth('/', $authFile, $authName);
  }
  
  public function setAuth($location, $authFile = NULL, $authName = NULL) {
    $vhost = $this->targetProject->getVhostName();
    $authFile = $this->replaceHelpers( $authFile ?: '%vhost%base/auth/public');
    $authName = $authName ?: $vhost.' public authentication';
    
    $this->append('
  <Location '.$location.'>
    AuthType basic
    AuthUserFile '.$authFile.'
    AuthName "'.$authName.'"
    
    Require valid-user
   </Location>
');
    return $this;
  }
  
  public function setTemplate($type = NULL) {
    if ($type === 'cms-public') {
      $this->template = <<<'APACHE'
<VirtualHost *:80>
  ServerName %serverName%
  ServerAlias %serverAlias%

  DocumentRoot %documentRoot%
  CustomLog %customLog%
  
  %aliases%

%phpValues%
  <Directory %documentRoot%>
    AllowOverride All
  </Directory>
%appendix%
</VirtualHost>

<VirtualHost *:80>
  ServerName %serverNameCms%
  ServerAlias %serverAliasCms%

  DocumentRoot %documentRootCms%
  
  %aliases%
  
%phpValues%
  <Directory %documentRootCms%>
    AllowOverride All
  </Directory>
</VirtualHost>
%mainAppendix%
APACHE;

    } else {
      $this->template = <<<'APACHE'
<VirtualHost *:80>
  ServerName %serverName%
  ServerAlias %serverAlias%

  DocumentRoot %documentRoot%
  CustomLog %customLog%

  %aliases%
  
%phpValues%
  <Directory %documentRoot%>
    AllowOverride All
  </Directory>
%appendix%
</VirtualHost>
%mainAppendix%
APACHE;
      
    }
    return $this;
  }
}
?>