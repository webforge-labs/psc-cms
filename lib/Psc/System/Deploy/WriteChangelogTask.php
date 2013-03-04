<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\File;

class WriteChangelogTask extends \Psc\SimpleObject implements Task {
  
  protected $changelog;
  protected $changes;
  protected $versionIncrement = 'minor';
  
  public function __construct(File $changelog) {
    $this->changelog = $changelog;
  }
  
  public function run() {
    if (!isset($this->changes) || !is_array($this->changes)) {
      throw new \InvalidArgumentException('changes muss gesetzt sein');
    }    
    
    if (!$this->changelog->exists()) {
      throw new \RuntimeException('changelogFile '.$targetFile.' muss existieren');
    }
    require $this->changelog;

    /* changeslist : 
    
    'bugfix: Im Sound Content stand nur "content" statt dem eigentlichen Soundtext',
    'geändert: Bei der Soundsuche wird bei sehr großen Results das Ergebnis auf 15 Einträge eingeschränkt'
    
    */
    
    /* version */
    //2.0.9-Beta
    $version = \Psc\Preg::qmatch($data[0]['version'], '/^([0-9]+)\.([0-9]+)\.([0-9]+)\-(Alpha|Beta|Gamma)$/i', array(1,2,3,4));
    
    if (!is_array($version)) {
      throw new \RuntimeException('Fehler beim Version Parsing. Alte Version '.$oldVersion);
    }
    
    if ($this->versionIncrement === 'minor') {
      $version[2]++;
    } else {
      throw new \InvalidArgumentException('Kann nichts anderes als minor für versionIncrement');
    }
    
    $newVersion = vsprintf('%d.%d.%d-%s', $version);
    
    
    $php = <<<'PHP'
$data[] = array(
  'version'=>'%version%',
  'time'=>'%time%',
  'changelog'=>array(
    %changesList%
  )
);

PHP;

    $php = \Psc\TPL\TPL::miniTemplate($php,array('version'=>$newVersion,
                                            'time'=>date('H:i d.m.Y'),
                                            'changesList'=>
                                              \Webforge\Common\ArrayUtil::implode(
                                                $this->changes,
                                                ",\n    ",
                                                function ($change) {
                                                  return var_export($change, true);
                                                }
                                              )
                                            )
                                    );
    
    $contents = $this->changelog->getContents();
    $pos = mb_strpos($contents, $needle = '$data = array();'."\n");
    
    if ($pos === FALSE) {
      throw new \RuntimeException('Cannot Modify File: '.\Webforge\Common\String::cutAt($contents, 300).' enhält nicht '.$needle);
    }
    $pos += mb_strlen($needle);
    
    $contents = mb_substr($contents, 0, $pos).
                  $php."\n".
                mb_substr($contents, $pos);
                
    $this->changelog->writeContents($contents);
  }
  
  /**
   * @param  $changes
   * @chainable
   */
  public function setChanges(array $changes) {
    $this->changes = $changes;
    return $this;
  }

  /**
   * @return 
   */
  public function getChanges() {
    return $this->changes;
  }


}
?>