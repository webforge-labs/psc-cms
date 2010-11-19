<?php

class PHPCompiler extends Object {

  /**
   * 
   * @var array $classes
   */
  protected $classes;

  public function process() {
    /* wir durchlaufen alle Klassen und compilieren diese */

    /* alle Klassen indizieren */
    $this->locateClasses();

    /* jetzt wirds lustig: 
       wir müssen die Vererbungen der Klassen herausfinden 
       und daraus einen Graphen bauen
    */
    $this->parseClasses();
  }


  protected function parseClasses() {
    
    foreach ($this->classes as $className => $classInfo) {
      
      /* Klasse parsen */
      try {
        $parser = new PHPParser(new PHPLexer($classInfo['file']->getContents()));
        $parser->parse();
      } catch (PHPParserException $e) {
        throw new Exception('Fehler beim Parsen der Klasse: '.$classInfo['file'].' '.$e);
      }
      
      $class = NULL;
      foreach ($parser->getTree() as $phpElement) {
        if ($phpElement instanceof PHPClass) {
          $class = $phpElement;
          
          if ($class->getName() != $className) {
            throw new Exception('Es wurde die PHP-Klasse: "'.$class->getName().'" gefunden. Es wurde aber "'.$className.'" erwartet. Datei: '.$classInfo['file']);
          }

          $this->classes[$className]['class'] = $class;
          break;
        }
      }

      if ($class == NULL) {
        throw new Exception('es wurde keine Klasse in Datei: '.$classInfo['file'].' gefunden.');
      }
    }
  }

  protected function locateClasses() {
    
    $src = new Dir(SRC_PATH);
    $src->ignores[] = '/^.svn$/';
    
    $modules = PSC::getAvaibleModules();

    foreach ($modules as $moduleDir) {
      $classDir = $moduleDir->clone_()->append('class/');
      
      /* wir starten eine BFS für alle Dateien */
      foreach ($classDir->getFiles('php') as $classFile) {
        $name = PSC::getFullClassName($classFile);
        $this->classes[$name] = array(
          'name'=>$name,
          'file'=>$classFile
        );
      }
    }
  }
}

?>