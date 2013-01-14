<?php

namespace Psc\System;

use Webforge\Common\System\Dir;

class SVNMiniClient extends \Psc\SimpleObject {
  
  public function commitSinglePath(WebforgeDir $repositoryDir, $path, $contents, $commitMessage = NULL) {
    // wir müssen das repository auschecken (geht leider nicht anders)
    // wir checken das rep leer aus und machen update für den path
    $repoFile = $repositoryDir->getFile($path);
    
    $directoryUrl = clone $repoFile->getDirectory();
    $directoryUrl->wrapWith('file');
    
    $wc = Dir::createTemporary();
    
    $this->executeCommand(
      'svn co '.$directoryUrl.' '.$wc->getQuotedString(Dir::WITHOUT_TRAILINGSLASH) //  mag keine trailingslashs
    );
    
    $wcFile = $wc->getFile($repoFile->getName()); // wir haben ja das verzeichnis direkt ausgecheckt, deshalb brauchen wir hier nur den dateinamen
    $wcFile->writeContents($contents);
    
    $this->executeCommand(
      'svn commit'.($commitMessage ? ' -m'.escapeshellarg($commitMessage) : '').' '.$wcFile->getQuotedString()
    );
    
    $wc->delete();
  }
  
  protected function executeCommand($cmd) {
    //print $cmd."\n";
    return exec($cmd);
  }
}
?>n