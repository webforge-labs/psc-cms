<?php

namespace Psc\System\Deploy;

use Psc\CMS\Project;
use Webforge\Common\System\File;

class CopyProjectSourcesTask extends \Psc\SimpleObject implements Task {
  
  protected $sourceProject, $targetProject;
  protected $ignores;
  
  protected $additionalPaths = array();
  protected $additionalFiles = array();
  
  public function __construct(Project $sourceProject, Project $targetProject, Array $ignoreSourceDirectories = array()) {
    $this->sourceProject = $sourceProject;
    $this->targetProject = $targetProject;
    $this->ignores = array_unique(array_merge($ignoreSourceDirectories, array('Proxies', 'helpers', 'vendor')));
  }
  
  public function run() {
    $ignores = '/^('.implode('|', $this->ignores).')/';
    $extensions = array('php','json','lock');
    
    // copy src
    if ($this->sourceProject->loadedFromPackage) {
      $libSource = $this->sourceProject->getRoot()->sub('lib/');
      $libTarget = $this->targetProject->getRoot()->sub('lib/');
      $libSource->copy($libTarget, $extensions, array($ignores), TRUE);
    } else {
      $srcSource = $this->sourceProject->getSrc();
      $srcTarget = $this->targetProject->getSrc();
      $srcSource->copy($srcTarget, $extensions, array($ignores), TRUE);
    }
    
    // copy root-files (packge)
    if ($this->sourceProject->loadedFromPackage) {
      $this->sourceProject->getRoot()->copy(
        $this->targetProject->getRoot(),
        array_merge($extensions, array('xml')), array($ignores), $subdirs = FALSE
      );

      // copy compoesr deploy (if avaible)
      $deployComposer = $this->sourceProject->getRoot()->getFile('composer.deploy.json');
      if ($deployComposer->exists()) {
        $deployComposer->copy($this->targetProject->getRoot()->getFile('composer.json'));
      }
    }

    // copy bin
    $this->sourceProject->getBin()->copy($this->targetProject->getBin(), NULL, NULL, TRUE);
    $this->sourceProject->getTpl()->copy($this->targetProject->getTpl(), NULL, NULL, TRUE);
    
    // if etc, copy etc
    $etc = $this->sourceProject->getBase()->sub('etc/');
    if ($etc->exists())
      $etc->copy($this->targetProject->getBase()->sub('etc/')->create(), NULL, NULL, TRUE);
    
    // copy auth
    $auth = $this->sourceProject->getBase()->sub('auth/');
    if ($auth->exists()) {
      $auth->copy($this->targetProject->getBase()->sub('auth/'));
    }
    
    // copy htdocs
    if ($this->sourceProject->loadedFromPackage) {
      $cmsHtdocs = $this->sourceProject->getBase()->sub('www/cms/');
    } else {
      $cmsHtdocs = $this->sourceProject->getBase()->sub('htdocs-cms/');
    }

    $source = new \stdClass;
    $target = new \stdClass;
    if ($cmsHtdocs->exists()) {
      $source->cmsHtdocs = $cmsHtdocs;
      $source->publicHtdocs = $this->sourceProject->getHtdocs();
      
      if ($this->targetProject->loadedFromPackage) {
        $target->cmsHtdocs = $this->targetProject->getBase()->sub('www/cms/')->create();
      } else {
        $target->cmsHtdocs = $this->targetProject->getBase()->sub('htdocs-cms/')->create();
      }
      $target->publicHtdocs = $this->targetProject->getHtdocs();
    } else {
      $source->publicHtdocs = NULL;
      $source->cmsHtdocs = $this->sourceProject->getHtdocs();
      
      $target->publicHtdocs = NULL;
      $target->cmsHtdocs = $this->targetProject->getHtdocs()->create();
    }
    
    // copy index.php und api.php in cms
    foreach (array('index.php','api.php') as $f) {
      $source->cmsHtdocs->getFile($f)->copy($target->cmsHtdocs->getFile($f));
    }
    
    // copy img, css, js
    foreach (array('css/','js/','img/') as $d) {
      if ($source->cmsHtdocs->sub($d)->exists()) {
        $source->cmsHtdocs->sub($d)->copy($target->cmsHtdocs->sub($d), NULL, NULL, TRUE);
      }
    }
    
    if (isset($source->publicHtdocs))
      $source->publicHtdocs->copy($target->publicHtdocs, NULL, NULL, TRUE);
    
    // copy fixtures files
    $this->sourceProject->getTestdata()->sub('fixtures/')->copy($this->targetProject->getTestdata()->sub('fixtures/'), NULL, NULL, TRUE);
    
    if ($this->sourceProject->getTestdata()->sub('common/')->exists()) {
      $this->sourceProject->getTestdata()->sub('common/')->copy($this->targetProject->getTestdata()->sub('common/'), NULL, NULL, TRUE);
    }
    
    // copy misc
    foreach ($this->additionalPaths as $path) {
      $this->sourceProject->getBase()->sub($path)->copy($this->targetProject->getBase()->sub($path), NULL, NULL, TRUE);
    }
    
    foreach ($this->additionalFiles as $url) {
      File::createFromURL($url, $this->sourceProject->getBase())->copy(
        File::createFromURL($url, $this->targetProject->getBase())
      );
    }
  }
  
  /**
   * ohne / davor. Relativ zu base mit trailing slash nur forward slashes
   * wird rekursiv kopiert
   */
  public function addAdditionalPath($additionalPath) {
    $this->additionalPaths[] = $additionalPath;
    return $this;
  }
  
  /**
   * @param array $additionalPaths
   * @chainable
   */
  public function setAdditionalPaths($additionalPaths) {
    $this->additionalPaths = $additionalPaths;
    return $this;
  }

  /**
   * @return array
   */
  public function getAdditionalPaths() {
    return $this->additionalPaths;
  }

  /**
   * ohne / davor. Relativ zu base mit trailing slash nur forward slashes
   * wird rekursiv kopiert
   */
  public function addAdditionalFile($additionalFile) {
    $this->additionalFiles[] = $additionalFile;
    return $this;
  }
  
  /**
   * @param array $additionalFiles
   * @chainable
   */
  public function setAdditionalFiles($additionalFiles) {
    $this->additionalFiles = $additionalFiles;
    return $this;
  }

  /**
   * @return array
   */
  public function getAdditionalFiles() {
    return $this->additionalFiles;
  }
}
?>