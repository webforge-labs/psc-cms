Migration to 1.5
====================

Psc\System\Process** has been deprecated
- use Webforge\Process\* classes where you can.

Client:
Use psc-cms-js >= 1.5.x@dev to keep the javascript running 

Psc\HTML\FrameworkPage
  - signature from addCMSDefaultCSS() changed. its now ($uiTheme, $jqUIVersion)

Psc\HTML\Page
  - cssManager and jsManager are removed from the hierarchy (this includes all attach* and other methods as well)
  - The Psc\CMS\Project references are switched into Webforge\Framework\Project

Psc\CMS\Page
  - class was deleted

Psc\CSS\CSS
  - class was deleted

Psc\CMS\ProjectMain
  - cssManager and jsManager were removed
  - css ui theme is no longer read from config