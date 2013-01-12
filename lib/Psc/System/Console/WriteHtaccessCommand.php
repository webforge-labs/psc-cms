<?php

namespace Psc\System\Console;

class WriteHtaccessCommand extends Command {

  protected function configure() {
    //$this->addArgument('file',self::REQUIRED);
    //$this->addArgument('class',self::REQUIRED);
    //$this->addOption('out','o',self::VALUE_REQUIRED);
    $this
      ->setName('cms:write-htaccess')
      ->setDescription(
        'Ueberschreibt die .htaccess mit einer automatisch erzeugten. ACHTUNG: Keine Abfrage davor!'
      );
  }
  
  protected function doExecute($input, $output) {
    $project = $this->getProject();
    $htaccess = $project->getHtdocs()->getFile('.htaccess');
    
    $output->writeln('Schreibe Datei: '.$htaccess);
    $htaccess->writeContents($this->getHtaccessContents());
    $output->writeln('finished.');
  }
  
  protected function getHtaccessContents() {
return <<<'HTACCESS'
Options FollowSymLinks

RewriteEngine on
RewriteBase /

# benutze direkten hardware rewrite für css und js dateien aus psc-cms sourcen
# (ist schneller als ResourceHandler, weil der immer auch doctrine bootstrappen muss)
# der Alias /psc-cms muss auf htdocs von psc-cms source zeigen (ohne trailing slash)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_URI} ^/(js|css)
RewriteRule  ^(.*)  /psc-cms/$1 [L,PT]

# entity service und durchfallende js / css dateien
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ^/(entities|js|css)

#RewriteRule  ^(.*)   /api.php?%{QUERY_STRING}&mod_rewrite_request=$1 [L]

RewriteRule . /api.php [L]
HTACCESS;
  }
}
?>