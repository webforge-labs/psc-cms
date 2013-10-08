Migration to 1.5
====================

## Major changes

Psc\CMS\Project:
  - Psc\CMS\Container does not return a Psc\CMS\Project on getProject() anymore. It returns a Webforge\Framework\Package\ProjectPackage instead (which also implements Webforge\Framework\Project). You have to  hint EVERYTHING for Webforge\Framework\Project.
  - the bootstrap function from project is no longer functional
  - no EVENT_BOOTSTRAPPED is triggered anymore

- The include path is no longer set automatically to src(!). Make sure that you can load everything with composer

## API changes

Psc\Doctrine\DCPackage
- Module is no longer optional

Psc\CMS\UploadManager
- use createForProject instead of empty constructor

Psc\CMS\Controller\FileUploadController
- UploadManager is required for constructor

Psc\CMS\Configuration
- class was removed. Use Webforge\Configuration\Configuration in place

ProjectMain:
 - getMainService from Project is no longer used

Psc\System\Process** has been deprecated
- use Webforge\Process\* classes where you can.

Psc\CMS\ContactFormMailer
- needs the configuration as first parameter now
- the debugRecipient can be set or not set (no development / no production flags anymore)

Client:
Use psc-cms-js >= 1.5.x@dev to keep the javascript running 

Psc\HTML\FrameworkPage
  - signature from addCMSDefaultCSS() changed. its now ($uiTheme, $jqUIVersion)

Psc\HTML\Page
  - cssManager and jsManager are removed from the hierarchy (this includes all attach* and other methods as well)
  - The Psc\CMS\Project references are switched into Webforge\Framework\Project

Psc\HTML\Page5, Psc\HTML\FrameworkPage
  - cssManager and jsManager were removed

Psc\CMS\Page
  - class was deleted

Psc\CSS\CSS
  - class was deleted

Psc\JS\JS
  - class was deprecated

Psc\CMS\ProjectMain
  - cssManager and jsManager were removed
  - css ui theme is no longer read from config

Psc\JS\RequirejsManager
  - class was deprecated

Psc\JS\jQuery
  - the class was shrinked to the minimum. See Webforge\DOM (webforge/dom) on packagist for details